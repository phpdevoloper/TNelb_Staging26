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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Pagination\LengthAwarePaginator;

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
        $st = strtoupper(trim((string) ($row->status ?? $row->application_status ?? $row->app_status ?? '')));
        $ps = strtolower(trim((string) ($row->payment_status ?? '')));

        return $st === 'QU' && $ps === 'draft';
    }

    public function login()
    { 
        // DB::table('tnelb_ea_applications')->truncate();
        // DB::table('tnelb_eb_applications')->truncate();
        // DB::table('tnelb_esa_applications')->truncate();
        // DB::table('tnelb_esb_applications')->truncate();
        // DB::table('tnelb_applicant_cl_staffdetails')->truncate();
        // DB::table('tnelb_applicant_doc_A')->truncate();
        // DB::table('proprietordetailsform_A')->truncate();
        // DB::table('tnelb_workflow_a')->truncate();

        // DB::table('tnelb_renewal_license')->truncate();
        // DB::table('tnelb_license')->truncate();


        //  DB::table('tnelb_banksolvency_a')->truncate();
        // DB::table('tnelb_bankguarantee_sb')->truncate();


        // DB::table('equipment_storetmp_a')->truncate();
        // DB::table('equipment_storetmp_sb')->truncate();
// DB::table('tnelb_application_tbl')->truncate();

// DB::table('tnelb_applicants_exp')->truncate();

// DB::table('mst_documents')->truncate();

// DB::table('tnelb_applicant_photos')->truncate();


        // dd('111');
        // exit;

// DB::table('tnelb_application_tbl')
//     ->where('application_id', 'WB251111111')
//     ->update([
//         'd_o_b' => '01-01-1945'
//     ]);

   // $show = DB::table('scert')
   //  ->where('certno', '22')
   //  ->update([
   //      'vdate' => '2027-03-30',
   //      // 'application_status' => 'A',

   //  ]);

   // $show = DB::table('scert')
   //  ->where('certno', '147')
   //  ->update([
   //      'vdate' => '2036-02-28',
   //      // 'application_status' => 'A',

   //  ]);


// $show = DB::table('tnelb_applicants_edu')
//         ->where('application_id', 'SC251111133')
//         ->get()->toArray();

        // $show = DB::table('payments')
        // ->where('application_id', 'SBESB25000001')
        // ->first();

//   $show = DB::table('tnelb_esa_applications')->get()->toArray();
// $show = DB::selectOne("SELECT * FROM calc_fees('R', 2, 'LB20251200003')");
// dd($show->base_fee);   // Example column
// exit;


//         $show = DB::table('mst_equipment_tbls')
//           ->where('equip_name', 'sample equipment')
//           ->delete();
// if ($show) {
//     echo "Record deleted successfully";
// } else {
//     echo "No record found";
// }

 

//  dd($show);
// exit;



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

          $commonColumns = [
        'af.application_id',
        'af.form_name',
         'af.created_at',
        'af.appl_type',
        'af.updated_at',
        'af.payment_status',
        'af.application_status',
        'af.payment_status',

        // Original license expiry
        'l.expires_at as original_expires_at',

        // Renewal license expiry
        'rl.expires_at as renewal_expires_at',

        // License number logic
        DB::raw("
            CASE 
                WHEN af.appl_type = 'N' THEN l.license_number 
                ELSE rl.license_number 
            END AS license_number
        "),

        // License expiry logic
        DB::raw("
            CASE 
                WHEN af.appl_type = 'N' THEN l.expires_at 
                ELSE rl.expires_at 
            END AS expires_at
        "),

        // Find renewed application id
        DB::raw("(
            SELECT t2.application_id
            FROM tnelb_application_tbl t2
            WHERE t2.old_application = af.application_id  
            ORDER BY t2.id ASC
            LIMIT 1
        ) AS renewed_application_id")
    ];


        
$today = now()->toDateString();

$tables = [
    'EA'  => 'tnelb_ea_applications',
    // 'ESA' => 'tnelb_esa_applications',
    // 'ESB' => 'tnelb_esb_applications',
    // 'EB'  => 'tnelb_eb_applications',
];

$applicationTables = array_values($tables);

$workflows_cl = collect();

foreach ($tables as $formCode => $tableName) {

    $records = DB::table("$tableName as ta")
        ->where('ta.login_id', $loginId)
        ->orderBy('ta.created_at', 'desc')
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

        $workflows_present = DB::table('tnelb_application_tbl as ta')
            ->where('ta.login_id', $loginId)
            ->orderByRaw("(CASE WHEN (ta.status = 'QU' OR ta.status = 'QU') AND LOWER(TRIM(COALESCE(ta.payment_status, ''))) = 'draft' THEN 0 ELSE 1 END)")
            ->orderBy('ta.submitted_date', 'desc')
            ->get()
            ->map(function ($workflow) {

                $licenseNumber = null;
                $expiry = null;
                $renewalApplicationId = null;
                $isValid = false;
                $validityMonth = null;

                $licenceID = null;


                $licenceID = MstLicence::where('cert_licence_code', $workflow->license_name)->value('id');
                
                // var_dump($licenceID);
                
                if ($workflow->appl_type === 'N') {
                    // Fresh license
                    $license = DB::table('tnelb_license')
                        ->where('application_id', $workflow->application_id)
                        ->select('license_number', 'expires_at')
                        ->first();

                    if ($license) {
                        // 🔑 Check if renewal exists (draft or submitted) using old_application
                        $renewalApp = DB::table('tnelb_application_tbl')
                            ->where('old_application', $workflow->application_id)
                            ->where('appl_type', 'R')
                            ->orderBy('id', 'desc')
                            ->first();

                        if ($renewalApp) {
                            // Renewal exists → show renewal app id in expired row
                            $renewalApplicationId = $renewalApp->application_id;
                            $licenseNumber = null;
                            $expiry = null;
                        } else {
                            // no renewal yet → show original license details
                            $licenseNumber = $license->license_number;
                            $expiry = $license->expires_at;
                        }
                    }
                } elseif ($workflow->appl_type === 'R') {
                    // Renewal application itself
                    $renewal = DB::table('tnelb_renewal_license')
                        ->where('application_id', $workflow->application_id)
                        ->select('license_number', 'expires_at')
                        ->first();

                    if ($renewal) {
                        $licenseNumber = $renewal->license_number;
                        $expiry = $renewal->expires_at;
                    }
                }

                // assign back

                if ($expiry) {
                    
                    $validityMonths = FeesValidity::where('licence_id', $licenceID)
                    ->where('form_type', 'A')
                    ->where('validity_start_date', '<=', $this->today)
                    ->value('validity');
                    
                    $expiryDate = Carbon::parse($expiry);
                    $validFromDate = $expiryDate->copy()->subMonths((int)$validityMonths);
                    $today = Carbon::today();

                    // var_dump($validFromDate->toDateString(), $expiryDate->toDateString().'<br>');

                    $oneYearAfterExpiry = $expiryDate->copy()->addYear();

                    $isValid = $isValid = ($today->greaterThanOrEqualTo($validFromDate)
                && $today->lessThanOrEqualTo($oneYearAfterExpiry));

                }else {
                    // No expiry means license not issued yet -> can't renew
                    $isValid = false;
                }

                $workflow->license_number = $licenseNumber;
                $workflow->expires_at = $expiry;
                $workflow->renewal_application_id = $renewalApplicationId;
                $workflow->is_under_validity_period = $isValid;

                return $workflow;

        });





        $renewal_applications = DB::table('tnelb_application_tbl as ta')
            ->leftJoin('tnelb_license as l', 'ta.application_id', '=', 'l.application_id')
            ->where('ta.login_id', $loginId)
            ->where('ta.appl_type', 'R')
            ->select(
                'ta.*',
                'l.license_number',
                'l.expires_at',
                DB::raw("(
                    SELECT t2.application_id
                    FROM tnelb_application_tbl t2
                    WHERE t2.login_id = ta.login_id
                      AND t2.id > ta.id
                      AND t2.form_name = ta.form_name
                    ORDER BY t2.id ASC
                    LIMIT 1
                ) AS next_application_id")
            )
            ->orderBy('ta.submitted_date', 'desc')
            ->get();


            // Get FORM P applications

        $all_form_p = DB::table('tnelb_form_p as ta')
            ->where('ta.login_id', $loginId)
            ->orderByRaw("(CASE WHEN ta.app_status = 'QU' AND LOWER(TRIM(COALESCE(ta.payment_status, ''))) = 'draft' THEN 0 ELSE 1 END)")
            ->orderBy('ta.submitted_date', 'desc')
            ->get()
            ->map(function ($workflow) {
                
                $licenseNumber = null;
                $expiry = null;
                $renewalApplicationId = null;
                $isValid = false;
                $validityMonth = null;
                
                $licenceID = null;
                
                $licenceID = MstLicence::where('cert_licence_code', $workflow->license_name)->value('id');
                
                if ($workflow->appl_type === 'N') {
                    // Fresh license
                    $license = DB::table('tnelb_license')
                    ->where('application_id', $workflow->application_id)
                    ->select('license_number', 'expires_at')
                    ->first();
                    
                    if ($license) {
                        // 🔑 Check if renewal exists (draft or submitted) using old_application
                        $renewalApp = DB::table('tnelb_form_p')
                        ->where('old_application', $workflow->application_id)
                        ->where('appl_type', 'R')
                        ->orderBy('id', 'desc')
                        ->first();
                        
                        if ($renewalApp) {
                            // Renewal exists → show renewal app id in expired row
                            $renewalApplicationId = $renewalApp->application_id;
                            $licenseNumber = null;
                            $expiry = null;
                        } else {
                            // no renewal yet → show original license details
                            $licenseNumber = $license->license_number;
                            $expiry = $license->expires_at;
                        }
                    }
                } elseif ($workflow->appl_type === 'R') {
                    // Renewal application itself
                    $renewal = DB::table('tnelb_renewal_license')
                    ->where('application_id', $workflow->application_id)
                    ->select('license_number', 'expires_at')
                    ->first();
                    
                    if ($renewal) {
                        $licenseNumber = $renewal->license_number;
                        $expiry = $renewal->expires_at;
                    }
                }
                
                
                if ($expiry) {
                    $validityMonths = FeesValidity::where('licence_id', $licenceID)
                    ->where('form_type', 'A')
                    ->where('validity_start_date', '<=', $this->today)
                    ->value('validity');
                    
                    $expiryDate = Carbon::parse($expiry);
                    $validFromDate = $expiryDate->copy()->subMonths((int)$validityMonths);
                    $today = Carbon::today();
                    
                    $oneYearAfterExpiry = $expiryDate->copy()->addYear();
                    
                    $isValid = $isValid = ($today->greaterThanOrEqualTo($validFromDate)
                    && $today->lessThanOrEqualTo($oneYearAfterExpiry));
                    
                }else {
                    // No expiry means license not issued yet -> can't renew
                    $isValid = false;
                }
                
                $workflow->license_number = $licenseNumber;
                $workflow->expires_at = $expiry;
                $workflow->renewal_application_id = $renewalApplicationId;
                $workflow->is_under_validity_period = $isValid;
                return $workflow;
                
            });



        $present_license = DB::table(function ($query) use ($loginId) {
            // First-time license
              $query->select(
                'l.license_number',
                'l.expires_at',
                'l.issued_at',
                'ta.application_id',
                'ta.form_name',
                'ta.license_name',
                DB::raw("'N' as license_type"),
                DB::raw("NULL as renewal_expires_at")   
            )

                ->from('tnelb_license as l')
                ->join('tnelb_application_tbl as ta', 'ta.application_id', '=', 'l.application_id')
                ->where('ta.login_id', $loginId)

                ->unionAll(
                    // Renewal licenses
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
                );
        }, 'licenses')
            ->whereDate('licenses.expires_at', '>=', now())
            ->orderBy('licenses.expires_at', 'desc')
            ->get();

            $commonColumns = [
                'ta.application_id',
                'ta.form_name',
                'ta.login_id',
                'ta.created_at',
                'ta.updated_at',
                'ta.license_name',

                'l.license_number',
                // 'l.license_name',
                'l.expires_at',
                'l.issued_at',

                 'R.expires_at as renewal_expires_at'
            ];
            $ea = DB::table('tnelb_ea_applications as ta')
            ->join('tnelb_license as l', 'ta.application_id', '=', 'l.application_id')
            ->leftJoin('tnelb_renewal_license as R', 'ta.application_id', '=', 'R.application_id')
            ->where('ta.login_id', $loginId)
            ->select($commonColumns);


       

            // $esa = DB::table('tnelb_esa_applications as ta')
            //     ->join('tnelb_license as l', 'ta.application_id', '=', 'l.application_id')
            //     ->leftJoin('tnelb_renewal_license as R', 'ta.application_id', '=', 'R.application_id')
            //     ->where('ta.login_id', $loginId)
            //     ->select($commonColumns);

            // $esb = DB::table('tnelb_esb_applications as ta')
            //     ->join('tnelb_license as l', 'ta.application_id', '=', 'l.application_id')
            //     ->leftJoin('tnelb_renewal_license as R', 'ta.application_id', '=', 'R.application_id')
            //     ->where('ta.login_id', $loginId)
            //     ->select($commonColumns);


            // $eb = DB::table('tnelb_eb_applications as ta')
            //     ->join('tnelb_license as l', 'ta.application_id', '=', 'l.application_id')
            //     ->leftJoin('tnelb_renewal_license as R', 'ta.application_id', '=', 'R.application_id')
            //     ->where('ta.login_id', $loginId)
            //     ->select($commonColumns);

            // $present_license_ea = $ea
            //     ->unionAll($esa)
            //     ->unionAll($esb)
            //     ->unionAll($eb)
            //     ->get();



        $table_applied_form = DB::table('tnelb_application_tbl as ta')
            ->where('ta.login_id', $loginId)
            ->pluck('form_name') // only need form_name values
            ->map(fn($v) => strtoupper(trim($v))) // normalize
            ->toArray();

        $table_applied_formA = DB::table('tnelb_ea_applications as ta')
            ->where('ta.login_id', $loginId)
            ->pluck('form_name') // only need form_name values
            ->map(fn($v) => strtoupper(trim($v))) // normalize
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
                $pa = $this->isReturnedQueryDraftRow($a);
                $pb = $this->isReturnedQueryDraftRow($b);
                if ($pa !== $pb) {
                    return $pa ? -1 : 1;
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
                    if (in_array($ps, ['payment', 'paid'], true)) {
                        $parts[] = 'success';
                        $parts[] = 'payment';
                    } else {
                        $parts[] = 'pending';
                    }

                    $st = strtoupper((string) ($row->status ?? $row->application_status ?? $row->app_status ?? ''));
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

        // Ajax
        if (request()->ajax()) {
            return view('user_login.pagination-list', compact('paginatedData'))->render();
        }


        $returnapplication = $workflows_cl
        ->where('application_status', 'RET')
        ->values();

        $returnapplication = $workflows_cl
        ->whereIn('application_status', ['RET', 'RETD'])
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
