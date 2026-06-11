<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MstLicence;
use App\Models\TnelbApplicantPhoto;
use App\Models\TnelbApplicantsSign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FormSAlteration extends BaseController
{
   public function index()
    {
            $appl_id = 'SC261111117';
        if (!Auth::check()) {
            return redirect()->route('logout');
        }

        if (!$appl_id) {
            return redirect()->route('dashboard')->with('error', 'Application ID is required.');
        }

        
        $application_details = DB::table('tnelb_application_tbl')
        ->where('application_id', $appl_id)
        ->select('*')
        ->first();

        $this->decryptPanForDisplay($application_details);

        
        $form_details = MstLicence::where('status', 1)
            ->select('*')
            ->get()
            ->toArray();
        
        $current_form = collect($form_details)->firstWhere('form_code', $application_details->form_name);

         $licence_name = DB::table('mst_licences')->where('form_code', $application_details->form_name)->first();

        if (!$current_form) {
            abort(504, 'Form Not Found..');
        }
        
        $fees_details = $this->getApplicableFee($current_form['id']);

        if (!$fees_details) {
            abort(505, 'The requested form details could not be found.');
        }


        if (!$application_details) {
            return redirect()->route('dashboard')->with('error', 'Application not found.');
        }

        $edu_details = DB::table('tnelb_applicants_edu')
            ->where('application_id', $appl_id)
            ->select('*')
            ->orderBy('year_of_passing', 'desc')
            ->get();

        $exp_details = DB::table('tnelb_applicants_exp')
            ->where('application_id', $appl_id)
            ->select('*')
            ->orderBy('id', 'asc')
            ->get();


        $license_details = DB::table('tnelb_license')
            ->where('application_id', $appl_id)
            ->select('*')
            ->first();
        $license_details = $this->enrichLicenseDetailsForRenewal($appl_id, $application_details, $license_details);

        $applicant_photo = TnelbApplicantPhoto::where('application_id', $appl_id)->first();

        $proof_doc = TnelbApplicantsSign::where('application_id', $appl_id)->first();

        $applicationid = $appl_id;

        $queries = DB::table('tnelb_query_applicable')
            ->where('application_id', $appl_id)
            ->where('query_status', 'P')
            ->orderByDesc('id')
            ->get();

        return view('user_login.alteration.form_s', compact(
            'applicationid',
            'application_details',
            'edu_details',
            'exp_details',
            'license_details',
            'applicant_photo',
            'proof_doc',
            'fees_details',
            'form_details',
            'licence_name',
            'queries'
        ));
       
    }
}
