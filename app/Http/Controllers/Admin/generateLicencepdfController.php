<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Mst_equipment_tbl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Auth;

class generateLicencepdfController extends Controller
{

    public function licencepdf_cl($application_id)
    {
        // ---------------------------------------
        // 1. DETECT WHICH TABLE HAS THE APPLICATION
        // ---------------------------------------
        $application = null;
        $table_name = null;

        $tables = [
            // 'tnelb_esa_applications',
            // 'tnelb_esb_applications',
            // 'tnelb_eb_applications',
            'tnelb_ea_applications'
        ];

        foreach ($tables as $t) {
            $record = DB::table($t)
                ->where('application_id', $application_id)
                ->first();

            if ($record) {
                $application = $record;
                $table_name = $t;
                break;
            }
        }

        if (!$application) {
            return back()->with('error', 'Application ID not found.');
        }

        $licence_id = DB::table('mst_licences')
            ->where('cert_licence_code', $application->license_name)
            ->first();

        // Clean appl_type
        $appltype = strtoupper(trim($application->appl_type));


        // ---------------------------------------
        // 2. FRESH APPLICATION (appl_type = N)
        // ---------------------------------------
        if ($appltype === 'N') {

            $applicant = DB::table('tnelb_license')
                ->join($table_name, 'tnelb_license.application_id', '=', $table_name . '.application_id')
                ->where('tnelb_license.application_id', $application_id)
                ->select(
                    'tnelb_license.application_id',
                    'tnelb_license.issued_by',
                    'tnelb_license.issued_at',
                    'tnelb_license.expires_at',

                    $table_name . '.applicant_name AS name',
                    $table_name . '.license_name',
                    $table_name . '.form_name',

                    'tnelb_license.license_number'
                )
                ->first();

            $staffDetails = DB::table('tnelb_applicant_cl_staffdetails')
                ->where('application_id', $application_id)
                ->orderBy('id')
                ->get();
        }

        // ---------------------------------------
        // 3. RENEWAL APPLICATION
        // ---------------------------------------
        else {

            $applicant = DB::table('tnelb_renewal_license')
                ->join($table_name, 'tnelb_renewal_license.application_id', '=', $table_name . '.application_id')
                ->where('tnelb_renewal_license.application_id', $application_id)
                ->select(
                    'tnelb_renewal_license.application_id',
                    'tnelb_renewal_license.issued_by',
                    'tnelb_renewal_license.issued_at',
                    'tnelb_renewal_license.expires_at',

                    $table_name . '.applicant_name AS name',
                    $table_name . '.license_name',
                    $table_name . '.form_name',

                    'tnelb_renewal_license.license_number'
                )
                ->first();

            $staffDetails = DB::table('tnelb_applicant_cl_staffdetails')
                ->where('application_id', $application_id)
                ->orderBy('id')
                ->get();
        }


        // ---------------------------------------
        // 4. RETURN OR LOAD PDF VIEW
        // ---------------------------------------

        $mpdf = new \Mpdf\Mpdf([
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'default_font_size' => 11,
            'default_font' => 'Abyssinica_SIL'
        ]);

        $mpdf->SetTitle('TNELB Application License ' . $applicant->license_name);

        $formname = $applicant->form_name;

        // dd($formname);
        // exit;

        $license_name = DB::table('mst_licences')->where('form_code', $formname)->first();


        // dd($license_name);
        // exit;

        $mpdf->WriteHTML('<style>
        body { }
        p, td, th { padding: 0px; }
        p {font-size:15px;}
        .tbl_center { text-align: center;!important }
        .mt-2 { margin-top: 5; }
        table { border-collapse: collapse; width: 100%; }
        th, td {  padding: 8px; text-align:left; }
        .highlight { font-weight: bold; color: white; background-color: green; padding: 5px; text-align:center; font-size:16px; }
        .photo-container { text-align: right; padding-right: 10px; }
        .photo-container img { width: 132px; height: 170px; border: 1px solid #000; object-fit: cover; display: block; }
        .highlight_text{color:green;}
        .staff_tbl td{text-align:center;}
        .license_name{ font-size:15px;text-align:center;font-weight:bold; }
        .font-weight{font-weight:bold;}
        .blue{color:#000;}
        .orange{color:#000;}
        .txt_uppercase{text-transform:uppercase;}
        .line-height-30{line-height:20px;}
        .text-indent-40 {text-indent: 40px;}
        .text-indent-60 {text-indent: 50px;}
        .text-justify {text-align: justify;}
        .font-size-14{ font-size:15px;}
        .mb-5{margin-bottom:5px;}
        .mb-1{margin-bottom:1px;}
        .mb-10{margin-bottom:10px;}
        .pb-10{padding: bottom 10px!important;}
        .mt-5{margin-top:5px;}
        .mt-1{margin-top:1px;}
        .text-black{color:black;}
        .text-uppercase{text-transform:uppercase;}
        .font-size-16{font-size:16px;}
        .font-weight-n{font-weight:normal}
        
    </style>', \Mpdf\HTMLParserMode::HEADER_CSS);
        $grade_name = $applicant->license_name;
        // dd($grade_name);
        // exit;

        if ($grade_name == 'EA') {
            $grade_name_txt = 'EA Grade Contractor Licence';
        } elseif ($grade_name == 'ESA') {
            $grade_name_txt = 'ESA Grade Contractor Licence';
        } elseif ($grade_name == 'ESB') {
            $grade_name_txt = 'ESB Grade Contractor Licence';
        } elseif ($grade_name == 'EB') {
            $grade_name_txt = 'EB Grade Contractor Licence ';
        }
        $qrData = url('/verify-certificate/' . $applicant->application_id);



        // Start building the PDF content
        $html = '
<table width="100%" class="mt-1 mb-1">
    <tr>
        <!-- LEFT CONTENT -->
        <td  style="text-align:center; padding-top:10px;">

            <div class="header-block">
                <h3 class="blue mb-10 pb-10 ">GOVERNMENT OF TAMILNADU</h3>
                <br>

                <h3 class="blue mb-10 pb-10">ELECTRICAL LICENSING BOARD</h3>
                <br>

                <h3 class="blue mb-10 pb-10">
                    Thiru.Vi.Ka. Industrial Estate, Guindy, Chennai - 600 032.
                </h3>
                <br>

                <p class="license_name orange mt-10 text-uppercase">
                    ' . $grade_name_txt . '
                </p>
            </div>

        </td>

        

  
    </tr>
</table>';

        $proprietors = DB::table('proprietordetailsform_A')
            ->where('application_id', $application_id)
            ->where('proprietor_flag', '1')
            ->orderBy('id')
            ->get();
        $proprietorNames = '';

        if ($proprietors->count() > 0) {
            foreach ($proprietors as $proprietor) {
                $proprietorNames .= strtoupper($proprietor->proprietor_name) . ', ';
            }

            // Remove last comma
            $proprietorNames = rtrim($proprietorNames, ', ');
        } else {
            $proprietorNames = '—';
        }

    $html .= '
<table style="width:100%; border-collapse:collapse;" class="mt-1 mb-1">
    <tr>
        <!-- LEFT SIDE DATA -->
        <td style="width:80%; vertical-align:top;">
            <table style="width:100%;">
                <tr>
                    <td style="width:50%;"><b class="txt_uppercase">Licence No</b></td>
                    <td>: ' . $applicant->license_number . '</td>
                </tr>

                <tr>
                    <td><b class="txt_uppercase">D.O.I</b></td>
                    <td>: ' . format_date($applicant->issued_at) . '</td>
                </tr>

                <tr>
                    <td><b class="txt_uppercase">Validity</b></td>
                    <td>: ' . format_date($applicant->issued_at) . ' To ' . format_date($applicant->expires_at) . '</td>
                </tr>

                <tr>
                    <td><b class="txt_uppercase">Name</b></td>
                    <td>: ' . strtoupper($applicant->name) . '</td>
                </tr>

                <tr>
                    <td><b>PROPRIETOR / PARTNER / DIRECTOR NAME</b></td>
                    <td>: ' . $proprietorNames . '</td>
                </tr>
            </table>
        </td>

        <!-- RIGHT SIDE QR -->
        <td style="width:20%; text-align:right; vertical-align:top;">
            <barcode 
                code="' . htmlspecialchars($qrData) . '" 
                type="QR" 
                size="1.5"
                error="H"
            />
        </td>
    </tr>
</table>';

        $html .= '
<h4 class="mt-2 orange font-size-14 font-weight txt_uppercase">Staff Details</h4>

<table border="1" cellpadding="6" cellspacing="0" style="width:100%; border-collapse:collapse; text-align:center;">

    <tr style="background-color:#f2f2f2;">
        <th>S.No</th>
        <th>Name</th>
        <th>Certificate Number</th>
        <th>Certificate Validity</th>
    </tr>';

       if ($staffDetails->count() > 0) {
    $i = 1;
    foreach ($staffDetails as $staff) {

        $html .= '
        <tr>
            <td>' . $i++ . '</td>
            <td>' . strtoupper($staff->staff_name) . '</td>
            <td>' . $staff->cc_number . '</td>
            <td>' . format_date($staff->cc_validity) . '</td>
        </tr>';
    }
} else {
    $html .= '
    <tr>
        <td colspan="4">No Staff Details Available</td>
    </tr>';
}
        $html .= '</table>';


        $equiplist = Mst_equipment_tbl::where('equip_licence_name', 8)
            ->where('status', 1)
            ->orderBy('id')
            ->get();

        $equipmentlist = DB::table('tnelb_equimentsuser_cl')

            ->where('application_id', $application_id)
            ->get();

        /*
            Create map:
            equip_id => equipment_value
            */
        $equipmentMap = collect($equipmentlist)->keyBy('equipment_id');

        $html .= ' <h4 class="mt-2 orange font-size-14 font-weight txt_uppercase">Equipment / Instruments List</h4>';

        $html .= '
        <table border="1" cellpadding="4" cellspacing="0" width="100%">
            <thead>
                <tr style="font-weight:bold; background-color:#f2f2f2;">
                    <th >S.No</th>
                    <th >Equipment Name</th>
                    <th >Equipment Type</th>
                    <th  align="center">Serial No</th>
                    <th align="center">	Make Model</th>
                    <th  align="center">Date of Test</th>
                </tr>
            </thead>
            <tbody>';

        $slno = 1;

        foreach ($equiplist as $equip) {

            $userEquip = $equipmentMap[$equip->id] ?? null;

            $serial = $userEquip->serial_no ?? '';
            $model  = $userEquip->model_no ?? '';
            $date   = $userEquip->dateoftest ?? '';

            $html .= '
            <tr>
                <td align="center">' . $slno . '</td>
                <td align="center">' . $equip->equip_name . '</td>
                <td align="center">' . $equip->equipment_type . '</td>
                <td align="center">' . $serial . '</td>
                <td align="center">' . $model . '</td>
                <td align="center">' . $date . '</td>
            </tr>';
            $slno++;
        }

        $html .= '
    </tbody>
</table>';


        // $html .= '<h4 class="mt-2 highlight"> Payment Details</h4>
        // <table class="tbl_center bank">
        //     <tr>
        //         <th class="">Bank Name</th>
        //         <th class="">Mode of Payment</th>
        //         <th class="">Amount</th>
        //         <th class="">Payment Date</th>
        //         <th class="">Transaction ID</th>
        //     </tr>
        //     <tr>
        //         <td>State Bank of India</td>
        //         <td>UPI</td>
        //         <td>' . ($payment->amount ?? 'N/A') . '</td>
        //         <td>25-02-2025</td>
        //         <td>' . ($payment->transaction_id ?? 'N/A') . '</td>
        //     </tr>
        // </table>';

        // Declaration


        // Write HTML to PDF
        $mpdf->WriteHTML($html);



        // Footer section
        $mpdf->SetFooter('
        <table style="width:100%; font-size:12px;">
            <tr>
                <td style="text-align:left;">TNELB</td>
                <td class="label" style="text-align:right;">Date : ' . date('d-m-Y') . '</td>
                
            </tr>
        </table>
        ');

        return response($mpdf->Output('License_A_approval.pdf', 'I'))
            ->header('Content-Type', 'application/pdf');
    }



    // ----------------------Tamil-----------------------------------------

    public function licencepdf_cl_tamil($application_id)
    {

    // dd('111');exit;
        // ---------------------------------------
        // 1. DETECT WHICH TABLE HAS THE APPLICATION
        // ---------------------------------------
        $application = null;
        $table_name = null;

        $tables = [
            // 'tnelb_esa_applications',
            // 'tnelb_esb_applications',
            // 'tnelb_eb_applications',
            'tnelb_ea_applications'
        ];

        foreach ($tables as $t) {
            $record = DB::table($t)
                ->where('application_id', $application_id)
                ->first();

            if ($record) {
                $application = $record;
                $table_name = $t;
                break;
            }
        }

        if (!$application) {
            return back()->with('error', 'Application ID not found.');
        }

        $licence_id = DB::table('mst_licences')
            ->where('cert_licence_code', $application->license_name)
            ->first();

        // Clean appl_type
        $appltype = strtoupper(trim($application->appl_type));


        // ---------------------------------------
        // 2. FRESH APPLICATION (appl_type = N)
        // ---------------------------------------
        if ($appltype === 'N') {

            $applicant = DB::table('tnelb_license')
                ->join($table_name, 'tnelb_license.application_id', '=', $table_name . '.application_id')
                ->where('tnelb_license.application_id', $application_id)
                ->select(
                    'tnelb_license.application_id',
                    'tnelb_license.issued_by',
                    'tnelb_license.issued_at',
                    'tnelb_license.expires_at',

                    $table_name . '.applicant_name AS name',
                    $table_name . '.license_name',
                    $table_name . '.form_name',

                    'tnelb_license.license_number'
                )
                ->first();

            $staffDetails = DB::table('tnelb_applicant_cl_staffdetails')
                ->where('application_id', $application_id)
                ->orderBy('id')
                ->get();
        }

        // ---------------------------------------
        // 3. RENEWAL APPLICATION
        // ---------------------------------------
        else {

            $applicant = DB::table('tnelb_renewal_license')
                ->join($table_name, 'tnelb_renewal_license.application_id', '=', $table_name . '.application_id')
                ->where('tnelb_renewal_license.application_id', $application_id)
                ->select(
                    'tnelb_renewal_license.application_id',
                    'tnelb_renewal_license.issued_by',
                    'tnelb_renewal_license.issued_at',
                    'tnelb_renewal_license.expires_at',

                    $table_name . '.applicant_name AS name',
                    $table_name . '.license_name',
                    $table_name . '.form_name',

                    'tnelb_renewal_license.license_number'
                )
                ->first();

            $staffDetails = DB::table('tnelb_applicant_cl_staffdetails')
                ->where('application_id', $application_id)
                ->orderBy('id')
                ->get();
        }


        // ---------------------------------------
        // 4. RETURN OR LOAD PDF VIEW
        // ---------------------------------------
   $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
               'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            
            'mode' => 'utf-8',

            'fontDir' => array_merge($fontDirs, [
                public_path('fonts'),
            ]),

            'fontdata' => $fontData + [
                'marutham' => [
                    'R' => 'Marutham.ttf',
                ],
            ],

            'default_font' => 'marutham',

            'autoScriptToLang' => true,
            'autoLangToFont'   => true,
        ]);
  

        $mpdf->SetTitle('TNELB Application License ' . $applicant->license_name);

        $formname = $applicant->form_name;

        // dd($formname);
        // exit;

        $license_name = DB::table('mst_licences')->where('form_code', $formname)->first();


        // dd($license_name);
        // exit;

        $mpdf->WriteHTML('<style>
        body { }
        p, td, th { padding: 0px; }
        p {font-size:15px;}
        .tbl_center { text-align: center;!important }
        .mt-2 { margin-top: 5; }
        table { border-collapse: collapse; width: 100%; }
        th, td {  padding: 8px; text-align:left; }
        .highlight { font-weight: bold; color: white; background-color: green; padding: 5px; text-align:center; font-size:16px; }
        .photo-container { text-align: right; padding-right: 10px; }
        .photo-container img { width: 132px; height: 170px; border: 1px solid #000; object-fit: cover; display: block; }
        .highlight_text{color:green;}
        .staff_tbl td{text-align:center;}
        .license_name{ font-size:18px;text-align:center;font-weight:bold; }
        .font-weight{font-weight:bold;}
        .blue{color:#000; font-size:18px; font-weight:bold;}
        .orange{color:#000;}
        .txt_uppercase{text-transform:uppercase;}
        .line-height-30{line-height:20px;}
        .text-indent-40 {text-indent: 40px;}
        .text-indent-60 {text-indent: 50px;}
        .text-justify {text-align: justify;}
        .font-size-14{ font-size:15px;}
        .mb-5{margin-bottom:5px;}
        .mb-1{margin-bottom:1px;}
        .mb-10{margin-bottom:10px;}
        .pb-10{padding: bottom 10px!important;}
        .mt-5{margin-top:5px;}
        .mt-1{margin-top:1px;}
        .text-black{color:black;}
        .text-uppercase{text-transform:uppercase;}
        .font-size-16{font-size:16px;}
        .font-weight-n{font-weight:normal}
        b{
        font-size:18px;
        }
        .ft-wt-18{font-size:18px;}
        
    </style>', \Mpdf\HTMLParserMode::HEADER_CSS);
        $grade_name = $applicant->license_name;
        // dd($grade_name);
        // exit;

       if ($grade_name == 'EA') {
            $grade_name_txt = '"இஏ" கிரேடு மின் ஒப்பந்தக்காரர் உரிமம்';
        } elseif ($grade_name == 'ESA') {
            $grade_name_txt = '"இஎஸ்ஏ" கிரேடு மின் ஒப்பந்தக்காரர் உரிமம்';
        } elseif ($grade_name == 'ESB') {
            $grade_name_txt = '"இஎஸ்பி" கிரேடு மின் ஒப்பந்தக்காரர் உரிமம்';
        } elseif ($grade_name == 'EB') {
            $grade_name_txt = '"இபி" கிரேடு மின் ஒப்பந்தக்காரர் உரிமம்';
        }
        $qrData = url('/verify-certificate/' . $applicant->application_id);



        // Start building the PDF content
        $html = '
<table width="100%" class="mt-1 mb-1">
    <tr>
        <!-- LEFT CONTENT -->
        <td  style="text-align:center; padding-top:10px;">

            <div class="header-block">
                <h3 class="blue mb-10 pb-10 ">தமிழ்நா அர</h3>
                <br>

                <h3 class="blue mb-10 pb-10">மின் உரிமம் வழங்ம் வாரியம் </h3>
                <br>

                <h3 class="blue mb-10 pb-10">
                    திரு.வி.க. தொழிற்பேட்டை, கிண்டி, சென்னை -600 032.
                </h3>
                <br>

                <p class="license_name orange mt-10 text-uppercase">
                    ' . $grade_name_txt . '
                </p>
            </div>

        </td>

       

  
    </tr>
</table>';

        $proprietors = DB::table('proprietordetailsform_A')
            ->where('application_id', $application_id)
            ->where('proprietor_flag', '1')
            ->orderBy('id')
            ->get();
        $proprietorNames = '';

        if ($proprietors->count() > 0) {
            foreach ($proprietors as $proprietor) {
                $proprietorNames .= strtoupper($proprietor->proprietor_name) . ', ';
            }

            // Remove last comma
            $proprietorNames = rtrim($proprietorNames, ', ');
        } else {
            $proprietorNames = '—';
        }

 

$html .= '
<table style="width:100%; border-collapse:collapse;" class="mt-1 mb-1">
    <tr>
        <!-- LEFT SIDE DATA -->
        <td style="width:80%; vertical-align:top;">
            <table style="width:100%;">
               
    <tr>
        <td style="width:40%;"><b>உரிமம் எண்</b></td>
        <td>: ' . $applicant->license_number . '</td>
    </tr>

    <tr>
        <td><b>வழங்கிய நாள்</b></td>
        <td>: ' . format_date($applicant->issued_at) . '</td>
    </tr>

    <tr>
        <td><b>செல்லுபடியாகும் காலம்</b></td>
        <td>: ' . format_date($applicant->issued_at) . ' To ' . format_date($applicant->expires_at) . '</td>
    </tr>

    <tr>
        <td><b>பெயர்</b></td>
        <td>: ' . strtoupper($applicant->name) . '</td>
    </tr>

    <tr>
        <td><b>உரிமையாளர் / பங்குதாரர் / இயக்குநரின் பெயர்</b></td>
        <td>: ' . $proprietorNames . '</td>
    </tr>
            </table>
        </td>

        <!-- RIGHT SIDE QR -->
        <td style="width:20%; text-align:right; vertical-align:top;">
            <barcode 
                code="' . htmlspecialchars($qrData) . '" 
                type="QR" 
                size="1.5"
                error="H"
            />
        </td>
    </tr>
</table>';

        $html .= '
<h4 class="mt-2 orange font-size-14 font-weight ft-wt-18">பணியாளர்கள் விவரங்கள்</h4>

<table border="1" cellpadding="6" cellspacing="0" style="width:100%; border-collapse:collapse; text-align:center;">

    <tr style="background-color:#f2f2f2;">
        <th class="font-weight-n">S.No</th>
        <th class="font-weight-n">Name</th>
        <th class="font-weight-n">Certificate Number</th>
        <th class="font-weight-n">Certificate Validity</th>
    </tr>';

       if ($staffDetails->count() > 0) {
    $i = 1;
    foreach ($staffDetails as $staff) {

        $html .= '
        <tr>
            <td>' . $i++ . '</td>
            <td>' . strtoupper($staff->staff_name) . '</td>
            <td>' . $staff->cc_number . '</td>
            <td>' . format_date($staff->cc_validity) . '</td>
        </tr>';
    }
} else {
    $html .= '
    <tr>
        <td colspan="4">No Staff Details Available</td>
    </tr>';
}
        $html .= '</table>';


        $equiplist = Mst_equipment_tbl::where('equip_licence_name', 8)
            ->where('status', 1)
            ->orderBy('id')
            ->get();

        $equipmentlist = DB::table('tnelb_equimentsuser_cl')

            ->where('application_id', $application_id)
            ->get();

        /*
            Create map:
            equip_id => equipment_value
            */
        $equipmentMap = collect($equipmentlist)->keyBy('equipment_id');

        $html .= ' <h4 class="ft-wt-18 mt-2 orange font-size-14 font-weight">உபகரணங்கள் / கருவிகளின் பட்டியல்</h4>';

        $html .= '
        <table border="1" cellpadding="4" cellspacing="0" width="100%">
            <thead>
                <tr style="font-weight:bold; background-color:#f2f2f2;">
                    <th class="font-weight-n">S.No</th>
                    <th class="font-weight-n">Equipment Name</th>
                    <th class="font-weight-n">Equipment Type</th>
                    <th  align="center" class="font-weight-n">Serial No</th>
                    <th align="center" class="font-weight-n">	Make Model</th>
                    <th  align="center" class="font-weight-n">Date of Test</th>
                </tr>
            </thead>
            <tbody>';

        $slno = 1;

        foreach ($equiplist as $equip) {

            $userEquip = $equipmentMap[$equip->id] ?? null;

            $serial = $userEquip->serial_no ?? '';
            $model  = $userEquip->model_no ?? '';
            $date   = $userEquip->dateoftest ?? '';

            $html .= '
            <tr>
                <td align="center">' . $slno . '</td>
                <td align="center">' . $equip->equip_name . '</td>
                <td align="center">' . $equip->equipment_type . '</td>
                <td align="center">' . $serial . '</td>
                <td align="center">' . $model . '</td>
                <td align="center">' . $date . '</td>
            </tr>';
            $slno++;
        }

        $html .= '
    </tbody>
</table>';


        // $html .= '<h4 class="mt-2 highlight"> Payment Details</h4>
        // <table class="tbl_center bank">
        //     <tr>
        //         <th class="">Bank Name</th>
        //         <th class="">Mode of Payment</th>
        //         <th class="">Amount</th>
        //         <th class="">Payment Date</th>
        //         <th class="">Transaction ID</th>
        //     </tr>
        //     <tr>
        //         <td>State Bank of India</td>
        //         <td>UPI</td>
        //         <td>' . ($payment->amount ?? 'N/A') . '</td>
        //         <td>25-02-2025</td>
        //         <td>' . ($payment->transaction_id ?? 'N/A') . '</td>
        //     </tr>
        // </table>';

        // Declaration


        // Write HTML to PDF
        $mpdf->WriteHTML($html);



        // Footer section
        $mpdf->SetFooter('
        <table style="width:100%; font-size:12px;">
            <tr>
                <td style="text-align:left;">TNELB</td>
                <td class="label" style="text-align:right;">Date : ' . date('d-m-Y') . '</td>
                
            </tr>
        </table>
        ');

        return response($mpdf->Output('License_A_approval.pdf', 'I'))
            ->header('Content-Type', 'application/pdf');
    }
}
