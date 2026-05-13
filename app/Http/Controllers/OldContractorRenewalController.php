<?php

namespace App\Http\Controllers;

use App\Models\MstLicence;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OldContractorRenewalController extends BaseController
{
    private const CONTRACTOR_TABLES = [
        // Some DBs store codes as A/SA/SB/EB, others as EA/ESA/ESB/EB.
        'A'  => 'ealicense',   // EA
        'EA' => 'ealicense',   // EA
        'SA' => 'esalicense',  // ESA
        'ESA' => 'esalicense', // ESA
        'SB' => 'esblicense',  // ESB
        'ESB' => 'esblicense', // ESB
        'EB' => 'eblicense',   // EB
    ];

    private const CONTRACTOR_PREFIXES = [
        'A'  => 'EA',
        'EA' => 'EA',
        'SA' => 'ESA',
        'ESA' => 'ESA',
        'SB' => 'ESB',
        'ESB' => 'ESB',
        'EB' => 'EB',
    ];

    /**
     * Show the Old Contractor Renewal page.
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('logout');
        }

        $licences = MstLicence::where('status', 1)
        ->where('category_id', 1)
        ->orderBy('id', 'asc')
        ->get(['id', 'form_name', 'licence_name', 'cert_licence_code']);

        return view('user_login.old_contractor_renewal', compact('licences'));
    }

    /**
     * Verify whether a given contractor licence number is valid for the selected type.
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'form_id'     => 'required|integer|exists:mst_licences,id',
            'licence_no'  => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'valid'   => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $licence = MstLicence::find((int) $request->form_id);
        $code = strtoupper((string) ($licence->cert_licence_code ?? ''));

        $table = self::CONTRACTOR_TABLES[$code] ?? null;

        if (!$table) {
            return response()->json([
                'valid'   => false,
                'message' => 'Licence type is not recognized.',
            ], 200);
        }

        $expectedPrefix = self::CONTRACTOR_PREFIXES[$code] ?? '';
        $inputRaw = strtoupper(trim((string) $request->licence_no));

        // If user typed a known prefix but it doesn't match the selected type, show mismatch immediately.
        $typedPrefix = null;
        if (preg_match('/^([A-Z]+)/', $inputRaw, $m)) {
            $typedPrefix = strtoupper($m[1]);
        }
        $knownPrefixes = array_values(self::CONTRACTOR_PREFIXES);
        if ($typedPrefix && in_array($typedPrefix, $knownPrefixes, true) && $typedPrefix !== $expectedPrefix) {
            return response()->json([
                'valid'   => false,
                'message' => 'Invalid licence number for selected licence type.',
            ], 200);
        }

        $digits = $this->extractDigitsFromLicence($inputRaw, $expectedPrefix);
        if ($digits === null) {
            return response()->json([
                'valid'   => false,
                'message' => 'Licence number format is invalid.',
            ], 200);
        }

        $record = $this->findLegacyContractorRecord($table, $digits);

        if (!$record) {
            // Exists elsewhere? Then it's a type mismatch.
            foreach (self::CONTRACTOR_TABLES as $otherCode => $otherTable) {
                if ($otherTable === $table) continue;
                if ($this->findLegacyContractorRecord($otherTable, $digits)) {
                    return response()->json([
                        'valid'   => false,
                        'message' => 'Invalid licence number for selected licence type.',
                    ], 200);
                }
            }

            return response()->json([
                'valid'   => false,
                'message' => 'Licence number does not exist.',
            ], 200);
        }

        $verifiedExpiry = Carbon::parse($record->vdate)->toDateString();
        $expiry = Carbon::parse($verifiedExpiry)->startOfDay();
        $today = Carbon::today()->startOfDay();
        $expiredMoreThanOneYear = $today->gt($expiry->copy()->addYear());

        return response()->json([
            'valid'   => true,
            'message' => 'Licence verified successfully.',
            'details' => [
                'licence_no'  => $expectedPrefix . $digits,
                'expiry_date' => $verifiedExpiry,
                'expired_more_than_one_year' => $expiredMoreThanOneYear,
            ],
        ], 200);
    }

    /**
     * Store a submitted old contractor renewal request (document + verified details).
     */
    public function submit(Request $request)
    {
        $rules = [
            'form_name'    => 'required|integer|exists:mst_licences,id',
            'licence_no'   => 'required|string|max:50',
            'supporting_doc' => 'required|file|mimes:pdf,jpg,jpeg,png|max:256', // ~250KB
        ];

        $validated = $request->validate($rules);

        // Enforce same rules server-side (exists + belongs to type + expired>1year => block)
        $verifyRequest = new Request([
            'form_id'    => $validated['form_name'],
            'licence_no' => $validated['licence_no'],
        ]);
        $verifyResponse = $this->verify($verifyRequest);
        $verifyData = $verifyResponse->getData(true);

        if (empty($verifyData['valid'])) {
            return back()
                ->withErrors(['licence_no' => $verifyData['message'] ?? 'Licence validation failed.'])
                ->withInput();
        }

        $verifiedExpiry = $verifyData['details']['expiry_date'] ?? null;
        $expiredMoreThanOneYear = (bool) ($verifyData['details']['expired_more_than_one_year'] ?? false);
        $verifiedLicenceNo = $verifyData['details']['licence_no'] ?? strtoupper(trim((string) $validated['licence_no']));

        if ($verifiedExpiry && $expiredMoreThanOneYear) {
            return back()
                ->withErrors([
                    'licence_no' => 'Your licence expired more than a year ago, so it will be treated as a new application. Please apply for a new licence.',
                ])
                ->withInput();
        }

        // Redirect to contractor renewal form (within 1 year)
        $licence = MstLicence::find((int) $validated['form_name']);
        $code = strtoupper((string) ($licence->cert_licence_code ?? ''));
        $prefix = self::CONTRACTOR_PREFIXES[$code] ?? null;

        $routeName = match ($prefix) {
            'EA'  => 'renew-form_ea',
            'ESA' => 'renew-form_esa',
            'ESB' => 'renew-form_esb',
            'EB'  => 'renew-form_eb',
            default => null,
        };

        $applicationId = $routeName
            ? $this->resolveApplicationIdForRenewal($verifiedLicenceNo)
            : null;

        if (!$routeName || !$applicationId) {
            return back()
                ->withErrors([
                    'licence_no' => 'Unable to locate your application for renewal. Please contact support.',
                ])
                ->withInput();
        }

        $path = $request->file('supporting_doc')
            ->store('old_contractor_renewals', 'public');

        Log::info('Old contractor renewal request submitted', [
            'user_id'       => optional(Auth::user())->id,
            'licence_id'    => $validated['form_name'],
            'licence_no'    => strtoupper(trim((string) $validated['licence_no'])),
            'expiry_date'   => $verifiedExpiry,
            'document_path' => $path,
        ]);

        session([
            'old_contractor_renewal' => [
                'licence_id'    => (int) $validated['form_name'],
                'licence_no'    => strtoupper(trim((string) $validated['licence_no'])),
                'expiry_date'   => $verifiedExpiry,
                'document_path' => $path,
            ],
        ]);

        return redirect()
            ->route($routeName, ['application_id' => $applicationId])
            ->with('success', 'Licence verified. Please complete the renewal form.');
    }

    private function extractDigitsFromLicence(string $inputRaw, string $expectedPrefix): ?string
    {
        $input = $inputRaw;

        // Strip prefix (expected or any known) so digits extraction is stable
        if ($expectedPrefix !== '' && str_starts_with($input, $expectedPrefix)) {
            $input = substr($input, strlen($expectedPrefix));
        } else {
            foreach (array_values(self::CONTRACTOR_PREFIXES) as $p) {
                if ($p !== '' && str_starts_with($input, $p)) {
                    $input = substr($input, strlen($p));
                    break;
                }
            }
        }

        $digits = preg_replace('/\D/', '', $input);
        $digits = (string) $digits;
        $digits = ltrim($digits, '0') !== '' ? ltrim($digits, '0') : $digits;

        return $digits === '' ? null : $digits;
    }

    private function findLegacyContractorRecord(string $table, string $digits): ?object
    {
        // Postgres legacy tables often store certno as integer; cast to text to avoid integer/text mismatches.
        $record = DB::table($table)
            ->whereRaw('certno::text = ?', [$digits])
            ->first();

        if ($record) return $record;

        $digitsLtrim = ltrim($digits, '0');
        if ($digitsLtrim !== '' && $digitsLtrim !== $digits) {
            $record = DB::table($table)
                ->whereRaw('certno::text = ?', [$digitsLtrim])
                ->first();
        }

        return $record ?: null;
    }

    private function resolveApplicationIdForRenewal(string $licenceNo): ?int
    {
        $loginId = optional(Auth::user())->login_id;
        if (!$loginId) {
            return null;
        }

        $licenceNo = strtoupper(trim($licenceNo));
        $digits = preg_replace('/\D/', '', $licenceNo);
        $digits = (string) $digits;

        // Try to resolve from current licences first, then renewal licences
        foreach (['tnelb_license', 'tnelb_renewal_license'] as $table) {
            try {
                $id = DB::table($table . ' as l')
                    ->join('tnelb_application_tbl as a', 'l.application_id', '=', 'a.application_id')
                    ->where('a.login_id', $loginId)
                    ->whereRaw('upper(l.license_number::text) = ?', [$licenceNo])
                    ->value('l.application_id');

                if ($id) return (int) $id;

                // Fallback: some DBs store number without prefix
                if ($digits !== '') {
                    $id = DB::table($table . ' as l')
                        ->join('tnelb_application_tbl as a', 'l.application_id', '=', 'a.application_id')
                        ->where('a.login_id', $loginId)
                        ->whereRaw('l.license_number::text = ?', [$digits])
                        ->value('l.application_id');

                    if ($id) return (int) $id;
                }
            } catch (\Throwable $e) {
                // ignore and try next table
            }
        }

        return null;
    }
}

