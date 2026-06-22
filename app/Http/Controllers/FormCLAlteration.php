<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Admin\Mst_equipment_tbl;
use App\Models\Equipment_storetmp_A;
use App\Models\MstLicence;
use App\Models\Tnelb_Addressproof_cl;
use App\Models\Tnelb_Attachments_cl;
use App\Models\Tnelb_banksolvency_a;
use App\Models\Tnelb_Equimentsuser_cl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FormCLAlteration extends BaseController
{
    public function index()
    {

    $application_id = 'AEA26000023';
        // dd($application_id);
        // exit;
        $application = null;
        $proprietors = collect();
        $staffs = collect();
        $document = collect();

        if ($application_id) {
            $application = DB::table('tnelb_ea_applications')->where('application_id', $application_id)->first();
            $proprietors = DB::table('proprietordetailsform_A')
                ->where('application_id', $application_id)
                ->where('proprietor_flag', '1')
                ->orderBy('id')->get();
            $draftCount = $proprietors->count();

            $staffs = DB::table('tnelb_applicant_cl_staffdetails')->where('application_id', $application_id)->orderBy('id', 'ASC')->get();
            $document = DB::table('tnelb_applicant_doc_A')->where('application_id', $application_id)->first();
            $banksolvency = Tnelb_banksolvency_a::where('application_id', $application_id)->where('status', '1')->first();

            $equipmentlist = Equipment_storetmp_A::where('application_id', $application_id)->first();


            $attachment_doc = Tnelb_Attachments_cl::where('application_id', $application_id)->get();

            $Address_proof = Tnelb_Addressproof_cl::where('application_id', $application_id)->first();

            $equipmentDetails = Tnelb_Equimentsuser_cl::where('application_id', $application_id)
                ->get()
                ->keyBy('equipment_id');



            $equiplist = Mst_equipment_tbl::where('equip_licence_name', 8)
                ->where('status', 1)
                ->orderBy('id')
                ->get();

            $equipmentlist = DB::table('equipmentforma_tbls')
                ->where('login_id', Auth::user()->login_id)
                ->where('application_id', $application_id) // IMPORTANT
                ->get();

            $cert_licence_code = 'EA';
            $form_code = MstLicence::where('cert_licence_code', $cert_licence_code)
                ->where('status', 1)
                ->orderBy('id')
                ->first();

            // var_dump()

            $returnsection = json_decode($application->return_reason, true);
            // $returnsection = json_decode($application->return_reason, true);
        }

        return view('user_login.alteration.EA.form_ea', compact('application', 'proprietors', 'draftCount', 'staffs', 'document', 'banksolvency', 'equipmentlist', 'equiplist', 'form_code', 'attachment_doc', 'Address_proof', 'equipmentDetails', 'returnsection'));
    }
}
