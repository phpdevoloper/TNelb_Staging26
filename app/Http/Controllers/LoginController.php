<?php

namespace App\Http\Controllers;

use App\Models\Admin\FeesValidity;
use App\Models\admin\Tnelb_Newsboard as AdminTnelb_Newsboard;
use App\Models\Equipment_storetmp_A;
use Carbon\Carbon;
use App\Models\Login_Logs;
use App\Models\MstLicence;
use App\Models\Register;
use App\Models\Tnelb_banksolvency_a;
use App\Models\Tnelb_Newsboard;
use App\Services\Competency\CompetencyCertificateService;
use App\Services\Competency\CompetencyMetaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

use App\Models\CC_checklist_applicant;
use App\Models\PaymentTransactionModel;

class LoginController extends BaseController
{


     protected $today;

    public function __construct()
    {
        parent::__construct();
        $this->today = now()->toDateString();
    }

     /**
     * Staff returned the application (QU) and applicant is still on payment draft — surface these first in lists.
     */
    private function isReturnedQueryDraftRow(object $row): bool
    {
        $st = $this->normalizeWorkflowStatus($row->status ?? $row->application_status ?? $row->app_status ?? '');
        $ps = strtolower(trim((string) ($row->payment_status ?? '')));

        return $st === 'QU' && in_array($ps, ['draft', 'n'], true);
    }

    private function isReturnedQueryRow(object $row): bool
    {
        return $this->normalizeWorkflowStatus($row->status ?? $row->application_status ?? $row->app_status ?? '') === 'QU';
    }

    /** Trim padded char/varchar codes from cc_form_*_meta (e.g. app_status "QU   "). */
    private function normalizeWorkflowStatus(?string $status): string
    {
        return strtoupper(trim((string) $status));
    }

    /** SQL CASE for surfacing returned (QU) draft rows first in competency lists. */
    private function ccMetaReturnedFirstOrderSql(string $statusColumn, string $paymentColumn): string
    {
        return "(CASE WHEN TRIM({$statusColumn}) = 'QU' AND LOWER(TRIM(COALESCE({$paymentColumn}, ''))) IN ('draft', 'n') THEN 0 ELSE 1 END)";
    }

    /** Competency forms stored in per-form cc_form_*_meta tables. */
    private function competencyCcMetaFormNames(): array
    {
        return ['S', 'W', 'WH'];
    }

    private function competencyMetaService(): CompetencyMetaService
    {
        return app(CompetencyMetaService::class);
    }

    /** @return list<string> */
    private function competencySwMetaTables(): array
    {
        return array_values(array_filter([
            $this->competencyMetaService()->tableForForm('S'),
            $this->competencyMetaService()->tableForForm('W'),
            $this->competencyMetaService()->tableForForm('WH'),
        ]));
    }

    /** @return list<string> */
    private function ccMetaSelectColumns(): array
    {
        return [
            'ta.app_id',
            'ta.application_id',
            'ta.login_id',
            'ta.form_name',
            'ta.certificate_name',
            'ta.appl_type',
            'ta.app_status',
            'ta.payment_status',
            'ta.submitted_date',
            'ta.created_at',
            'ta.updated_at',
            'ta.old_application',
            'ta.certificate_no',
            'ta.processed_by',
        ];
    }

    private function competencyCertificateService(): CompetencyCertificateService
    {
        return app(CompetencyCertificateService::class);
    }

    /** Map cc_form_*_meta payment codes to dashboard labels (draft / payment). */
    private function normalizeCcPaymentStatus(?string $status): string
    {
        $normalized = strtolower(trim((string) $status));

        return match ($normalized) {
            'n', 'draft' => 'draft',
            'y', 'payment', 'paid', 'success' => 'payment',
            default => $normalized !== '' ? $normalized : 'draft',
        };
    }

    /** Align cc_form_*_meta row shape with legacy tnelb_application_tbl fields used by dashboard views. */
    private function normalizeCcWorkflowRow(object $workflow): object
    {
        $workflow->application_id = trim((string) ($workflow->application_id ?? ''));
        $workflow->form_name = strtoupper(trim((string) ($workflow->form_name ?? '')));
        $workflow->appl_type = strtoupper(trim((string) ($workflow->appl_type ?? '')));
        $workflow->certificate_name = trim((string) ($workflow->certificate_name ?? ''));

        if (! isset($workflow->license_name) || trim((string) ($workflow->license_name ?? '')) === '') {
            $workflow->license_name = $workflow->certificate_name !== '' ? $workflow->certificate_name : null;
        } else {
            $workflow->license_name = trim((string) $workflow->license_name);
        }

        $workflow->status = $this->normalizeWorkflowStatus($workflow->status ?? $workflow->app_status ?? null);
        $workflow->application_status = $workflow->status;
        $workflow->payment_status = $this->normalizeCcPaymentStatus($workflow->payment_status ?? null);

        if (! isset($workflow->id) && isset($workflow->app_id)) {
            $workflow->id = $workflow->app_id;
        }

        if (empty($workflow->created_at) && ! empty($workflow->submitted_date)) {
            $workflow->created_at = $workflow->submitted_date;
        }

        if (isset($workflow->processed_by)) {
            $workflow->processed_by = trim((string) $workflow->processed_by);
        }

        return $workflow;
    }

    /** Map tnelb_form_p row shape to fields expected by dashboard views. */
    private function normalizeFormPWorkflowRow(object $workflow): object
    {
        return $this->normalizeCcWorkflowRow($workflow);
    }

    private function loadCompetencyIssuedCertificate(object $workflow): ?object
    {
        $applicationId = trim((string) ($workflow->application_id ?? ''));
        if ($applicationId === '') {
            return null;
        }

        $formName = strtoupper(trim((string) ($workflow->form_name ?? '')));
        if ($this->competencyCertificateService()->supportsForm($formName)) {
            return $this->competencyCertificateService()->asWorkflowLicense($applicationId, $formName);
        }

        // Legacy contractor / unmigrated rows only (not S/W/WH/P cert tables).
        $license = DB::table('tnelb_license')
            ->where('application_id', $applicationId)
            ->select('license_number', 'expires_at')
            ->first();

        if ($license) {
            return $license;
        }

        return DB::table('tnelb_renewal_license')
            ->where('application_id', $applicationId)
            ->select('license_number', 'expires_at')
            ->first();
    }

    /** Build present-license UNION branch for one form's cert table. */
    private function presentLicenseCertUnion(string $certTable, string $metaTable, string $formName, int|string $loginId): \Illuminate\Database\Query\Builder
    {
        $licenseNameExpr = 'ta.certificate_name';

        $query = DB::table("{$certTable} as c")
            ->join("{$metaTable} as ta", 'ta.application_id', '=', 'c.application_id')
            ->where('ta.login_id', $loginId)
            ->select(
                'c.certificate_no as license_number',
                'c.valid_to as expires_at',
                'c.dateof_issue as issued_at',
                'ta.application_id',
                'ta.form_name',
                DB::raw("{$licenseNameExpr} as license_name"),
                DB::raw("'C' as license_type"),
                DB::raw('NULL::timestamp as renewal_expires_at')
            );

        return $query;
    }

    private function enrichFormPWorkflowRow(object $workflow): object
    {
        $licenseNumber = null;
        $expiry = null;
        $renewalApplicationId = null;
        $isValid = false;

        $licenceID = MstLicence::where('cert_licence_code', $workflow->license_name)->value('id');

        if (in_array($workflow->appl_type, ['N', 'D', 'R'], true)) {
            $license = $this->competencyCertificateService()->asWorkflowLicense(
                (string) $workflow->application_id,
                'P'
            );

            if ($license) {
                if ($workflow->appl_type === 'N') {
                    $renewalApp = DB::table('tnelb_form_p')
                        ->where('old_application', $workflow->application_id)
                        ->where('appl_type', 'R')
                        ->orderByDesc('id')
                        ->first();

                    if ($renewalApp) {
                        $renewalApplicationId = $renewalApp->application_id;
                    } else {
                        $licenseNumber = $license->license_number;
                        $expiry = $license->expires_at;
                    }
                } else {
                    $licenseNumber = $license->license_number;
                    $expiry = $license->expires_at;
                }
            }
        }

        if ($expiry) {
            $validityMonths = FeesValidity::where('licence_id', $licenceID)
                ->where('form_type', 'A')
                ->where('validity_start_date', '<=', $this->today)
                ->value('validity');

            $expiryDate = Carbon::parse($expiry);
            $validFromDate = $expiryDate->copy()->subMonths((int) $validityMonths);
            $today = Carbon::today();
            $oneYearAfterExpiry = $expiryDate->copy()->addYear();

            $isValid = $today->greaterThanOrEqualTo($validFromDate)
                && $today->lessThanOrEqualTo($oneYearAfterExpiry);
        }

        $workflow->license_number = $licenseNumber;
        $workflow->expires_at = $expiry;
        $workflow->renewal_application_id = $renewalApplicationId;
        $workflow->is_under_validity_period = $isValid;

        return $workflow;
    }

    private function findCompetencyRenewalApplication(string $parentApplicationId): ?object
    {
        foreach ($this->competencyMetaService()->allMetaTables() as $metaTable) {
            $renewalApp = DB::table($metaTable)
                ->where('old_application', $parentApplicationId)
                ->where('appl_type', 'R')
                ->orderByDesc('app_id')
                ->first();

            if ($renewalApp) {
                return $renewalApp;
            }
        }

        return DB::table('tnelb_application_tbl')
            ->where('old_application', $parentApplicationId)
            ->where('appl_type', 'R')
            ->orderByDesc('id')
            ->first();
    }

    private function enrichCompetencyWorkflowRow(object $workflow): object
    {
        $licenseNumber = null;
        $expiry = null;
        $renewalApplicationId = null;
        $isValid = false;

        $licenceID = MstLicence::where('cert_licence_code', $workflow->license_name)->value('id');

        if (in_array($workflow->appl_type, ['N', 'D', 'R'], true)) {
            $license = $this->loadCompetencyIssuedCertificate($workflow);

            if ($license) {
                if ($workflow->appl_type === 'N') {
                    $renewalApp = $this->findCompetencyRenewalApplication((string) $workflow->application_id);

                    if ($renewalApp) {
                        $renewalApplicationId = $renewalApp->application_id;
                        $licenseNumber = null;
                        $expiry = null;
                    } else {
                        $licenseNumber = $license->license_number;
                        $expiry = $license->expires_at;
                    }
                } else {
                    $licenseNumber = $license->license_number;
                    $expiry = $license->expires_at;
                }
            }
        }

        if ($expiry) {
            $validityMonths = FeesValidity::where('licence_id', $licenceID)
                ->where('form_type', 'A')
                ->where('validity_start_date', '<=', $this->today)
                ->value('validity');

            $expiryDate = Carbon::parse($expiry);
            $validFromDate = $expiryDate->copy()->subMonths((int) $validityMonths);
            $today = Carbon::today();
            $oneYearAfterExpiry = $expiryDate->copy()->addYear();

            $isValid = $today->greaterThanOrEqualTo($validFromDate)
                && $today->lessThanOrEqualTo($oneYearAfterExpiry);
        }

        $workflow->license_number = $licenseNumber;
        $workflow->expires_at = $expiry;
        $workflow->renewal_application_id = $renewalApplicationId;
        $workflow->is_under_validity_period = $isValid;

        return $workflow;
    }

    private function loadCompetencyWorkflowsPresent(int|string $loginId): Collection
    {
        $ccSelect = $this->ccMetaSelectColumns();
        $ccWorkflows = collect();

        foreach ($this->competencySwMetaTables() as $metaTable) {
            $rows = DB::table("{$metaTable} as ta")
                ->where('ta.login_id', $loginId)
                ->orderByRaw($this->ccMetaReturnedFirstOrderSql('ta.app_status', 'ta.payment_status'))
                ->orderByDesc('ta.submitted_date')
                ->get($ccSelect);

            $ccWorkflows = $ccWorkflows->merge($rows);
        }

        $ccWorkflows = $ccWorkflows
            ->map(function ($workflow) {
                return $this->enrichCompetencyWorkflowRow(
                    $this->normalizeCcWorkflowRow($workflow)
                );
            });

        $ccApplicationIds = $ccWorkflows->pluck('application_id')->filter()->values()->all();

        $legacyWorkflows = DB::table('tnelb_application_tbl as ta')
            ->where('ta.login_id', $loginId)
            ->whereNotIn('ta.form_name', $this->competencyCcMetaFormNames())
            ->when($ccApplicationIds !== [], fn ($query) => $query->whereNotIn('ta.application_id', $ccApplicationIds))
            ->orderByRaw($this->ccMetaReturnedFirstOrderSql('ta.status', 'ta.payment_status'))
            ->orderByDesc('ta.submitted_date')
            ->get()
            ->map(function ($workflow) {
                return $this->enrichCompetencyWorkflowRow(
                    $this->normalizeCcWorkflowRow($workflow)
                );
            });

        return $ccWorkflows->merge($legacyWorkflows)->values();
    }

    private function attachAlterationParentContext(Collection $workflows): Collection
    {
        $alterationParentIds = $workflows
            ->filter(function ($workflow) {
                return strtoupper((string) ($workflow->appl_type ?? '')) === 'A'
                    && trim((string) ($workflow->old_application ?? '')) !== '';
            })
            ->pluck('old_application')
            ->unique()
            ->values();

        if ($alterationParentIds->isEmpty()) {
            return $workflows;
        }

        $parentsByApplicationId = collect();

        foreach ($this->competencyMetaService()->allMetaTables() as $metaTable) {
            DB::table($metaTable)
                ->whereIn('application_id', $alterationParentIds)
                ->get(['application_id', 'appl_type'])
                ->each(function ($parent) use (&$parentsByApplicationId) {
                    $parentsByApplicationId->put($parent->application_id, $parent);
                });
        }

        $missingParentIds = $alterationParentIds
            ->diff($parentsByApplicationId->keys())
            ->values();

        if ($missingParentIds->isNotEmpty()) {
            DB::table('tnelb_application_tbl')
                ->whereIn('application_id', $missingParentIds)
                ->get(['application_id', 'appl_type'])
                ->each(function ($parent) use (&$parentsByApplicationId) {
                    $parentsByApplicationId->put($parent->application_id, $parent);
                });
        }

        return $workflows->map(function ($workflow) use ($parentsByApplicationId) {
            if (strtoupper((string) ($workflow->appl_type ?? '')) !== 'A') {
                return $workflow;
            }

            $parentId = trim((string) ($workflow->old_application ?? ''));
            if ($parentId === '') {
                return $workflow;
            }

            $parent = $parentsByApplicationId->get($parentId);
            $workflow->parent_application_id = $parentId;
            $workflow->parent_appl_type = $parent->appl_type ?? null;

            return $workflow;
        });
    }

    /** @return array<string, string> application_id => gateway status */
    private function loadPayuCheckableApplications(Collection $applicationIds): array
    {
        $ids = $applicationIds->filter()->unique()->values()->all();
        if ($ids === []) {
            return [];
        }

        return PaymentTransactionModel::query()
            ->whereIn('application_id', $ids)
            ->whereIn('status', ['PENDING', 'INITIATED', 'PENDING_VERIFICATION', 'FAILED'])
            ->orderByDesc('id')
            ->get()
            ->unique('application_id')
            ->mapWithKeys(fn ($row) => [
                (string) $row->application_id => strtoupper((string) $row->status),
            ])
            ->all();
    }

    public function login()
    {
        return view('login');
    }
    public function check(Request $request)
    {
        // dd('111');
        // exit;
        $request->validate([
            'phone' => ['required', 'digits:10', 'regex:/^[6-9]\d{9}$/'],
            // 'captcha' => ['required'],
        ], [
            'phone.required' => 'Enter Mobile Number.',
            'phone.digits' => 'Enter a valid 10-digit mobile number.',
        ]);



        // Check if the phone number exists
        $user =Register::where('mobile', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a valid user. Please register now.'
            ], 422);
        }


        // Store login ID in session temporarily
        Session::put('login_user', $user->login_id);

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully'
        ], 200);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        if ($request->otp !== '123456') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP. Please try again.'
            ], 422);
        }

        $loginUser = Session::get('login_user');
        if (!$loginUser) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please log in again.'
            ], 401);
        }

        $user = Register::where('login_id', $loginUser)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Store session and login
        Session::put('login_id', $user->login_id);
        Session::put('user_name', $user->name);
        Auth::login($user);

        Login_Logs::create([
            'login_id' => $user->login_id,
            'ipaddress' => request()->ip(),
            'Idate' => now(),
            'attempt' => 1,
            'duration' => 0.00,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'redirect_url' => route('finalize.login'),

            'user_name' => $user->name,
        ], 200);
    }




    public function logout()
    {
        Auth::logout();
        Session::flush();

        return redirect()->route('login');
    }

    public function dashboard()
    {
        $loginId = session('login_id'); // Get login_id from session


        if (!$loginId) {
            return redirect()->route('login')->with('error', 'Session expired. Please log in again.');
        }

        // Retrieve user details
        $user = DB::table('tnelb_registers')->where('login_id', $loginId)->first();

        // Store user name in session
        if ($user) {
            session(['name' => $user->first_name.$user->last_name]);
        }

        $tables = [
            'EA' => 'tnelb_ea_applications',
        ];

        $applicationTables = array_values($tables);

        $workflows_cl = collect();

        foreach ($tables as $formCode => $tableName) {
            $records = DB::table("$tableName as ta")
                ->where('ta.login_id', $loginId)
                ->orderByDesc('ta.created_at')
                ->get()
                ->map(function ($workflow) use ($formCode, $applicationTables) {

                $licenseNumber = null;
                $expiry = null;
                $renewalApplicationId = null;
                $isValid = false;

                // 🔹 Get licence master id
                $licenceID = MstLicence::where(
                    'cert_licence_code',
                    $workflow->license_name
                )->value('id');

                $appl_type = str_replace(' ', '', $workflow->appl_type);

                // ------------------------------------------------
                // NEW APPLICATION
                // ------------------------------------------------
                if ($appl_type === 'N') {

                    $license = DB::table('tnelb_license')
                        ->where('application_id', $workflow->application_id)
                        ->select('license_number', 'expires_at')
                        ->first();

                    if ($license) {

                        // 🔍 Check renewal in ALL FOUR tables
                        foreach ($applicationTables as $appTable) {

                            $renewalApp = DB::table($appTable)
                                ->where('old_application', $workflow->application_id)
                                ->where('appl_type', 'R')
                                ->orderBy('id', 'desc')
                                ->first();

                            if ($renewalApp) {
                                $renewalApplicationId = $renewalApp->application_id;
                                break;
                            }
                        }

                        // If NO renewal exists → show license
                        if (!$renewalApplicationId) {
                            $licenseNumber = $license->license_number;
                            $expiry = $license->expires_at;
                        }
                    }
                }

                // ------------------------------------------------
                // RENEWAL APPLICATION
                // ------------------------------------------------
                elseif ($appl_type === 'R') {

                    $renewal = DB::table('tnelb_renewal_license')
                        ->where('application_id', $workflow->application_id)
                        ->select('license_number', 'expires_at')
                        ->first();

                    if ($renewal) {
                        $licenseNumber = $renewal->license_number;
                        $expiry = $renewal->expires_at;
                    }
                }



                // ------------------------------------------------
                // VALIDITY CHECK
                // ------------------------------------------------
                if ($expiry && $licenceID) {

                    $validityMonths = FeesValidity::where('licence_id', $licenceID)
                        ->where('form_type', 'A')
                        ->value('validity');

                    $expiryDate = Carbon::parse($expiry);
                    $validFromDate = $expiryDate->copy()->subMonths((int) $validityMonths);
                    $today = Carbon::today();
                    $oneYearAfterExpiry = $expiryDate->copy()->addYear();

                    $isValid = $today->greaterThanOrEqualTo($validFromDate)
                            && $today->lessThanOrEqualTo($oneYearAfterExpiry);
                }

                // ------------------------------------------------
                // ATTACH EXTRA DATA
                // ------------------------------------------------
                $workflow->form_code = $formCode;
                $workflow->license_number = $licenseNumber;
                $workflow->expires_at = $expiry;
                $workflow->renewal_application_id = $renewalApplicationId;
                $workflow->is_under_validity_period = $isValid;

                // Resolve full licence name for display (try form_code e.g. EA, then form_name e.g. A)
                $licenceRow = DB::table('mst_licences')->where('cert_licence_code', $formCode)->first()
                    ?? DB::table('mst_licences')->where('form_code', $workflow->form_name ?? '')->first();
                $workflow->licence_display_name = $licenceRow && !empty(trim($licenceRow->licence_name ?? ''))
                    ? $licenceRow->licence_name
                    : ('Form ' . $formCode);

                return $workflow;
            });

        $workflows_cl = $workflows_cl->merge($records);
    }

    // ------------------------------------------------
    // FINAL SORTING
    // ------------------------------------------------
    $workflows_cl = $workflows_cl
        ->sortByDesc('updated_at')
        ->values();

        $workflows_present = $this->attachAlterationParentContext(
            $this->loadCompetencyWorkflowsPresent($loginId)
        );




        $ccRenewalApplicationIds = collect();

        foreach ($this->competencySwMetaTables() as $metaTable) {
            $ccRenewalApplicationIds = $ccRenewalApplicationIds->merge(
                DB::table($metaTable)
                    ->where('login_id', $loginId)
                    ->whereRaw("TRIM(appl_type) = 'R'")
                    ->pluck('application_id')
            );
        }

        $ccRenewalApplicationIds = $ccRenewalApplicationIds->filter()->values()->all();

        $renewal_applications = collect();

        foreach ($this->competencySwMetaTables() as $metaTable) {
            $renewal_applications = $renewal_applications->merge(
                DB::table("{$metaTable} as ta")
                    ->where('ta.login_id', $loginId)
                    ->whereRaw("TRIM(ta.appl_type) = 'R'")
                    ->select($this->ccMetaSelectColumns())
                    ->orderByDesc('ta.submitted_date')
                    ->get()
            );
        }

        $renewal_applications = $renewal_applications
            ->map(function ($row) use ($loginId) {
                $row = $this->normalizeCcWorkflowRow($row);
                $cert = $this->competencyCertificateService()->asWorkflowLicense(
                    (string) $row->application_id,
                    $row->form_name ?? null
                );
                $row->license_number = $cert?->license_number;
                $row->expires_at = $cert?->expires_at;
                $metaTable = $this->competencyMetaService()->tableForForm((string) ($row->form_name ?? ''));
                $row->next_application_id = $metaTable
                    ? DB::table($metaTable)
                        ->where('login_id', $loginId)
                        ->where('app_id', '>', $row->app_id)
                        ->orderBy('app_id')
                        ->value('application_id')
                    : null;

                return $row;
            });

        $legacyRenewals = DB::table('tnelb_application_tbl as ta')
            ->where('ta.login_id', $loginId)
            ->where('ta.appl_type', 'R')
            ->whereNotIn('ta.form_name', $this->competencyCcMetaFormNames())
            ->when($ccRenewalApplicationIds !== [], fn ($query) => $query->whereNotIn('ta.application_id', $ccRenewalApplicationIds))
            ->orderByDesc('ta.submitted_date')
            ->get()
            ->map(function ($row) use ($loginId) {
                $renewal = DB::table('tnelb_renewal_license')
                    ->where('application_id', $row->application_id)
                    ->first();

                $row->license_number = $renewal->license_number ?? null;
                $row->expires_at = $renewal->expires_at ?? null;
                $row->next_application_id = DB::table('tnelb_application_tbl')
                    ->where('login_id', $loginId)
                    ->where('form_name', $row->form_name)
                    ->where('id', '>', $row->id)
                    ->orderBy('id')
                    ->value('application_id');

                return $row;
            });

        $renewal_applications = $renewal_applications->merge($legacyRenewals)->values();

        $migratedFormPIds = DB::table('cc_form_p_meta')->pluck('application_id')->filter()->all();

        $all_form_p = DB::table('cc_form_p_meta as ta')
            ->where('ta.login_id', $loginId)
            ->orderByRaw($this->ccMetaReturnedFirstOrderSql('ta.app_status', 'ta.payment_status'))
            ->orderByDesc('ta.submitted_date')
            ->get()
            ->map(function ($workflow) {
                return $this->enrichFormPWorkflowRow(
                    $this->normalizeCcWorkflowRow($workflow)
                );
            });

        $legacyFormP = DB::table('tnelb_form_p as ta')
            ->where('ta.login_id', $loginId)
            ->when($migratedFormPIds !== [], fn ($query) => $query->whereNotIn('ta.application_id', $migratedFormPIds))
            ->orderByRaw($this->ccMetaReturnedFirstOrderSql('ta.app_status', 'ta.payment_status'))
            ->orderByDesc('ta.submitted_date')
            ->get()
            ->map(function ($workflow) {
                return $this->enrichFormPWorkflowRow(
                    $this->normalizeFormPWorkflowRow($workflow)
                );
            });

        $all_form_p = $all_form_p->merge($legacyFormP)->values();



        $certService = $this->competencyCertificateService();
        $metaService = $this->competencyMetaService();
        $certFormConfig = [
            'S' => ['table' => $certService->certTableForForm('S'), 'meta' => $metaService->tableForForm('S')],
            'W' => ['table' => $certService->certTableForForm('W'), 'meta' => $metaService->tableForForm('W')],
            'WH' => ['table' => $certService->certTableForForm('WH'), 'meta' => $metaService->tableForForm('WH')],
            'P' => ['table' => $certService->certTableForForm('P'), 'meta' => $metaService->tableForForm('P')],
        ];

        $present_license = DB::table(function ($query) use ($loginId, $certFormConfig) {
            // Legacy unmigrated applications only
              $query->select(
                'l.license_number',
                'l.expires_at',
                'l.issued_at',
                'ta.application_id',
                'ta.form_name',
                'ta.license_name',
                DB::raw("'N' as license_type"),
                DB::raw('NULL::timestamp as renewal_expires_at')
            )

                ->from('tnelb_license as l')
                ->join('tnelb_application_tbl as ta', 'ta.application_id', '=', 'l.application_id')
                ->where('ta.login_id', $loginId)
                ->whereNotIn('ta.form_name', ['S', 'W', 'WH', 'P'])

                ->unionAll(
                    DB::table('tnelb_renewal_license as rl')
                        ->join('tnelb_application_tbl as ta', 'ta.application_id', '=', 'rl.application_id')
                       ->select(
                        'rl.license_number',
                        'rl.expires_at',
                        'rl.issued_at',
                        'rl.application_id',
                        'ta.form_name',
                        'ta.license_name',
                        DB::raw("'R' as license_type"),
                        'rl.expires_at as renewal_expires_at'
                    )
                        ->where('rl.login_id', $loginId)
                        ->whereNotIn('ta.form_name', ['S', 'W', 'WH', 'P'])
                );

            foreach ($certFormConfig as $formName => $cfg) {
                if (empty($cfg['table'])) {
                    continue;
                }
                $query->unionAll(
                    $this->presentLicenseCertUnion($cfg['table'], $cfg['meta'], $formName, $loginId)
                );
            }
        }, 'licenses')
            ->whereDate('licenses.expires_at', '>=', now())
            ->orderByDesc('licenses.expires_at')
            ->get();

        $table_applied_form = collect(
            DB::table('tnelb_application_tbl')
                ->where('login_id', $loginId)
                ->whereNotIn('form_name', array_keys(CompetencyMetaService::FORM_META_TABLES))
                ->pluck('form_name')
        );

        foreach ($metaService->allMetaTables() as $metaTable) {
            $table_applied_form = $table_applied_form->merge(
                DB::table($metaTable)
                    ->where('login_id', $loginId)
                    ->pluck('form_name')
            );
        }

        $table_applied_form = $table_applied_form
            ->map(fn ($v) => strtoupper(trim((string) $v)))
            ->unique()
            ->values()
            ->toArray();

        $table_applied_formA = DB::table('tnelb_ea_applications as ta')
            ->where('ta.login_id', $loginId)
            ->pluck('form_name')
            ->map(fn ($v) => strtoupper(trim((string) $v)))
            ->toArray();

        // Pagination code started
        $allowedPerPage = [5, 10, 20, 50, 100];
        $perPage = (int) request()->input('per_page', 5);
        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 5;
        }
        $mergedData = $workflows_present->merge($all_form_p);
        $mergedData = $mergedData
            ->sort(function ($a, $b) {
                $pa = $this->isReturnedQueryRow($a);
                $pb = $this->isReturnedQueryRow($b);
                if ($pa !== $pb) {
                    return $pa ? -1 : 1;
                }
                if ($this->isReturnedQueryDraftRow($a) !== $this->isReturnedQueryDraftRow($b)) {
                    return $this->isReturnedQueryDraftRow($a) ? -1 : 1;
                }
                $ta = strtotime((string) ($a->submitted_date ?? $a->created_at ?? '1970-01-01'));
                $tb = strtotime((string) ($b->submitted_date ?? $b->created_at ?? '1970-01-01'));

                return $tb <=> $ta;
            })
            ->values();

        $search = trim((string) request()->input('search', ''));
        if (mb_strlen($search) > 120) {
            $search = mb_substr($search, 0, 120);
        }
        if ($search !== '') {
            $needle = mb_strtolower($search, 'UTF-8');

            // Full calendar date (e.g. 27/03/2026) → match "Applied on" only (created_at / dt_submit),
            // not updated_at / other timestamps (avoids wrong rows when activity date ≠ applied date).
            $appliedDateYmd = null;
            foreach (['d/m/Y', 'j/n/Y', 'd-m-Y', 'd.m.Y', 'Y-m-d', 'd/m/y'] as $fmt) {
                try {
                    $parsed = Carbon::createFromFormat($fmt, $search);
                    if ($parsed instanceof Carbon) {
                        $appliedDateYmd = $parsed->format('Y-m-d');
                        break;
                    }
                } catch (\Throwable $e) {
                    //
                }
            }

            if ($appliedDateYmd !== null) {
                $mergedData = $mergedData->filter(function ($row) use ($appliedDateYmd) {
                    foreach (['created_at', 'dt_submit'] as $field) {
                        $raw = data_get($row, $field);
                        if ($raw === null || $raw === '') {
                            continue;
                        }
                        try {
                            if (Carbon::parse($raw)->format('Y-m-d') === $appliedDateYmd) {
                                return true;
                            }
                        } catch (\Throwable $e) {
                            //
                        }
                    }

                    return false;
                })->values();
            } else {
                $mergedData = $mergedData->filter(function ($row) use ($needle) {
                    $parts = [];

                    foreach (get_object_vars($row) as $v) {
                        if ($v === null || $v === '') {
                            continue;
                        }
                        if ($v instanceof \DateTimeInterface) {
                            $dt = Carbon::instance($v);
                            $parts[] = $dt->format('Y-m-d');
                            $parts[] = $dt->format('d/m/Y');
                            $parts[] = $dt->format('j/n/Y');
                            $parts[] = $dt->format('d-m-Y');
                            $parts[] = $dt->format('j-n-Y');
                            $parts[] = $dt->format('m/d/Y');

                            continue;
                        }
                        if (is_scalar($v)) {
                            $s = (string) $v;
                            $parts[] = $s;
                            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $s) || preg_match('/^\d{2,4}-\d{2}-\d{2}\s+\d/', $s)) {
                                try {
                                    $dt = Carbon::parse(substr($s, 0, 19));
                                    $parts[] = $dt->format('d/m/Y');
                                    $parts[] = $dt->format('j/n/Y');
                                    $parts[] = $dt->format('d/m/y');
                                    $parts[] = $dt->format('d-m-Y');
                                    $parts[] = $dt->format('j-n-Y');
                                    $parts[] = $dt->format('Y-m-d');
                                    $parts[] = $dt->format('m/d/Y');
                                    $parts[] = $dt->format('d.m.Y');
                                } catch (\Throwable $e) {
                                    //
                                }
                            }
                        }
                    }

                    $ps = strtolower((string) ($row->payment_status ?? ''));
                    if (in_array($ps, ['payment', 'paid', 'y', 'success'], true)) {
                        $parts[] = 'success';
                        $parts[] = 'payment';
                    } else {
                        $parts[] = 'pending';
                    }

                    $st = $this->normalizeWorkflowStatus($row->status ?? $row->application_status ?? $row->app_status ?? '');
                    $byCode = [
                        'P' => ['submitted'],
                        'F' => ['in progress'],
                        'QU' => ['returned'],
                        'RJ' => ['rejected'],
                        'RE' => ['resubmitted'],
                        'A' => ['approved', 'completed'],
                    ];
                    if ($st !== '' && isset($byCode[$st])) {
                        $parts = array_merge($parts, $byCode[$st]);
                    }

                    $hay = mb_strtolower(
                        implode(' ', array_unique(array_filter($parts, static fn ($p) => $p !== ''))),
                        'UTF-8'
                    );

                    return $hay !== '' && str_contains($hay, $needle);
                })->values();
            }
        }

        $page = LengthAwarePaginator::resolveCurrentPage();
        $currentPageItems = $mergedData->slice(($page - 1) * $perPage, $perPage)->values();



        $paginatedData = new LengthAwarePaginator(
            $currentPageItems,
            $mergedData->count(),
            $perPage,
            $page,
            [
                'path' => url()->current(),
                'query' => request()->query(),
            ]
        );

        $payuCheckableApplications = $this->loadPayuCheckableApplications(
            $mergedData->pluck('application_id')
        );

        // Ajax
        if (request()->ajax()) {
            return view('user_login.pagination-list', compact('paginatedData', 'payuCheckableApplications'))->render();
        }


        $returnapplication = $workflows_cl
            ->filter(function ($workflow) {
                $status = $this->normalizeWorkflowStatus($workflow->application_status ?? $workflow->status ?? '');

                return in_array($status, ['RET', 'RETD'], true);
            })
            ->values();


        $mstLicences = DB::table('mst_licences')->get();


        return view('user_login.index', compact(
            'loginId',
            'workflows_cl',
            'workflows_present',
            'present_license',
            'table_applied_form',
            'table_applied_formA',
            'table_applied_form',
            'renewal_applications',
            'all_form_p',
            'paginatedData',
            'payuCheckableApplications',
            'returnapplication',
            'mstLicences'
        ));


    }

    public function noticeboardcontent($news_id)
    {
        // Fetch the record by ID
        $news = AdminTnelb_Newsboard::find($news_id);

        if (!$news) {
            abort(404, 'Newsboard not found');
        }

        // Pass it to a view
        return view('noticeboardcontent', compact('news'));
    }
}
