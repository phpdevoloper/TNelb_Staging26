<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Admin\Mst_equipment_tbl;
use App\Models\EA_Application_model;
use App\Models\MstLicence;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FormAAlteration extends BaseController
{
    public function index(){

    if (!Auth::check()) {
            return redirect()->route('logout');
        }

        $cert_licence_code = 'EA';

        $equiplist = Mst_equipment_tbl::where('equip_licence_name', 8)
            ->where('status', 1)
            ->orderBy('id')
            ->get();


        $form_code = MstLicence::where('cert_licence_code', $cert_licence_code)
            ->where('status', 1)
            ->orderBy('id')
            ->first();
        // $cert_licence_code = $form_code ? $form_code->cert_licence_code : null;



        $loginId = Auth::user()->login_id;




        $applicationIds = EA_Application_model::where('login_id', $loginId)
            ->pluck('application_id');




        $today = Carbon::today();

        $activeLicense = DB::table('cl_forma_lic')
            ->whereIn('application_id', $applicationIds)
            ->whereDate('valid_from', '<=', $today)
            ->whereDate('valid_to', '>=', $today)
            ->orderByDesc('valid_to')
            ->first();




        $previousLicenceNo = '';
        $previousValidityFirstIssue = '';
        $previousValidityFrom = '';
        $previousValidityTo = '';




        if ($activeLicense) {

            $previousLicenceNo = $activeLicense->license_number;

            $previousValidityFirstIssue = $activeLicense->valid_from;

            $previousValidityFrom = $activeLicense->valid_from;

            $previousValidityTo = $activeLicense->valid_to;
        }


        return view('user_login.alteration.EA.form_ea', compact('equiplist', 'form_code',
            'previousLicenceNo',
            'previousValidityFirstIssue',
            'previousValidityFrom',
            'previousValidityTo'));

    }
}
