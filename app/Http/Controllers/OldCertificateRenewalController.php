<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MstLicence;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class OldCertificateRenewalController extends BaseController
{
    /**
     * Show the Old Certificate Renewal landing page.
     *
     * This page will be accessed from the user sidebar link
     * `route('old_certificate_renewal')`.
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('logout');
        }

        $licences = MstLicence::where('status', 1)
            ->where('category_id', 2)
            ->orderBy('id', 'asc')
            ->get(['id', 'form_name', 'licence_name', 'cert_licence_code']);

        return view('user_login.old_certificate_renewal', compact('licences'));
    }

    /**
     * Verify whether a given licence number and expiry date are valid.
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'form_id'        => 'required|integer|exists:mst_licences,id',
            'certificate_no' => 'required|string|max:50',
            'expiry_date'    => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'valid'   => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $licenseNumber = strtoupper(trim($request->certificate_no));
        $date          = $request->expiry_date;

        // Decide which single legacy table to hit based on licence prefix (C/B/H)
        $prefix = null;
        if (preg_match('/^([A-Z]+)/', $licenseNumber, $m)) {
            $prefix = $m[1];
        }

        switch ($prefix) {
            case 'C': // Supervisor competency
                $base = DB::table('scert');
                break;

            case 'B': // Wireman B
                $base = DB::table('wcert');
                break;

            case 'H': // WH
                $base = DB::table('whcert');
                break;

            default:
                return response()->json([
                    'valid'   => false,
                    'message' => 'Certificate prefix is not recognized.',
                ], 200);
        }

        // Strip prefix (e.g. C01 -> 01) because legacy tables store numeric part in certno
        $numericPart = preg_replace('/^[A-Z]+/', '', $licenseNumber);
        $digits = preg_replace('/\D/', '', (string) $numericPart);
        $digits = (string) $digits;
        $digitsLtrim = ltrim($digits, '0');
        if ($digitsLtrim === '') {
            $digitsLtrim = $digits;
        }

        if ($digits === '') {
            return response()->json([
                'valid'   => false,
                'message' => 'Certificate number format is invalid.',
            ], 200);
        }

        // Postgres legacy tables often store certno as integer; avoid integer=text mismatch by casting to text
        $record = $base
            ->whereRaw('certno::text = ?', [$digits])
            ->whereDate('vdate', $date)
            ->first();

        if (!$record && $digitsLtrim !== $digits) {
            $record = $base
                ->whereRaw('certno::text = ?', [$digitsLtrim])
                ->whereDate('vdate', $date)
                ->first();
        }

        if (!$record) {
            return response()->json([
                'valid'   => false,
                'message' => 'Certificate not found for the given number and expiry date.',
            ], 200);
        }

        $verifiedExpiry = Carbon::parse($record->vdate)->toDateString();
        $expiredMoreThanOneYear = Carbon::today()->startOfDay()->gt(Carbon::parse($verifiedExpiry)->addYear());

        return response()->json([
            'valid'   => true,
            'message' => 'Certificate verified successfully.',
            'details' => [
                'certificate_no' => $licenseNumber,
                'expiry_date' => $verifiedExpiry,
                'expired_more_than_one_year' => $expiredMoreThanOneYear,
            ],
        ]);
    }

    /**
     * Store a submitted old certificate renewal request.
     */
    public function submit(Request $request)
    {
        $licence = MstLicence::findOrFail($request->input('form_name')); // dropdown uses licence id as value

        $prefix = strtoupper((string) ($licence->cert_licence_code ?? ''));

        $rules = [
            'form_name'      => 'required|integer|exists:mst_licences,id',
            'certificate_no' => ['required', 'string', 'max:50'],
            'expiry_date'    => 'required|date',
            'supporting_doc' => 'required|file|mimes:pdf,jpg,jpeg,png|max:256', // ~250KB
        ];

        if ($prefix !== '') {
            $rules['certificate_no'][] = 'regex:/^' . $prefix . '[0-9]{2,}$/i';
        }

        $validated = $request->validate($rules, [
            'certificate_no.regex' => 'Certificate number must start with ' . $prefix . ' followed by digits (e.g. ' . $prefix . '01).',
        ]);

        // If expiry is more than 1 year old, treat as new application (block old renewal flow)
        $expiry = Carbon::parse($validated['expiry_date'])->startOfDay();
        if (Carbon::today()->startOfDay()->gt($expiry->copy()->addYear())) {
            return back()
                ->withErrors([
                    'expiry_date' => 'Your licence expired more than a year ago, so it will be treated as a new application. Please apply for a new licence.',
                ])
                ->withInput();
        }

        // Reuse verify logic to ensure number + date are valid in legacy tables
        $verifyRequest = new Request([
            'form_id'        => $validated['form_name'],
            'certificate_no' => $validated['certificate_no'],
            'expiry_date'    => $validated['expiry_date'],
        ]);

        $verifyResponse = $this->verify($verifyRequest);
        $verifyData     = $verifyResponse->getData(true);

        if (empty($verifyData['valid'])) {
            return back()
                ->withErrors(['certificate_no' => $verifyData['message'] ?? 'Certificate validation failed.'])
                ->withInput();
        }

        // Store uploaded document
        $path = $request->file('supporting_doc')
            ->store('old_certificate_renewals', 'public');

        // Log the request for now (can be replaced with dedicated table later)
        Log::info('Old certificate renewal request submitted', [
            'user_id'        => optional(Auth::user())->id,
            'licence_id'     => $validated['form_name'],
            'certificate_no' => $validated['certificate_no'],
            'expiry_date'    => $validated['expiry_date'],
            'document_path'  => $path,
        ]);

        // Carry verified certificate details into the renewal form flow
        session([
            'old_certificate_renewal' => [
                'licence_id'     => (int) $validated['form_name'],
                'licence_code'   => strtoupper((string) ($licence->cert_licence_code ?? '')),
                'certificate_no' => strtoupper(trim((string) $validated['certificate_no'])),
                'expiry_date'    => $validated['expiry_date'],
                'document_path'  => $path,
            ],
        ]);

        // Redirect directly to the correct OLD renewal form based on licence code
        $code = strtoupper((string) ($licence->cert_licence_code ?? ''));

        $routeName = match ($code) {
            'C'     => 'old_renewal.form_s',   // Supervisor Competency (Form S)
            'B'     => 'old_renewal.form_w',   // Wireman Competency (Form W)
            'H'     => 'old_renewal.form_wh',  // WH Competency (Form WH)
            default => 'dashboard',
        };

        return redirect()
            ->route($routeName)
            ->with('success', 'Old certificate validated and uploaded. Please complete the renewal form.');
    }

    /**
     * Show simple selector to choose C / B / H renewal form.
     */
    public function selectForm(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('logout');
        }

        return view('user_login.renew-form-old');
    }

    /**
     * OLD renewal form – Form S (Certificate C).
     */
    public function oldRenewalFormS(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('logout');
        }

        $user = Auth::user();
        $salutation = $user->salutation ?? null;
        $fullNameFromParts = trim(implode(' ', array_filter([$user->first_name ?? null, $user->last_name ?? null])));
        $baseName = $fullNameFromParts !== '' ? $fullNameFromParts : ($user->name ?? $user->user_name ?? session('name') ?? '');
        $baseName = trim((string) $baseName);
        $hasSalutationAlready = $salutation
            ? preg_match('/^\s*' . preg_quote((string) $salutation, '/') . '\.?\s+/i', $baseName)
            : false;
        $applicantName = $hasSalutationAlready
            ? $baseName
            : trim(($salutation ? ($salutation . '. ') : '') . $baseName);
        $applicantAddress = $user->address ?? '';

        $old = (array) session('old_certificate_renewal', []);
        $oldCertNo = isset($old['certificate_no']) ? (string) $old['certificate_no'] : null;
        $oldExpiry = $old['expiry_date'] ?? null;

        $application_details = (object) [
            'form_name'          => 'S',
            'license_name'       => 'C',
            'application_id'     => null,
            'applicant_name'     => $applicantName,
            'fathers_name'       => '',
            'applicants_address' => $applicantAddress,
            'd_o_b'              => null,
            'age'                => null,
            'previously_number'  => null,
            'license_verify'     => 0,
            'previously_date'    => null,
            'certificate_no'     => $oldCertNo,
            'cert_verify'        => $oldCertNo ? 1 : 0,
            'certificate_date'   => $oldExpiry,
            'aadhaar'            => null,
            'pancard'            => null,
            'aadhaar_doc'        => null,
            'pan_doc'            => null,
            'form_id'            => 1,
        ];

        $licenceId = isset($old['licence_id']) ? (int) $old['licence_id'] : null;
        $licence_name = $licenceId ? MstLicence::find($licenceId) : null;
        if (!$licence_name) {
            $licence_name = MstLicence::where('status', 1)
                ->where('cert_licence_code', 'C')
                ->orderBy('id', 'asc')
                ->first();
        }
        if (!$licence_name) {
            $licence_name = (object) ['licence_name' => (string) $application_details->license_name];
        }

        $edu_details     = collect();
        $exp_details     = collect();
        $license_details = (object) ['license_number' => $oldCertNo];
        $applicant_photo = (object) ['upload_path' => null];
        $fees_details    = (object) ['renewal_amount' => 0];

        return view('user_login.renew-form-old-certificate', compact(
            'application_details',
            'edu_details',
            'exp_details',
            'license_details',
            'applicant_photo',
            'fees_details',
            'licence_name'
        ));
    }

    /**
     * OLD renewal form – Form W (Certificate B).
     */
    public function oldRenewalFormW(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('logout');
        }

        $user = Auth::user();
        $salutation = $user->salutation ?? null;
        $fullNameFromParts = trim(implode(' ', array_filter([$user->first_name ?? null, $user->last_name ?? null])));
        $baseName = $fullNameFromParts !== '' ? $fullNameFromParts : ($user->name ?? $user->user_name ?? session('name') ?? '');
        $baseName = trim((string) $baseName);
        $hasSalutationAlready = $salutation
            ? preg_match('/^\s*' . preg_quote((string) $salutation, '/') . '\.?\s+/i', $baseName)
            : false;
        $applicantName = $hasSalutationAlready
            ? $baseName
            : trim(($salutation ? ($salutation . '. ') : '') . $baseName);
        $applicantAddress = $user->address ?? '';

        $old = (array) session('old_certificate_renewal', []);
        $oldCertNo = isset($old['certificate_no']) ? (string) $old['certificate_no'] : null;
        $oldExpiry = $old['expiry_date'] ?? null;

        $application_details = (object) [
            'form_name'          => 'W',
            'license_name'       => 'B',
            'application_id'     => null,
            'applicant_name'     => $applicantName,
            'fathers_name'       => '',
            'applicants_address' => $applicantAddress,
            'd_o_b'              => null,
            'age'                => null,
            'previously_number'  => null,
            'license_verify'     => 0,
            'previously_date'    => null,
            'certificate_no'     => $oldCertNo,
            'cert_verify'        => $oldCertNo ? 1 : 0,
            'certificate_date'   => $oldExpiry,
            'aadhaar'            => null,
            'pancard'            => null,
            'aadhaar_doc'        => null,
            'pan_doc'            => null,
            'form_id'            => 2,
        ];

        $licenceId = isset($old['licence_id']) ? (int) $old['licence_id'] : null;
        $licence_name = $licenceId ? MstLicence::find($licenceId) : null;
        if (!$licence_name) {
            $licence_name = MstLicence::where('status', 1)
                ->where('cert_licence_code', 'B')
                ->orderBy('id', 'asc')
                ->first();
        }
        if (!$licence_name) {
            $licence_name = (object) ['licence_name' => (string) $application_details->license_name];
        }

        $edu_details     = collect();
        $exp_details     = collect();
        $license_details = (object) ['license_number' => $oldCertNo];
        $applicant_photo = (object) ['upload_path' => null];
        $fees_details    = (object) ['renewal_amount' => 0];

        return view('user_login.renew-form-old-certificate', compact(
            'application_details',
            'edu_details',
            'exp_details',
            'license_details',
            'applicant_photo',
            'fees_details',
            'licence_name'
        ));
    }

    /**
     * OLD renewal form – Form WH (Certificate H).
     */
    public function oldRenewalFormWH(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('logout');
        }

        $user = Auth::user();
        $salutation = $user->salutation ?? null;
        $fullNameFromParts = trim(implode(' ', array_filter([$user->first_name ?? null, $user->last_name ?? null])));
        $baseName = $fullNameFromParts !== '' ? $fullNameFromParts : ($user->name ?? $user->user_name ?? session('name') ?? '');
        $baseName = trim((string) $baseName);
        $hasSalutationAlready = $salutation
            ? preg_match('/^\s*' . preg_quote((string) $salutation, '/') . '\.?\s+/i', $baseName)
            : false;
        $applicantName = $hasSalutationAlready
            ? $baseName
            : trim(($salutation ? ($salutation . '. ') : '') . $baseName);
        $applicantAddress = $user->address ?? '';

        $old = (array) session('old_certificate_renewal', []);
        $oldCertNo = isset($old['certificate_no']) ? (string) $old['certificate_no'] : null;
        $oldExpiry = $old['expiry_date'] ?? null;

        $application_details = (object) [
            'form_name'          => 'WH',
            'license_name'       => 'H',
            'application_id'     => null,
            'applicant_name'     => $applicantName,
            'fathers_name'       => '',
            'applicants_address' => $applicantAddress,
            'd_o_b'              => null,
            'age'                => null,
            'previously_number'  => null,
            'license_verify'     => 0,
            'previously_date'    => null,
            'certificate_no'     => $oldCertNo,
            'cert_verify'        => $oldCertNo ? 1 : 0,
            'certificate_date'   => $oldExpiry,
            'aadhaar'            => null,
            'pancard'            => null,
            'aadhaar_doc'        => null,
            'pan_doc'            => null,
            'form_id'            => 3,
        ];

        $licenceId = isset($old['licence_id']) ? (int) $old['licence_id'] : null;
        $licence_name = $licenceId ? MstLicence::find($licenceId) : null;
        if (!$licence_name) {
            $licence_name = MstLicence::where('status', 1)
                ->where('cert_licence_code', 'H')
                ->orderBy('id', 'asc')
                ->first();
        }
        if (!$licence_name) {
            $licence_name = (object) ['licence_name' => (string) $application_details->license_name];
        }

        $edu_details     = collect();
        $exp_details     = collect();
        $license_details = (object) ['license_number' => $oldCertNo];
        $applicant_photo = (object) ['upload_path' => null];
        $fees_details    = (object) ['renewal_amount' => 0];

        return view('user_login.renew-form-old-certificate', compact(
            'application_details',
            'edu_details',
            'exp_details',
            'license_details',
            'applicant_photo',
            'fees_details',
            'licence_name'
        ));
    }
}

