<?php

namespace App\Http\Controllers\Admin;

use App\Models\Mst_Form_s_w;
use App\Models\Mst_education;
use App\Models\Mst_experience;
use App\Models\Mst_documents;
use App\Models\MstLicence;
use App\Models\TnelbApplicantPhoto;
use App\Models\TnelbApplicantsSign;
use App\Models\TnelbAppsInstitute;
use App\Models\TnelbFormP;
use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;

use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output;

class LicensepdfController extends Controller
{

    public function getLicenceDoc($application_id)
    {
        // Fetch application details
        $application = DB::table('tnelb_application_tbl')
        ->where('application_id', $application_id)
        ->first();

        

        if ($application && $application->appl_type === 'R') {
            // Renewal application → use tnelb_renewal_license
            $applicant = DB::table('tnelb_application_tbl')
                ->join('tnelb_renewal_license', 'tnelb_renewal_license.application_id', '=', 'tnelb_application_tbl.application_id')
                ->where('tnelb_application_tbl.application_id', $application_id)
                ->select(
                    'tnelb_application_tbl.application_id',
                    'tnelb_application_tbl.applicant_name AS name',
                    'tnelb_application_tbl.fathers_name',
                    'tnelb_application_tbl.applicants_address',
                    'tnelb_application_tbl.d_o_b',
                    'tnelb_application_tbl.age',
                    'tnelb_application_tbl.license_name',
                    'tnelb_application_tbl.form_name',
                    'tnelb_renewal_license.license_number',
                    'tnelb_renewal_license.issued_by',
                    'tnelb_renewal_license.issued_at',
                    'tnelb_renewal_license.expires_at'
                )
                ->first();
        } else {
            // New application → use tnelb_license
            $applicant = DB::table('tnelb_application_tbl')
                ->join('tnelb_license', 'tnelb_license.application_id', '=', 'tnelb_application_tbl.application_id')
                ->where('tnelb_application_tbl.application_id', $application_id)
                ->select(
                    'tnelb_application_tbl.application_id',
                    'tnelb_application_tbl.applicant_name AS name',
                    'tnelb_application_tbl.fathers_name',
                    'tnelb_application_tbl.applicants_address',
                    'tnelb_application_tbl.d_o_b',
                    'tnelb_application_tbl.age',
                    'tnelb_application_tbl.license_name',
                    'tnelb_application_tbl.form_name',
                    'tnelb_license.license_number',
                    'tnelb_license.issued_by',
                    'tnelb_license.issued_at',
                    'tnelb_license.expires_at'
                )
                ->first();
        }
    
        if (!$applicant) {
            return back()->with('error', 'Application not found.');
        }
    
        // Fetch Payment Details
        $payment = DB::table('payments')->where('application_id', $application_id)->first();
    
        // Initialize mPDF
        $mpdf = new Mpdf(['default_font_size' => 10]);
        $mpdf->SetTitle('TNELB Application License ' . $applicant->license_name);

        
        $mpdf->WriteHTML('<style>
        body { line-height: 1.5; }
        p, td, th { padding: 5px; }
        .tbl_center { text-align: center; }
        .mt-2 { margin-top: 10px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 8px; }
        .highlight { font-weight: bold; color: black; background-color: #ddbe12; padding: 5px; }
        .photo-container { text-align: right; padding-right: 10px; }
        .photo-container img { width: 132px; height: 170px; border: 1px solid #000; object-fit: cover; display: block; }
    </style>', \Mpdf\HTMLParserMode::HEADER_CSS);
    
        // Start building the PDF content
        $html = '
        <h3 style="text-align: center;" class="">GOVERNMENT OF TAMILNADU</h3>
        <h4 style="text-align: center;" class="">THE ELECTRICAL LICENSING BOARD</h4>
        <p style="text-align: center;">Thiru.Vi.Ka.Indl.Estate, Guindy, Chennai – 600032.</p>
        <h4 style="text-align: center;" class="">' . $applicant->form_name . ' License "' . $applicant->license_name . '"</h4>
        <p style="text-align: center;">License for Supervisor Competency Certificate</p>
        <h3 style="text-align: center;" class="">' . $applicant->license_number . '</h3>';
 

    
        $html .= '
        <h4 class="mt-2 "> License Summary</h4>
        <table>
            <tr><th class="highlight">Applicant ID</th><td>' . $applicant->application_id . '</td></tr>
            <tr><th class="highlight">Name</th><td>' . $applicant->name . '</td></tr>
            <tr><th class="highlight">License Name</th><td>' . $applicant->license_name . '</td></tr>
            <tr><th class="highlight">Issued By</th><td>' . $applicant->issued_by . '</td></tr>
            <tr><th class="highlight">Issued On</th><td>' . format_date($applicant->issued_at) . '</td></tr>
            <tr><th class="highlight">Expired On</th><td>' . format_date($applicant->expires_at) . '</td></tr>
        </table>';
    
        // Payment Details
        $html .= '<h4 class="mt-2 "> Payment Details</h4>
        <table class="tbl_center">
            <tr>
                <th class="highlight">Bank Name</th>
                <th class="highlight">Mode of Payment</th>
                <th class="highlight">Payment Date</th>
                <th class="highlight">Transaction ID</th>
            </tr>
            <tr>
                <td>State Bank of India</td>
                <td>UPI</td>
                <td>25-02-2025</td>
                <td>' . ($payment->transaction_id ?? 'N/A') . '</td>
            </tr>
        </table>';
    
        // Declaration
        $html .= '
        <br>
        <p><strong>Place:</strong> Chennai</p>
        <p><strong>Date:</strong> ' . date('d-m-Y') . '</p>';
    
        // Write HTML to PDF
        $mpdf->WriteHTML($html);

        $fileName = ($applicant->license_number ?? $application_id) . '_SUMMARY.pdf';

        return response($mpdf->Output($fileName, 'I'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');
    }


 // ---------------generate pdf -------------------------

    public function generatePDF($application_id)
    {
        
        $application = DB::table('tnelb_application_tbl')
        ->where('application_id', $application_id)
        ->first();
        $applicant_photo = TnelbApplicantPhoto::where('application_id', $application_id)->first();
        $applicant_sign  = TnelbApplicantsSign::where('application_id', $application_id)->first();
        if ($application && $application->appl_type === 'R') {
            $applicant = DB::table('tnelb_application_tbl')
            ->join('tnelb_renewal_license', 'tnelb_renewal_license.application_id', '=', 'tnelb_application_tbl.application_id')
            ->where('tnelb_application_tbl.application_id', $application_id)
            ->select(
                'tnelb_application_tbl.application_id',
                'tnelb_application_tbl.applicant_name AS name',
                'tnelb_application_tbl.fathers_name',
                'tnelb_application_tbl.applicants_address',
                'tnelb_application_tbl.d_o_b',
                'tnelb_application_tbl.age',
                'tnelb_application_tbl.license_name',
                'tnelb_application_tbl.form_name',
                'tnelb_renewal_license.license_number',
                'tnelb_renewal_license.issued_by',
                'tnelb_renewal_license.issued_at',
                'tnelb_renewal_license.expires_at'
            )
            ->first();
        } else {
            $applicant = DB::table('tnelb_application_tbl')
            ->join('tnelb_license', 'tnelb_license.application_id', '=', 'tnelb_application_tbl.application_id')
            ->where('tnelb_application_tbl.application_id', $application_id)
            ->select(
                'tnelb_application_tbl.application_id',
                'tnelb_application_tbl.applicant_name AS name',
                'tnelb_application_tbl.fathers_name',
                'tnelb_application_tbl.applicants_address',
                'tnelb_application_tbl.d_o_b',
                'tnelb_application_tbl.age',
                'tnelb_application_tbl.license_name',
                'tnelb_application_tbl.form_name',
                'tnelb_license.license_number',
                'tnelb_license.issued_by',
                'tnelb_license.issued_at',
                'tnelb_license.expires_at'
            )
            ->first();
        }
        if (!$applicant) {
            return back()->with('error', 'Application not found.');
        }
        // dd($applicant->license_name);exit;

        $licence_details = MstLicence::where('cert_licence_code', $applicant->license_name)->first();

        $licence_name = $licence_details->licence_name;

        // dd($licence_details->licence_name);exit;

        if($applicant->license_name == 'B'){
            $certificate_name = 'Electrician';
            $content_text = 'The holder of this certificate is authorized to carry out Medium and Low Voltage electrical installation works under a licensed contractor, or to perform operation and maintenance works of Medium and Low Voltage installations in the concerned establishment with due authorization.'; 
        }else if($applicant->license_name == 'H'){
             $certificate_name = 'WIREMAN HELPER';
            $content_text = 'The holder of this certificate may work as an assistant to an electrician under a licensed electrical contractor for carrying out Medium and Low Voltage electrical installation works, or may, with the authorization of the establishment, work as an assistant to an electrician in the operation and maintenance of Medium and Low Voltage installations of the establishment.'; 
        }else{
            $certificate_name = '';
            $content_text = 'This Certificate holder is permitted to supervise <strong>H.V and M.V. Electrical installation works</strong> under licensed contractor or to work as authorised person under rule 3 of Indian Electricity Rule 1956.';
        }


         $certificateRow = '';

        if (!empty($certificate_name)) {
            $certificateRow = '
                <tr>
                    <td class="lbl">Certificate</td>
                    <td class="colon">:</td>
                    <td class="val">'.$certificate_name.'</td>
                </tr>
            ';
        }
        
        // Different layout for WH form: full A4, no backside text/signatures
        if ($applicant->form_name === 'WH') {
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'orientation' => 'P',
                'margin_top' => 18,
                'margin_bottom' => 18,
                'margin_left' => 15,
                'margin_right' => 15,
            ]);

            $mpdf->SetTitle('TNELB Application License ' . $applicant->license_name);
            $mpdf->WriteHTML('
                <style>
                    @page {
                        size: A4;
                        margin: 15mm;
                    }
                    .lbl{
                        font-size: 12pt;
                    }
              
                    .val{
                        font-size: 12pt;
                    }
                    body {
                        margin: 0;
                        padding: 0;
                        font-family: helvetica, sans-serif;
                        font-size: 10pt;
                    }
                    .card {
                        width: 100%;
                        border: 0.4mm solid #000;
                        box-sizing: border-box;
                        padding: 6mm;
                    }
                    .header {
                        text-align: center;
                        font-size: 12pt;
                        font-weight: bold;
                        margin-bottom: 4mm;
                    }
                    .sub-header {
                        text-align: center;
                        font-size: 10pt;
                        margin-bottom: 6mm;
                    }
                    .content {
                        font-size: 9pt;
                    }
                    .photo {
                        width: 22mm;
                        height: 22mm;
                        border: 0.3mm solid #000;
                        box-sizing: border-box;
                        overflow: hidden;
                    }
                    .info-table {
                        font-size: 9pt;
                        border-collapse: collapse;
                    }
                    .info-table td {
                        padding: 1.5mm 1mm;
                        vertical-align: top;
                    }
                    .info-table .lbl {
                        width: 30mm;
                        font-weight: bold;
                    }
                    .info-table .colon {
                        width: 3mm;
                        text-align: center;
                    }
                    .footer {
                        margin-top: 8mm;
                        text-align: center;
                        font-size: 8pt;
                    }
                        .text-center{
                            text-align: center;
                        }
                            .pt-6{
                            padding-top: 6mm;
                            }
                    .info-table tr{
                        padding-top:50px;
                        }

                         .text-uppercase{
                            text-transform: uppercase;
                        }
                </style>
            ', \Mpdf\HTMLParserMode::HEADER_CSS);

            $photoPath = !empty($applicant_photo->upload_path) ? public_path($applicant_photo->upload_path): null;

            $signPath  = !empty($applicant_sign?->uploaded_doc) ? public_path($applicant_sign->uploaded_doc) : null;

            $qrValue = 'TNELB QR TESTING';

            $html = '

           
            <div class="card">

              <table class="header-table text-center" style="width: 100%;" >
                <tr>
                    <td style="font-size:12pt; font-weight:bold;">GOVERNMENT OF TAMILNADU</td>
                </tr>
                <tr>
                    <td style="font-size:11pt; font-weight:bold;">THE ELECTRICAL LICENCING BOARD</td>
                </tr>
                <tr>
                     <td style="font-size:11pt; font-weight:bold;">THIRU.VI.KA.INDUSTRIAL.ESTATE, GUINDY, CHENNAI – 600032.</td>
                </tr>

                <tr>
                     <td style="font-size:11pt; font-weight:bold;text-transform: uppercase;">'.$licence_name.'</td>
                </tr>
               
            </table>

              

                <div class="content pt-6" >
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="70%" valign="top">
                                <table class="info-table">
                                    <tr class="tr_padding">
                                        <td class="lbl">Licence No</td>
                                        <td class="colon">:</td>
                                        <td class="val text-uppercase">'.$applicant->license_number.'</td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">D.O.I</td>
                                        <td class="colon">:</td>
                                        <td class="val">'.date('d M Y', strtotime($applicant->issued_at)).'</td>
                                    </tr>
                                     <tr>
                                        <td class="lbl">Validity</td>
                                        <td class="colon">:</td>
                                        <td class="val">'.format_date($applicant->issued_at). '<small style="font-weight: bold;"> To </small>'. format_date($applicant->expires_at).'</td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">Name</td>
                                        <td class="colon">:</td>
                                        <td class="val text-uppercase">'.$applicant->name.'</td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">F/H Name</td>
                                        <td class="colon">:</td>
                                        <td class="val text-uppercase">'.$applicant->fathers_name.'</td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">Date of Birth</td>
                                        <td class="colon">:</td>
                                        <td class="val">'.date('d M Y', strtotime($applicant->d_o_b)).'</td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">Address</td>
                                        <td class="colon">:</td>
                                        <td class="val text-uppercase">'.$applicant->applicants_address.'</td>
                                    </tr>
                                    
                                </table>


                          

                            </td>

                            <!-- RIGHT : PHOTO -->
                            <td width="30%" valign="top">
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <!-- PHOTO ROW -->
                                    <tr>
                                        <td align="center">
                                            <div class="photo">
                                                '.($photoPath
                                                    ? '<img src="'.$photoPath.'" style="width:52mm; height:52mm; object-fit:cover;">'
                                                    : '').'
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="center">
                                            <div class="photo">
                                                '.($signPath
                                                    ? '<img src="'.$signPath.'" style="width:52mm; height:20mm; object-fit:cover;">'
                                                    : '').'
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- SPACE BETWEEN PHOTO & QR -->
                                    <tr>
                                        <td height="3mm"></td>
                                    </tr>

                                    <!-- QR ROW -->
                                    <tr>
                                        <td align="center">
                                            <barcode code="'.$qrValue.'" type="QR" size="1.6" error="M" />
                                        </td>
                                    </tr>

                                    <!-- BOTTOM SAFE SPACE -->
                                    <tr>
                                        <td height="4mm"></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                </div>

                <!-- FOOTER -->
                <div class="footer">
                    Issued by TNELB | Tamil Nadu
                </div>

            </div>
            ';
            
            $mpdf->WriteHTML($html);
        } else {
            // A4 layout for all other forms
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'orientation' => 'P',
                'margin_top' => 18,
                'margin_bottom' => 18,
                'margin_left' => 15,
                'margin_right' => 15,
            ]);

            $mpdf->SetTitle('TNELB Application License ' . $applicant->license_name);
            $mpdf->WriteHTML('
                <style>
                    @page {
                        size: A4;
                        margin: 15mm;
                    }
                    .lbl{
                        font-size: 12pt;
                    }
              
                    .val{
                        font-size: 12pt;
                    }
                    body {
                        margin: 0;
                        padding: 0;
                        font-family: helvetica, sans-serif;
                        font-size: 10pt;
                    }
                    .card {
                        width: 100%;
                        border: 0.4mm solid #000;
                        box-sizing: border-box;
                        padding: 6mm;
                    }
                    .header {
                        text-align: center;
                        font-size: 12pt;
                        font-weight: bold;
                        margin-bottom: 4mm;
                    }
                    .sub-header {
                        text-align: center;
                        font-size: 10pt;
                        margin-bottom: 6mm;
                    }
                    .content {
                        font-size: 9pt;
                    }
                    .photo {
                        width: 22mm;
                        height: 22mm;
                        border: 0.3mm solid #000;
                        box-sizing: border-box;
                        overflow: hidden;
                    }
                    .info-table {
                        font-size: 9pt;
                        border-collapse: collapse;
                    }
                    .info-table td {
                        padding: 1.5mm 1mm;
                        vertical-align: top;
                    }
                    .info-table .lbl {
                        width: 30mm;
                        font-weight: bold;
                    }
                    .info-table .colon {
                        width: 3mm;
                        text-align: center;
                    }
                    .footer {
                        margin-top: 8mm;
                        text-align: center;
                        font-size: 8pt;
                    }
                        .text-center{
                            text-align: center;
                        }
                            .pt-6{
                            padding-top: 6mm;
                            }
                    .info-table tr{
                        padding-top:50px;
                        }

                         .text-uppercase{
                            text-transform: uppercase;
                        }
                </style>
            ', \Mpdf\HTMLParserMode::HEADER_CSS);

            $photoPath = !empty($applicant_photo->upload_path) ? public_path($applicant_photo->upload_path): null;

            $signPath  = !empty($applicant_sign?->uploaded_doc) ? public_path($applicant_sign->uploaded_doc) : null;

            $qrValue = 'TNELB QR TESTING';

            $html = '

           
            <div class="card">

              <table class="header-table text-center" style="width: 100%;" >
                <tr>
                    <td style="font-size:12pt; font-weight:bold;">GOVERNMENT OF TAMILNADU</td>
                </tr>
                <tr>
                    <td style="font-size:11pt; font-weight:bold;">THE ELECTRICAL LICENCING BOARD</td>
                </tr>
                <tr>
                     <td style="font-size:11pt; font-weight:bold;">THIRU.VI.KA.INDUSTRIAL.ESTATE, GUINDY, CHENNAI – 600032.</td>
                </tr>

                <tr>
                     <td style="font-size:11pt; font-weight:bold; text-transform: uppercase;">'.$licence_name.'</td>
                </tr>
               
            </table>

              

                <div class="content pt-6" >
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="70%" valign="top">
                                <table class="info-table">
                                    <tr class="tr_padding">
                                        <td class="lbl">Licence No</td>
                                        <td class="colon">:</td>
                                        <td class="val text-uppercase">'.$applicant->license_number.'</td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">D.O.I</td>
                                        <td class="colon">:</td>
                                        <td class="val">'.date('d M Y', strtotime($applicant->issued_at)).'</td>
                                    </tr>
                                     <tr>
                                        <td class="lbl">Validity</td>
                                        <td class="colon">:</td>
                                        <td class="val">'.format_date($applicant->issued_at). '<small style="font-weight: bold;"> To </small>'. format_date($applicant->expires_at).'</td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">Name</td>
                                        <td class="colon">:</td>
                                        <td class="val text-uppercase">'.$applicant->name.'</td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">F/H Name</td>
                                        <td class="colon">:</td>
                                        <td class="val text-uppercase">'.$applicant->fathers_name.'</td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">Date of Birth</td>
                                        <td class="colon">:</td>
                                        <td class="val">'.date('d M Y', strtotime($applicant->d_o_b)).'</td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">Address</td>
                                        <td class="colon">:</td>
                                        <td class="val text-uppercase">'.$applicant->applicants_address.'</td>
                                    </tr>'
                                    .$certificateRow.'
                                </table>


                          

                            </td>

                            <!-- RIGHT : PHOTO -->
                            <td width="30%" valign="top">
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <!-- PHOTO ROW -->
                                    <tr>
                                        <td align="center">
                                            <div class="photo">
                                                '.($photoPath
                                                    ? '<img src="'.$photoPath.'" style="width:55mm; height:55mm; object-fit:cover;">'
                                                    : '').'
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="center">
                                            <div class="photo">
                                                '.($signPath
                                                    ? '<img src="'.$signPath.'" style="width:52mm; height:20mm; object-fit:cover;">'
                                                    : '').'
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- SPACE BETWEEN PHOTO & QR -->
                                    <tr>
                                        <td height="3mm"></td>
                                    </tr>

                                    <!-- QR ROW -->
                                    <tr>
                                        <td align="center">
                                            <barcode code="'.$qrValue.'" type="QR" size="1.6" error="M" />
                                        </td>
                                    </tr>

                                    <!-- BOTTOM SAFE SPACE -->
                                    <tr>
                                        <td height="4mm"></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                </div>

                <!-- FOOTER -->
                <div class="footer">
                    Issued by TNELB | Tamil Nadu
                </div>

            </div>
            ';
            
            $mpdf->WriteHTML($html);
            
            
            
        }
        $fileNameEn = ($applicant->license_number ?? $application_id) . '_EN.pdf';
        $pdfBinaryEn = $mpdf->Output($fileNameEn, 'S');

        // Build Tamil PDF in the same request
        $pdfBinaryTa = $this->generateLicenceTamil($application_id, true);

        // Encrypt and store both securely
        $encryptedPathEn = 'private_documents/license_pdfs/' . $application_id . '_en.pdf.enc';
        Storage::disk('local')->put($encryptedPathEn, Crypt::encryptString($pdfBinaryEn));

        $encryptedPathTa = null;
        if (is_string($pdfBinaryTa) && $pdfBinaryTa !== '') {
            $encryptedPathTa = 'private_documents/license_pdfs/' . $application_id . '_ta.pdf.enc';
            Storage::disk('local')->put($encryptedPathTa, Crypt::encryptString($pdfBinaryTa));
        }

        // Save paths to `tnelb_license.license_pdf_en` / `license_pdf_ta`
        $this->storeEncryptedLicensePdfPath($application_id, $encryptedPathEn, $encryptedPathTa);

        // Stream English PDF to browser (existing behavior)
        return response($pdfBinaryEn)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $fileNameEn . '"');
    }
    
    public function generatePDF11($application_id)
    {
        
        $application = DB::table('tnelb_application_tbl')
        ->where('application_id', $application_id)
        ->first();
        $applicant_photo = TnelbApplicantPhoto::where('application_id', $application_id)->first();
        $applicant_sign  = TnelbApplicantsSign::where('application_id', $application_id)->first();
        if ($application && $application->appl_type === 'R') {
            $applicant = DB::table('tnelb_application_tbl')
            ->join('tnelb_renewal_license', 'tnelb_renewal_license.application_id', '=', 'tnelb_application_tbl.application_id')
            ->where('tnelb_application_tbl.application_id', $application_id)
            ->select(
                'tnelb_application_tbl.application_id',
                'tnelb_application_tbl.applicant_name AS name',
                'tnelb_application_tbl.fathers_name',
                'tnelb_application_tbl.applicants_address',
                'tnelb_application_tbl.d_o_b',
                'tnelb_application_tbl.age',
                'tnelb_application_tbl.license_name',
                'tnelb_application_tbl.form_name',
                'tnelb_renewal_license.license_number',
                'tnelb_renewal_license.issued_by',
                'tnelb_renewal_license.issued_at',
                'tnelb_renewal_license.expires_at'
            )
            ->first();
        } else {
            $applicant = DB::table('tnelb_application_tbl')
            ->join('tnelb_license', 'tnelb_license.application_id', '=', 'tnelb_application_tbl.application_id')
            ->where('tnelb_application_tbl.application_id', $application_id)
            ->select(
                'tnelb_application_tbl.application_id',
                'tnelb_application_tbl.applicant_name AS name',
                'tnelb_application_tbl.fathers_name',
                'tnelb_application_tbl.applicants_address',
                'tnelb_application_tbl.d_o_b',
                'tnelb_application_tbl.age',
                'tnelb_application_tbl.license_name',
                'tnelb_application_tbl.form_name',
                'tnelb_license.license_number',
                'tnelb_license.issued_by',
                'tnelb_license.issued_at',
                'tnelb_license.expires_at'
            )
            ->first();
        }
        if (!$applicant) {
            return back()->with('error', 'Application not found.');
        }

        if($applicant->license_name == 'B'){
            $certificate_name = 'Electrician';
            $content_text = 'The holder of this certificate is authorized to carry out Medium and Low Voltage electrical installation works under a licensed contractor, or to perform operation and maintenance works of Medium and Low Voltage installations in the concerned establishment with due authorization.'; 
        }else if($applicant->license_name == 'H'){
             $certificate_name = 'WIREMAN HELPER';
            $content_text = 'The holder of this certificate may work as an assistant to an electrician under a licensed electrical contractor for carrying out Medium and Low Voltage electrical installation works, or may, with the authorization of the establishment, work as an assistant to an electrician in the operation and maintenance of Medium and Low Voltage installations of the establishment.'; 
        }else{
            $certificate_name = '';
            $content_text = 'This Certificate holder is permitted to supervise <strong>H.V and M.V. Electrical installation works</strong> under licensed contractor or to work as authorised person under rule 3 of Indian Electricity Rule 1956.';
        }


         $certificateRow = '';

        if (!empty($certificate_name)) {
            $certificateRow = '
                <tr>
                    <td class="lbl">Certificate</td>
                    <td class="colon">:</td>
                    <td class="val">'.$certificate_name.'</td>
                </tr>
            ';
        }
        
        // Different layout for WH form: full A4, no backside text/signatures
        if ($applicant->form_name === 'WH') {
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'orientation' => 'P',
                'margin_top' => 18,
                'margin_bottom' => 18,
                'margin_left' => 15,
                'margin_right' => 15,
            ]);

            $mpdf->SetTitle('TNELB Application License ' . $applicant->license_name);
            $mpdf->WriteHTML('
                <style>
                    body {
                        font-family: helvetica, sans-serif;
                        font-size: 10pt;
                    }
                    .heading-main {
                        text-align: center;
                        font-size: 14pt;
                        font-weight: bold;
                    }
                    .heading-sub {
                        text-align: center;
                        font-size: 11pt;
                        margin-bottom: 8px;
                    }
                    .cert-title {
                        text-align: center;
                        font-size: 12pt;
                        font-weight: bold;
                        text-decoration: underline;
                        margin-bottom: 12px;
                    }
                    .info-table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 10pt;
                    }
                    .info-table td {
                        padding: 3px 4px;
                        vertical-align: top;
                    }
                    .lbl {
                        width: 22%;
                        font-weight: bold;
                    }
                    .colon {
                        width: 2%;
                        text-align: center;
                    }
                    .photo-box {
                        width: 30mm;
                        height: 35mm;
                        border: 0.3mm solid #000;
                        margin-bottom: 6mm;
                    }
                    .sign-box {
                        width: 30mm;
                        height: 12mm;
                        border: 0.3mm solid #000;
                        margin-top: 3mm;
                    }
                </style>
            ', \Mpdf\HTMLParserMode::HEADER_CSS);

            $photoPath = !empty($applicant_photo->upload_path) ? public_path($applicant_photo->upload_path): null;
            $signPath  = !empty($applicant_sign?->uploaded_doc) ? public_path($applicant_sign->uploaded_doc) : null;
            $qrValue = 'TNELB QR TESTING';

            $html = '
                <div class="heading-main">
                    TAMIL NADU ELECTRICAL LICENCING BOARD
                </div>
                <div class="heading-sub">
                    Thiru Vi. Ka. Indl. Estate, Guindy, Chennai - 600 032.
                </div>
                <div class="cert-title">
                    WIREMAN HELPER LICENCE
                </div>

                <table width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td width="70%" valign="top">
                            <table class="info-table">
                                <tr>
                                    <td class="lbl">WH.No</td>
                                    <td class="colon">:</td>
                                    <td>' . ($applicant->license_number ?? '') . '</td>
                                </tr>
                                <tr>
                                    <td class="lbl">D.O.I</td>
                                    <td class="colon">:</td>
                                    <td>' . (!empty($applicant->issued_at) ? date('d M Y', strtotime($applicant->issued_at)) : '') . '</td>
                                </tr>
                                <tr>
                                    <td class="lbl">Validity</td>
                                    <td class="colon">:</td>
                                    <td>' . (isset($applicant->issued_at, $applicant->expires_at)
                                        ? format_date($applicant->issued_at) . ' To ' . format_date($applicant->expires_at)
                                        : '') . '</td>
                                </tr>
                                <tr>
                                    <td class="lbl">Name</td>
                                    <td class="colon">:</td>
                                    <td>' . e($applicant->name) . '</td>
                                </tr>
                                <tr>
                                    <td class="lbl">F/H Name</td>
                                    <td class="colon">:</td>
                                    <td>' . e($applicant->fathers_name) . '</td>
                                </tr>
                                <tr>
                                    <td class="lbl">Date of Birth</td>
                                    <td class="colon">:</td>
                                    <td>' . (!empty($applicant->d_o_b) ? date('d M Y', strtotime($applicant->d_o_b)) : '') . '</td>
                                </tr>
                                <tr>
                                    <td class="lbl">Address</td>
                                    <td class="colon">:</td>
                                    <td>' . nl2br(e($applicant->applicants_address)) . '</td>
                                </tr>
                            </table>
                        </td>
                        <td width="30%" valign="top" align="center">
                            <div class="photo-box" style="margin-bottom:4mm;">
                                ' . ($photoPath ? '<img src="' . $photoPath . '" style="width:30mm; height:35mm; object-fit:cover;">' : '') . '
                            </div>
                            <div class="sign-box">
                                ' . ($signPath ? '<img src="' . $signPath . '" style="width:30mm; height:12mm; object-fit:contain;">' : '') . '
                            </div>
                            <div style="font-size:8pt; margin-top:2mm;">
                                Signature of Applicant
                            </div>
                            <div style="margin-top:4mm;">
                                <barcode code="' . $qrValue . '" type="QR" size="0.8" error="M" />
                            </div>
                        </td>
                    </tr>
                </table>
            ';

            $mpdf->WriteHTML($html);
        } else {
            // A4 layout for all other forms
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'orientation' => 'P',
                'margin_top' => 18,
                'margin_bottom' => 18,
                'margin_left' => 15,
                'margin_right' => 15,
            ]);

            $mpdf->SetTitle('TNELB Application License ' . $applicant->license_name);
            $mpdf->WriteHTML('
                <style>
                    @page {
                        size: A4;
                        margin: 15mm;
                    }
                    body {
                        margin: 0;
                        padding: 0;
                        font-family: helvetica, sans-serif;
                        font-size: 10pt;
                    }
                    .card {
                        width: 100%;
                        border: 0.4mm solid #000;
                        box-sizing: border-box;
                        padding: 6mm;
                    }
                    .header {
                        text-align: center;
                        font-size: 12pt;
                        font-weight: bold;
                        margin-bottom: 4mm;
                    }
                    .sub-header {
                        text-align: center;
                        font-size: 10pt;
                        margin-bottom: 6mm;
                    }
                    .content {
                        font-size: 9pt;
                    }
                    .photo {
                        width: 22mm;
                        height: 22mm;
                        border: 0.3mm solid #000;
                        box-sizing: border-box;
                        overflow: hidden;
                    }
                    .info-table {
                        font-size: 9pt;
                        border-collapse: collapse;
                    }
                    .info-table td {
                        padding: 1.5mm 1mm;
                        vertical-align: top;
                    }
                    .info-table .lbl {
                        width: 30mm;
                        font-weight: bold;
                    }
                    .info-table .colon {
                        width: 3mm;
                        text-align: center;
                    }
                    .footer {
                        margin-top: 8mm;
                        text-align: center;
                        font-size: 8pt;
                    }
                </style>
            ', \Mpdf\HTMLParserMode::HEADER_CSS);

            $photoPath = !empty($applicant_photo->upload_path) ? public_path($applicant_photo->upload_path): null;

            $qrValue = 'TNELB QR TESTING';

            $html = '
            <div class="card">

                <div class="header">
                    TAMIL NADU ELECTRICAL LICENCING BOARD
                </div>
                <div class="sub-header">
                    Thiru Vi. Ka. Indl. Estate, Guindy, Chennai - 600 032.
                </div>

                <div class="content">
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="70%" valign="top">
                                <table class="info-table">
                                    <tr>
                                        <td class="lbl">Licence No</td>
                                        <td class="colon">:</td>
                                        <td class="val">'.$applicant->license_number.'</td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">D.O.I</td>
                                        <td class="colon">:</td>
                                        <td class="val">'.date('d M Y', strtotime($applicant->issued_at)).'</td>
                                    </tr>
                                     <tr>
                                        <td class="lbl">Validity</td>
                                        <td class="colon">:</td>
                                        <td class="val">'.format_date($applicant->issued_at). '<small style="font-weight: bold;"> To </small>'. format_date($applicant->expires_at).'</td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">Name</td>
                                        <td class="colon">:</td>
                                        <td class="val">'.$applicant->name.'</td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">F/H Name</td>
                                        <td class="colon">:</td>
                                        <td class="val">'.$applicant->fathers_name.'</td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">Date of Birth</td>
                                        <td class="colon">:</td>
                                        <td class="val">'.date('d M Y', strtotime($applicant->d_o_b)).'</td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">Address</td>
                                        <td class="colon">:</td>
                                        <td class="val">'.$applicant->applicants_address.'</td>
                                    </tr>'
                                    .$certificateRow.'
                                </table>

                            </td>

                            <!-- RIGHT : PHOTO -->
                            <td width="30%" valign="top">
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <!-- PHOTO ROW -->
                                    <tr>
                                        <td align="center">
                                            <div class="photo">
                                                '.($photoPath
                                                    ? '<img src="'.$photoPath.'" style="width:22mm; height:22mm; object-fit:cover;">'
                                                    : '').'
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- SPACE BETWEEN PHOTO & QR -->
                                    <tr>
                                        <td height="3mm"></td>
                                    </tr>

                                    <!-- QR ROW -->
                                    <tr>
                                        <td align="center">
                                            <barcode code="'.$qrValue.'" type="QR" size="0.6" error="M" />
                                        </td>
                                    </tr>

                                    <!-- BOTTOM SAFE SPACE -->
                                    <tr>
                                        <td height="4mm"></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                </div>

                <!-- FOOTER -->
                <div class="footer">
                    Issued by TNELB | Tamil Nadu
                </div>

            </div>
            ';
            
            $mpdf->WriteHTML($html);
            $mpdf->AddPage('L');
            $backHtml = '
                <div class="card">
            
                    <div class="content" style="font-size:9.5pt; line-height:1.4;">
            
                        <div style="text-align:right; font-size:7pt; margin-bottom:2mm;">
                            Visit us at : www.tnelb.gov.in
                        </div>
            
                        <div style="margin-top:4mm;text-align: justify;">
                            ' . $content_text . '   
                        </div>
            
                        <br><br><br><br>
            
                        <!-- SIGNATURE AREA -->
                        <table width="100%" style="margin-top:15mm;">
                            <tr>
                                <td width="45%" style="text-align:left;">
                                    <div style="height:12mm;"></div>
                                    <strong>Secretary</strong>
                                </td>
            
                                <td width="55%" style="text-align:right;">
                                    <div style="height:12mm;"></div>
                                    <strong>President</strong>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>';
            $mpdf->WriteHTML($backHtml);
        }
        $fileNameEn = ($applicant->license_number ?? $application_id) . '_EN.pdf';
        $pdfBinaryEn = $mpdf->Output($fileNameEn, 'S');

        // Build Tamil PDF in the same request
        $pdfBinaryTa = $this->generateLicenceTamil($application_id, true);

        // Encrypt and store both securely
        $encryptedPathEn = 'private_documents/license_pdfs/' . $application_id . '_en.pdf.enc';
        Storage::disk('local')->put($encryptedPathEn, Crypt::encryptString($pdfBinaryEn));

        $encryptedPathTa = null;
        if (is_string($pdfBinaryTa) && $pdfBinaryTa !== '') {
            $encryptedPathTa = 'private_documents/license_pdfs/' . $application_id . '_ta.pdf.enc';
            Storage::disk('local')->put($encryptedPathTa, Crypt::encryptString($pdfBinaryTa));
        }

        // Save paths to `tnelb_license.license_pdf_en` / `license_pdf_ta`
        $this->storeEncryptedLicensePdfPath($application_id, $encryptedPathEn, $encryptedPathTa);

        // Stream English PDF to browser (existing behavior)
        return response($pdfBinaryEn)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $fileNameEn . '"');
    }

    // ----------tamil pdf---------------
    public function generateLicenceTamil($application_id, bool $returnBinary = false)
    {
        $application = DB::table('tnelb_application_tbl')
        ->where('application_id', $application_id)
        ->first();
        $applicant_photo = TnelbApplicantPhoto::where('application_id', $application_id)->first();
        $applicant_sign  = TnelbApplicantsSign::where('application_id', $application_id)->first();
        if ($application && $application->appl_type === 'R') {
            $applicant = DB::table('tnelb_application_tbl')
            ->join('tnelb_renewal_license', 'tnelb_renewal_license.application_id', '=', 'tnelb_application_tbl.application_id')
            ->where('tnelb_application_tbl.application_id', $application_id)
            ->select(
                'tnelb_application_tbl.application_id',
                'tnelb_application_tbl.applicant_name AS name',
                'tnelb_application_tbl.fathers_name',
                'tnelb_application_tbl.applicants_address',
                'tnelb_application_tbl.d_o_b',
                'tnelb_application_tbl.age',
                'tnelb_application_tbl.license_name',
                'tnelb_application_tbl.form_name',
                'tnelb_renewal_license.license_number',
                'tnelb_renewal_license.issued_by',
                'tnelb_renewal_license.issued_at',
                'tnelb_renewal_license.expires_at'
            )
            ->first();
        } else {
            $applicant = DB::table('tnelb_application_tbl')
            ->join('tnelb_license', 'tnelb_license.application_id', '=', 'tnelb_application_tbl.application_id')
            ->where('tnelb_application_tbl.application_id', $application_id)
            ->select(
                'tnelb_application_tbl.application_id',
                'tnelb_application_tbl.applicant_name AS name',
                'tnelb_application_tbl.fathers_name',
                'tnelb_application_tbl.applicants_address',
                'tnelb_application_tbl.d_o_b',
                'tnelb_application_tbl.age',
                'tnelb_application_tbl.license_name',
                'tnelb_application_tbl.form_name',
                'tnelb_license.license_number',
                'tnelb_license.issued_by',
                'tnelb_license.issued_at',
                'tnelb_license.expires_at'
            )
            ->first();
        }
        if (!$applicant) {
            return $returnBinary ? null : back()->with('error', 'Application not found.');
        }

        $licence_details = MstLicence::where('cert_licence_code', $applicant->license_name)->first();
        $licence_name = $licence_details->licence_name;

        if($applicant->license_name == 'B'){
            $certificate_name = 'Electrician';
            $content_text = 'இச்சான்று பெற்றவர் நடுத்தர மற்றும் குறைந்த மின்னழுத்த மின்னமைப்பு பணிகளை உரிமம் பெற்றுரியின் ஒப்பந்தக்காரரின் கீழ் மேற்கொள்ளலாம் அல்லது நடுத்தர மற்றும் குறைந்த அழுத்த நிறுவனத்தின் இயக்குதல் மற்றும் பராமரிப்பு பணிகளை அந்நிறுவனத்தில் அங்கீகாரத்துடன் மேற்கொள்ளலாம்.'; 
        }else if($applicant->license_name == 'H'){
             $certificate_name = 'WIREMAN HELPER';
            $content_text = 'இச்சான்று பெற்றவர் நடுத்தர மற்றும் குறைந்த மின்னழுத்த அமைப்பு பணிகளை மேற்கோள்வதில் உரிமம் பெற்ற மின் ஒப்பந்தக்காரரிடம் மின் கம்பியாளருக்கு உதவியாளராக பணிபுரியலாம். அல்லது நடுத்தர மற்றும் குறைந்த அழுத்த நிறுவனத்தின் இயக்குதல் மற்றும் பராமரிப்பு பணியில் மின்கம்பியாளருக்கு உதவியாளராக நிறுவனத்தின் அங்கீகாரத்துடன் மேற்கொள்ளலாம்.'; 
        }else{
            $certificate_name = '';
            $content_text = 'இச்சான்றிதழ் பெற்றவர், உரிமம் பெற்ற மின் ஒப்பந்தக்காரரின் கீழ் உயர் மின்னழுத்த (H.V) மற்றும் நடுத்தர மின்னழுத்த (M.V) மின் நிறுவல் பணிகளை மேற்பார்வை செய்ய அனுமதிக்கப்படுகிறார்; அல்லது இந்திய மின்சார விதிகள், 1956 இன் விதி 3 இன் கீழ் அங்கீகரிக்கப்பட்ட நபராக பணியாற்ற அனுமதிக்கப்படுகிறார்.';
        }

        $certificateRow = '';

        if (!empty($certificate_name)) {
            $certificateRow = '
                <tr>
                    <td class="lbl">தே.சான்று எண்</td>
                    <td class="colon">:</td>
                    <td class="val">'.$certificate_name.'</td>
                </tr>
            ';
        }

        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'fontDir' => array_merge($fontDirs, [
                public_path('fonts'),
            ]),
            'fontdata' => array_merge($fontData, [
                'notosanstamil' => [
                    'R' => 'NotoSansTamil-Regular.ttf',
                ]
            ]),
            'default_font' => 'notosanstamil',
            // A4 output (required): previously CR100 card size
            'format' => 'A4',
            'orientation' => 'P',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
        ]);


        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;


        $mpdf->SetTitle('TNELB Application License ' . $applicant->license_name);
        $mpdf->WriteHTML('<style>

           .lbl{
                        font-size: 12pt;
                    }
              
                    .val{
                        font-size: 12pt;
                    }
            body { font-family: notosanstamil; font-size: 14pt; }
            .card { border: 1px solid #000; padding: 18px; box-sizing: border-box; width: 100%; }
            .header { color: #003366; text-align: center; font-size: 15pt; font-weight: bold; margin-bottom: 16px; }
            .content { font-size: 14pt; }
            /* Same layout as generateLicensePDF (EN): photo & QR 38×38 mm, no inner borders */
            .photo-frame, .qr-box {
                width: 38mm;
                height: 38mm;
                border: none;
                box-sizing: border-box;
                margin: 0 auto;
                overflow: hidden;
                padding: 0;
                text-align: center;
            }
            .photo-inner {
                width: 100%;
                height: 100%;
                overflow: hidden;
                text-align: center;
            }
            .sign-frame {
                width: 38mm;
                height: 14mm;
                border: none;
                box-sizing: border-box;
                margin: 0 auto;
                text-align: center;
            }
            .sign-inner {
                width: 100%;
                height: 100%;
                overflow: hidden;
                line-height: 14mm;
            }
            .qr-box table { border-collapse: collapse; }
            .qr-box td { padding: 0; vertical-align: middle; }
           .info-table {
                font-size: 14pt;
                border-collapse: collapse;
            }

            .info-table td { padding: 2.2mm 2mm; vertical-align: top; }

            .info-table .lbl { width: 38mm; font-weight: bold; }

            .info-table .colon {
                width: 2mm;
                text-align: center;
            }
            .footer { margin-top: 16px; text-align: center; font-size: 12pt; }
            .text-center{
                text-align: center;
            }
            </style>', \Mpdf\HTMLParserMode::HEADER_CSS);
                
        $photoPath = !empty($applicant_photo->upload_path) ? public_path($applicant_photo->upload_path): null;
        $signPath  = !empty($applicant_sign?->uploaded_doc) ? public_path($applicant_sign->uploaded_doc) : null;
        $qrValue = 'sdfdgsdg'; 

        $html = '
        <div class="card">

            <!-- HEADER -->
            <div class="header">
                <table class="header text-center" style="width: 100%;" >
                <tr>
                    <td >தமிழ்நாடு அரசு</td>
                </tr>
                <tr>
                    <td >மின்சார உரிைமயாளர்கள் வாரியம்</td>
                </tr>
                <tr>
                     <td >திரு.வி.க. தொழிற்பேட்டை, கிண்டி, சென்னை – 32.</td>
                </tr>

                <tr>
                     <td >'.$licence_name.'</td>
                </tr>
               
            </table>
                
            </div>

           

            <!-- BODY -->
            <div class="content">

               <table width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <!-- LEFT : DETAILS -->
                        <td width="70%" valign="top">

                            <table class="info-table">
                                <tr>
                                    <td class="lbl">த. சா. எண்</td>
                                    <td class="colon">:</td>
                                    <td class="val">'.$applicant->license_number.'</td>
                                </tr>
                                <tr>
                                    <td class="lbl">வ.நாள்</td>
                                    <td class="colon">:</td>
                                    <td class="val">'.date('d M Y', strtotime($applicant->issued_at)).'</td>
                                </tr>
                                 <tr>
                                    <td class="lbl">செ.கா</td>
                                    <td class="colon">:</td>
                                    <td class="val">'.format_date($applicant->issued_at). '<small style="font-weight: bold;"> To </small>'. format_date($applicant->expires_at).'</td>
                                </tr>
                                <tr>
                                    <td class="lbl">பெயர்</td>
                                    <td class="colon">:</td>
                                    <td class="val">'.$applicant->name.'</td>
                                </tr>
                                <tr>
                                    <td class="lbl">த / க பெயர்</td>
                                    <td class="colon">:</td>
                                    <td class="val">'.$applicant->fathers_name.'</td>
                                </tr>
                                 <tr>
                                    <td class="lbl">பிறந்த நாள்</td>
                                    <td class="colon">:</td>
                                    <td class="val">'.date('d M Y', strtotime($applicant->d_o_b)).'</td>
                                </tr>
                                <tr>
                                    <td class="lbl">விலாசம்</td>
                                    <td class="colon">:</td>
                                    <td class="val">'.$applicant->applicants_address.'</td>
                                </tr>'
                                .$certificateRow.'
                            </table>

                        </td>

                        <!-- RIGHT : PHOTO / SIGN / QR (aligned with English licence PDF) -->
                        <td width="30%" valign="top">
                            <table width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center">
                                        <div class="photo-frame">
                                            <div class="photo-inner">
                                            '.($photoPath
                                                ? '<img src="'.$photoPath.'" style="width:38mm; height:38mm; object-fit:cover; display:block; margin:0 auto;">'
                                                : '').'
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="3mm"></td>
                                </tr>
                                <tr>
                                    <td align="center">
                                        <div class="sign-frame">
                                            <div class="sign-inner">
                                            '.($signPath
                                                ? '<img src="'.$signPath.'" style="width:34mm; height:10mm; object-fit:contain; vertical-align:middle;">'
                                                : '<span style="font-size:8pt; color:#666;">—</span>').'
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="3mm"></td>
                                </tr>
                                <tr>
                                    <td align="center">
                                        <div class="qr-box">
                                            <table width="100%" height="100%"><tr><td align="center" valign="middle">
                                            <barcode code="'.$qrValue.'" type="QR" size="1.42" error="M" />
                                            </td></tr></table>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="4mm"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

            </div>

            <!-- FOOTER -->
            <div class="footer">
                Issued by TNELB | தமிழ்நாடு
            </div>

        </div>
        ';
    
        $mpdf->WriteHTML($html);
       

        if ($returnBinary) {
            return $mpdf->Output('Application_Details.pdf', 'S');
        }

        return response($mpdf->Output('Application_Details.pdf', 'I'))->header('Content-Type', 'application/pdf');
    }

    // ----------------------

    public function generateLicenceTamil111($application_id, bool $returnBinary = false)
    {
        $application = DB::table('tnelb_application_tbl')
        ->where('application_id', $application_id)
        ->first();
        $applicant_photo = TnelbApplicantPhoto::where('application_id', $application_id)->first();
        if ($application && $application->appl_type === 'R') {
            $applicant = DB::table('tnelb_application_tbl')
            ->join('tnelb_renewal_license', 'tnelb_renewal_license.application_id', '=', 'tnelb_application_tbl.application_id')
            ->where('tnelb_application_tbl.application_id', $application_id)
            ->select(
                'tnelb_application_tbl.application_id',
                'tnelb_application_tbl.applicant_name AS name',
                'tnelb_application_tbl.fathers_name',
                'tnelb_application_tbl.applicants_address',
                'tnelb_application_tbl.d_o_b',
                'tnelb_application_tbl.age',
                'tnelb_application_tbl.license_name',
                'tnelb_application_tbl.form_name',
                'tnelb_renewal_license.license_number',
                'tnelb_renewal_license.issued_by',
                'tnelb_renewal_license.issued_at',
                'tnelb_renewal_license.expires_at'
            )
            ->first();
        } else {
            $applicant = DB::table('tnelb_application_tbl')
            ->join('tnelb_license', 'tnelb_license.application_id', '=', 'tnelb_application_tbl.application_id')
            ->where('tnelb_application_tbl.application_id', $application_id)
            ->select(
                'tnelb_application_tbl.application_id',
                'tnelb_application_tbl.applicant_name AS name',
                'tnelb_application_tbl.fathers_name',
                'tnelb_application_tbl.applicants_address',
                'tnelb_application_tbl.d_o_b',
                'tnelb_application_tbl.age',
                'tnelb_application_tbl.license_name',
                'tnelb_application_tbl.form_name',
                'tnelb_license.license_number',
                'tnelb_license.issued_by',
                'tnelb_license.issued_at',
                'tnelb_license.expires_at'
            )
            ->first();
        }
        if (!$applicant) {
            return $returnBinary ? null : back()->with('error', 'Application not found.');
        }

        if($applicant->license_name == 'B'){
            $certificate_name = 'Electrician';
            $content_text = 'இச்சான்று பெற்றவர் நடுத்தர மற்றும் குறைந்த மின்னழுத்த மின்னமைப்பு பணிகளை உரிமம் பெற்றுரியின் ஒப்பந்தக்காரரின் கீழ் மேற்கொள்ளலாம் அல்லது நடுத்தர மற்றும் குறைந்த அழுத்த நிறுவனத்தின் இயக்குதல் மற்றும் பராமரிப்பு பணிகளை அந்நிறுவனத்தில் அங்கீகாரத்துடன் மேற்கொள்ளலாம்.'; 
        }else if($applicant->license_name == 'H'){
             $certificate_name = 'WIREMAN HELPER';
            $content_text = 'இச்சான்று பெற்றவர் நடுத்தர மற்றும் குறைந்த மின்னழுத்த அமைப்பு பணிகளை மேற்கோள்வதில் உரிமம் பெற்ற மின் ஒப்பந்தக்காரரிடம் மின் கம்பியாளருக்கு உதவியாளராக பணிபுரியலாம். அல்லது நடுத்தர மற்றும் குறைந்த அழுத்த நிறுவனத்தின் இயக்குதல் மற்றும் பராமரிப்பு பணியில் மின்கம்பியாளருக்கு உதவியாளராக நிறுவனத்தின் அங்கீகாரத்துடன் மேற்கொள்ளலாம்.'; 
        }else{
            $certificate_name = '';
            $content_text = 'இச்சான்றிதழ் பெற்றவர், உரிமம் பெற்ற மின் ஒப்பந்தக்காரரின் கீழ் உயர் மின்னழுத்த (H.V) மற்றும் நடுத்தர மின்னழுத்த (M.V) மின் நிறுவல் பணிகளை மேற்பார்வை செய்ய அனுமதிக்கப்படுகிறார்; அல்லது இந்திய மின்சார விதிகள், 1956 இன் விதி 3 இன் கீழ் அங்கீகரிக்கப்பட்ட நபராக பணியாற்ற அனுமதிக்கப்படுகிறார்.';
        }

        $certificateRow = '';

        if (!empty($certificate_name)) {
            $certificateRow = '
                <tr>
                    <td class="lbl">தே.சான்று எண்</td>
                    <td class="colon">:</td>
                    <td class="val">'.$certificate_name.'</td>
                </tr>
            ';
        }

        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'fontDir' => array_merge($fontDirs, [
                public_path('fonts'),
            ]),
            'fontdata' => array_merge($fontData, [
                'notosanstamil' => [
                    'R' => 'NotoSansTamil-Regular.ttf',
                ]
            ]),
            'default_font' => 'notosanstamil',
            // A4 output (required): previously CR100 card size
            'format' => 'A4',
            'orientation' => 'P',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
        ]);


        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;


        $mpdf->SetTitle('TNELB Application License ' . $applicant->license_name);
        $mpdf->WriteHTML('<style>
            body { font-family: notosanstamil; font-size: 14pt; }
            .card { border: 1px solid #000; padding: 18px; box-sizing: border-box; width: 100%; }
            .header { color: #003366; text-align: center; font-size: 20pt; font-weight: bold; margin-bottom: 16px; }
            .content { font-size: 14pt; }
            .photo {
                width: 38mm;
                height: 38mm;
                border: 0.3mm solid #000;
                box-sizing: border-box;
                overflow: hidden;
            }
           .info-table {
                font-size: 14pt;
                border-collapse: collapse;
            }

            .info-table td { padding: 2.2mm 2mm; vertical-align: top; }

            .info-table .lbl { width: 38mm; font-weight: bold; }

            .info-table .colon {
                width: 2mm;
                text-align: center;
            }
            .footer { margin-top: 16px; text-align: center; font-size: 12pt; }
            </style>', \Mpdf\HTMLParserMode::HEADER_CSS);
                
        $photoPath = !empty($applicant_photo->upload_path) ? public_path($applicant_photo->upload_path): null;

        $qrValue = 'sdfdgsdg'; 

        $html = '
        <div class="card">

            <!-- HEADER -->
            <div class="header">
                தமிழ்நாடு மின் உரிமம் வழங்கும் வாரியம்<br>
                திரு.வி.க. தொழிற்பேட்டை, கிண்டி, சென்னை – 32.
            </div>

            <!-- BODY -->
            <div class="content">

               <table width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <!-- LEFT : DETAILS -->
                        <td width="70%" valign="top">

                            <table class="info-table">
                                <tr>
                                    <td class="lbl">த. சா. எண்</td>
                                    <td class="colon">:</td>
                                    <td class="val">'.$applicant->license_number.'</td>
                                </tr>
                                <tr>
                                    <td class="lbl">வ.நாள்</td>
                                    <td class="colon">:</td>
                                    <td class="val">'.date('d M Y', strtotime($applicant->issued_at)).'</td>
                                </tr>
                                 <tr>
                                    <td class="lbl">செ.கா</td>
                                    <td class="colon">:</td>
                                    <td class="val">'.format_date($applicant->issued_at). '<small style="font-weight: bold;"> To </small>'. format_date($applicant->expires_at).'</td>
                                </tr>
                                <tr>
                                    <td class="lbl">பெயர்</td>
                                    <td class="colon">:</td>
                                    <td class="val">'.$applicant->name.'</td>
                                </tr>
                                <tr>
                                    <td class="lbl">த / க பெயர்</td>
                                    <td class="colon">:</td>
                                    <td class="val">'.$applicant->fathers_name.'</td>
                                </tr>
                                 <tr>
                                    <td class="lbl">பிறந்த நாள்</td>
                                    <td class="colon">:</td>
                                    <td class="val">'.date('d M Y', strtotime($applicant->d_o_b)).'</td>
                                </tr>
                                <tr>
                                    <td class="lbl">விலாசம்</td>
                                    <td class="colon">:</td>
                                    <td class="val">'.$applicant->applicants_address.'</td>
                                </tr>'
                                .$certificateRow.'
                            </table>

                        </td>

                        <!-- RIGHT : PHOTO -->
                        <td width="30%" valign="top">
                            <table width="100%" cellspacing="0" cellpadding="0">
                                <!-- PHOTO ROW -->
                                <tr>
                                    <td align="center">
                                        <div class="photo">
                                            '.($photoPath
                                                ? '<img src="'.$photoPath.'" style="width:38mm; height:38mm; object-fit:cover;">'
                                                : '').'
                                        </div>
                                    </td>
                                </tr>

                                <!-- SPACE BETWEEN PHOTO & QR -->
                                <tr>
                                    <td height="3mm"></td>
                                </tr>

                                <!-- QR ROW -->
                                <tr>
                                    <td align="center">
                                        <barcode code="'.$qrValue.'" type="QR" size="0.9" error="M" />
                                    </td>
                                </tr>

                                <!-- BOTTOM SAFE SPACE -->
                                <tr>
                                    <td height="3mm"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

            </div>

            <!-- FOOTER -->
            <div class="footer">
                Issued by TNELB | தமிழ்நாடு
            </div>

        </div>
        ';
    
        $mpdf->WriteHTML($html);
        $mpdf->AddPage('L');
        $backHtml = '
            <div class="card">

                <div class="content" style="font-size:9.5pt; line-height:1.4;">

                    <div style="text-align:right; font-size:7pt; margin-bottom:2mm;">
                        Visit us at : www.tnelb.gov.in
                    </div>

                    <div style="margin-top:4mm; text-align: justify;">
                        ' . $content_text . '   
                    </div>

                    <br><br><br><br>

                    <!-- SIGNATURE AREA -->
                    <table width="100%" style="margin-top:6mm;">
                        <tr>
                            <td width="45%" style="text-align:left;">
                                <div style="height:12mm;"></div>
                                <strong>செயலாளர்</strong>
                            </td>

                            <td width="55%" style="text-align:right;">
                                <div style="height:12mm;"></div>
                                <strong>தலைவர்</strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>';
        $mpdf->WriteHTML($backHtml);

        if ($returnBinary) {
            return $mpdf->Output('Application_Details.pdf', 'S');
        }

        return response($mpdf->Output('Application_Details.pdf', 'I'))->header('Content-Type', 'application/pdf');
    }

    /**
     * Stream Tamil licence PDF for non-FormP applications.
     * This uses the existing Tamil generator directly.
     */
    public function streamLicenceTa(string $application_id)
    {
        return $this->generateLicenceTamil($application_id, false);
    }

    private function storeEncryptedLicensePdfPath($applicationId, string $encryptedPathEn, ?string $encryptedPathTa = null): void
    {
        try {
            if (!Schema::hasTable('tnelb_license')) {
                return;
            }

            // Build update payload for separate EN / TA columns
            $payload = ['license_pdf_en' => $encryptedPathEn];
            if ($encryptedPathTa) {
                $payload['license_pdf_ta'] = $encryptedPathTa;
            }

            $updated = DB::table('tnelb_license')
                ->where('application_id', $applicationId)
                ->update($payload);

            if ((int) $updated === 0) {
                Log::warning('No tnelb_license row updated for license_pdf_en / license_pdf_ta', [
                    'application_id' => $applicationId,
                    'payload' => $payload,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to store encrypted license PDF path', [
                'application_id' => $applicationId,
                'encryptedPathEn' => $encryptedPathEn,
                'encryptedPathTa' => $encryptedPathTa,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Licence row for Form P: new → tnelb_license, renewal → tnelb_renewal_license.
     */
    private function getFormPLicenceRow(string $applicationId): ?object
    {
        $formP = DB::table('tnelb_form_p')->where('application_id', $applicationId)->first();
        if (! $formP) {
            return null;
        }
        $applType = strtoupper(trim($formP->appl_type ?? 'N'));

        if ($applType === 'R') {
            return DB::table('tnelb_renewal_license')->where('application_id', $applicationId)->first();
        }

        return DB::table('tnelb_license')->where('application_id', $applicationId)->first();
    }

    private function formPLicenceTableForApplType(string $applType): string
    {
        return strtoupper(trim($applType)) === 'R' ? 'tnelb_renewal_license' : 'tnelb_license';
    }

    /**
     * Concatenate two PDFs (all pages from the first, then all pages from the second).
     */
    private function mergePdfBinaries(string $pdfBinaryA, string $pdfBinaryB): string
    {
        $tmpA = tempnam(sys_get_temp_dir(), 'fpdf_a');
        $tmpB = tempnam(sys_get_temp_dir(), 'fpdf_b');
        if ($tmpA === false || $tmpB === false) {
            throw new \RuntimeException('Unable to create temp file for PDF merge.');
        }
        try {
            file_put_contents($tmpA, $pdfBinaryA);
            file_put_contents($tmpB, $pdfBinaryB);
            $mpdf = new Mpdf(['mode' => 'utf-8']);
            foreach ([$tmpA, $tmpB] as $tmp) {
                $pageCount = $mpdf->setSourceFile($tmp);
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $mpdf->AddPage();
                    $tplId = $mpdf->importPage($pageNo);
                    $mpdf->useTemplate($tplId);
                }
            }

            return $mpdf->Output('', 'S');
        } finally {
            @unlink($tmpA);
            @unlink($tmpB);
        }
    }

    /**
     * Stored path to use when streaming Form P licence (single bilingual file preferred).
     */
    private function resolveFormPLicenceEncryptedPath(object $licence, string $requestedLocale): ?string
    {
        if (! empty($licence->license_pdf_bilingual)) {
            return $licence->license_pdf_bilingual;
        }
        if ($requestedLocale === 'ta') {
            return $licence->license_pdf_ta ?? null;
        }

        return $licence->license_pdf_en ?? null;
    }

    private function streamFormPLicenceByLocale(string $applicationId, string $requestedLocale): \Illuminate\Http\Response
    {
        $label = $requestedLocale === 'ta' ? 'Tamil' : 'English';
        $licence = $this->getFormPLicenceRow($applicationId);

        $encryptedPath = $licence ? $this->resolveFormPLicenceEncryptedPath($licence, $requestedLocale) : null;

        if (! $licence || empty($encryptedPath)) {
            $this->generateFormPLicencePdfs($applicationId);
            $licence       = $this->getFormPLicenceRow($applicationId);
            $encryptedPath = $licence ? $this->resolveFormPLicenceEncryptedPath($licence, $requestedLocale) : null;
        }

        if (! $licence || empty($encryptedPath)) {
            abort(404, "Form P {$label} licence PDF not found.");
        }

        try {
            $encryptedData = Storage::disk('local')->get($encryptedPath);
            $pdfBinary     = Crypt::decryptString($encryptedData);
        } catch (\Throwable $e) {
            Log::warning('Failed to stream Form P licence PDF', [
                'application_id' => $applicationId,
                'locale'         => $requestedLocale,
                'path'           => $encryptedPath,
                'error'          => $e->getMessage(),
            ]);
            abort(500, 'Unable to open licence PDF.');
        }

        $fileName = basename($encryptedPath, '.enc');

        return response($pdfBinary)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="'.$fileName.'"');
    }

    /**
     * Stream encrypted English Form P licence PDF from private storage.
     * If a bilingual PDF is stored, streams the combined EN+TA document (same file as Tamil route).
     */
    public function streamFormPLicenceEn(string $applicationId)
    {
        return $this->streamFormPLicenceByLocale($applicationId, 'en');
    }

    /**
     * Stream encrypted Tamil Form P licence PDF from private storage.
     * If a bilingual PDF is stored, streams the combined EN+TA document (same file as English route).
     */
    public function streamFormPLicenceTa(string $applicationId)
    {
        return $this->streamFormPLicenceByLocale($applicationId, 'ta');
    }

    /**
     * Generate Form P licence PDF(s): merges English + Tamil into one bilingual PDF when
     * `license_pdf_bilingual` exists on the licence table; encrypts once and stores the path.
     * Skips regeneration if a bilingual (or legacy EN+TA) file is already stored.
     *
     * @return string|null Storage path of the primary encrypted file (bilingual preferred, else EN legacy)
     */
    public function generateFormPLicencePdfs(string $applicationId): ?string
    {
        // Fetch Form P application
        $formP = DB::table('tnelb_form_p')
            ->where('application_id', $applicationId)
            ->first();

        if (!$formP) {
            Log::warning('generateFormPLicencePdfs: Form P application not found', [
                'application_id' => $applicationId,
            ]);
            return null;
        }

        $applType = strtoupper(trim($formP->appl_type ?? 'N')); // N or R

        // Determine licence source (fresh vs renewal)
        if ($applType === 'R') {
            $licence = DB::table('tnelb_renewal_license')
                ->where('application_id', $applicationId)
                ->first();
        } else {
            $licence = DB::table('tnelb_license')
                ->where('application_id', $applicationId)
                ->first();
        }

        if (!$licence) {
            Log::warning('generateFormPLicencePdfs: licence record not found for Form P', [
                'application_id' => $applicationId,
                'appl_type'      => $applType,
            ]);
            return null;
        }

        $licenceTable = $this->formPLicenceTableForApplType($applType);

        // Already have a single bilingual file (generate once per application).
        if (Schema::hasTable($licenceTable)
            && Schema::hasColumn($licenceTable, 'license_pdf_bilingual')
            && ! empty($licence->license_pdf_bilingual)
            && Storage::disk('local')->exists($licence->license_pdf_bilingual)) {
            return $licence->license_pdf_bilingual;
        }

        // Legacy: separate EN + TA already stored.
        if (Schema::hasTable($licenceTable)
            && Schema::hasColumn($licenceTable, 'license_pdf_en')
            && Schema::hasColumn($licenceTable, 'license_pdf_ta')
            && ! empty($licence->license_pdf_en) && ! empty($licence->license_pdf_ta)
            && Storage::disk('local')->exists($licence->license_pdf_en)
            && Storage::disk('local')->exists($licence->license_pdf_ta)) {
            return $licence->license_pdf_en;
        }

        $applicantPhoto = TnelbApplicantPhoto::where('application_id', $applicationId)->first();

        // Normalized applicant data for template
        $applicant = (object) [
            'application_id'    => $formP->application_id,
            'name'              => $formP->applicant_name,
            'fathers_name'      => $formP->fathers_name,
            'applicants_address'=> $formP->applicants_address,
            'd_o_b'             => $formP->d_o_b,
            'age'               => $formP->age,
            'license_name'      => $formP->license_name,
            'form_name'         => $formP->form_name,
            'license_number'    => $licence->license_number,
            'issued_by'         => $licence->issued_by,
            'issued_at'         => $licence->issued_at,
            'expires_at'        => $licence->expires_at,
        ];

        // ---------- English card ----------
        $mpdfEn = new \Mpdf\Mpdf([
            'mode'         => 'utf-8',
            'format'       => [80.80, 120.55],
            'orientation'  => 'L',
            'margin_top'    => 0,
            'margin_bottom' => 0,
            'margin_left'   => 0,
            'margin_right'  => 0,
        ]);

        $mpdfEn->SetTitle('TNELB Form P Licence ' . $applicant->license_number);
        $mpdfEn->WriteHTML('
            <style>
                @page { size: 110.55mm 70.80mm; margin: 0; }
                body { margin: 0; padding: 0; width: 120.55mm; height: 80.80mm; font-family: helvetica; overflow: hidden; }
                .card { width: 120.55mm; height: 80.80mm; border: 0.4mm solid #000; box-sizing: border-box; }
                .header { height: 11mm; color: #003366; text-align: center; font-size: 10.5pt; font-weight: bold; padding: 2mm; box-sizing: border-box; }
                .content { padding: 3mm; font-size: 7pt; box-sizing: border-box; }
                .photo { width: 22mm; height: 22mm; border: 0.3mm solid #000; box-sizing: border-box; overflow: hidden; }
                .info-table { font-size: 9pt; border-collapse: collapse; }
                .info-table td { padding: 1mm; vertical-align: top; }
                .info-table .lbl { width: 25mm; font-weight: bold; }
                .info-table .colon { width: 2mm; text-align: center; }
                .footer { margin-top: 5mm; text-align: center; font-size: 6pt; }
            </style>
        ', \Mpdf\HTMLParserMode::HEADER_CSS);

        $photoPath = !empty($applicantPhoto->upload_path) ? public_path($applicantPhoto->upload_path) : null;
        $qrValue   = 'TNELB FORM P ' . $applicant->license_number;

        $enHtml = '
        <div class="card">
            <div class="header">
                TAMIL NADU ELECTRICAL LICENCING BOARD<br>
                Thiru Vi. Ka. Indl. Estate, Guindy, Chennai - 600 032.
            </div>
            <div class="content">
                <table width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td width="70%" valign="top">
                            <table class="info-table">
                                <tr>
                                    <td class="lbl">WH.No</td>
                                    <td class="colon">:</td>
                                    <td class="val">'.$applicant->license_number.'</td>
                                </tr>
                                <tr>
                                    <td class="lbl">D.O.I</td>
                                    <td class="colon">:</td>
                                    <td class="val">'.date('d M Y', strtotime($applicant->issued_at)).'</td>
                                </tr>
                                <tr>
                                    <td class="lbl">Validity</td>
                                    <td class="colon">:</td>
                                    <td class="val">'.format_date($applicant->issued_at).'<small style="font-weight: bold;"> To </small>'.format_date($applicant->expires_at).'</td>
                                </tr>
                                <tr>
                                    <td class="lbl">Name</td>
                                    <td class="colon">:</td>
                                    <td class="val">'.$applicant->name.'</td>
                                </tr>
                                <tr>
                                    <td class="lbl">F/H Name</td>
                                    <td class="colon">:</td>
                                    <td class="val">'.$applicant->fathers_name.'</td>
                                </tr>
                                <tr>
                                    <td class="lbl">Date of Birth</td>
                                    <td class="colon">:</td>
                                    <td class="val">'.date('d M Y', strtotime($applicant->d_o_b)).'</td>
                                </tr>
                                <tr>
                                    <td class="lbl">Address</td>
                                    <td class="colon">:</td>
                                    <td class="val">'.$applicant->applicants_address.'</td>
                                </tr>
                            </table>
                        </td>
                        <td width="30%" valign="top">
                            <table width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center">
                                        <div class="photo">
                                            '.($photoPath
                                                ? '<img src="'.$photoPath.'" style="width:38mm; height:38mm; object-fit:cover;">'
                                                : '').'
                                        </div>
                                    </td>
                                </tr>
                                <tr><td height="3mm"></td></tr>
                                <tr>
                                    <td align="center">
                                        <barcode code="'.$qrValue.'" type="QR" size="0.9" error="M" />
                                    </td>
                                </tr>
                                <tr><td height="4mm"></td></tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="footer">
                Issued by TNELB | Tamil Nadu
            </div>
        </div>';

        $mpdfEn->WriteHTML($enHtml);

        // Back side simple text for Form P
        $mpdfEn->AddPage('L');
        $mpdfEn->WriteHTML('
            <div class="card">
                <div class="content" style="font-size:9.5pt; line-height:1.4;">
                    <div style="text-align:right; font-size:7pt; margin-bottom:2mm;">
                        Visit us at : www.tnelb.gov.in
                    </div>
                    <div style="margin-top:4mm;text-align: justify;">
                        The holder of this Form P competence certificate is authorised to work in connection with the operation and maintenance of power generating stations as per the regulations of the Tamil Nadu Electrical Licensing Board.
                    </div>
                    <br><br><br><br>
                    <table width="100%" style="margin-top:15mm;">
                        <tr>
                            <td width="45%" style="text-align:left;">
                                <div style="height:12mm;"></div>
                                <strong>Secretary</strong>
                            </td>
                            <td width="55%" style="text-align:right;">
                                <div style="height:12mm;"></div>
                                <strong>President</strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        ');

        $fileNameEn   = $applicationId . '_FORMP_EN.pdf';
        $pdfBinaryEn  = $mpdfEn->Output($fileNameEn, 'S');

        // ---------- Tamil card (simplified) ----------
        // Reuse the same Tamil font configuration as the existing Tamil licence generator
        $defaultConfig   = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs        = $defaultConfig['fontDir'];
        $defaultFontCfg  = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData        = $defaultFontCfg['fontdata'];

        $mpdfTa = new \Mpdf\Mpdf([
            'mode'         => 'utf-8',
            'format'       => [80.80, 120.55],
            'orientation'  => 'L',
            'margin_top'    => 0,
            'margin_bottom' => 0,
            'margin_left'   => 0,
            'margin_right'  => 0,
            'fontDir'       => array_merge($fontDirs, [
                public_path('fonts'),
            ]),
            'fontdata'      => array_merge($fontData, [
                'notosanstamil' => [
                    'R' => 'NotoSansTamil-Regular.ttf',
                ],
            ]),
            'default_font' => 'notosanstamil',
        ]);

        $mpdfTa->autoScriptToLang = true;
        $mpdfTa->autoLangToFont   = true;

        $mpdfTa->SetTitle('TNELB Form P Licence ' . $applicant->license_number);
        $mpdfTa->WriteHTML('
            <style>
                @page { size: 110.55mm 70.80mm; margin: 0; }
                body { margin: 0; padding: 0; width: 120.55mm; height: 80.80mm; font-family: notosanstamil; overflow: hidden; }
                .card { width: 120.55mm; height: 80.80mm; border: 0.4mm solid #000; box-sizing: border-box; }
                .header { height: 11mm; color: #003366; text-align: center; font-size: 9pt; font-weight: bold; padding: 2mm; box-sizing: border-box; }
                .content { padding: 3mm; font-size: 7pt; box-sizing: border-box; }
                .photo { width: 22mm; height: 22mm; border: 0.3mm solid #000; box-sizing: border-box; overflow: hidden; }
                .info-table { font-size: 8pt; border-collapse: collapse; }
                .info-table td { padding: 1mm; vertical-align: top; }
                .info-table .lbl { width: 28mm; font-weight: bold; }
                .info-table .colon { width: 2mm; text-align: center; }
                .footer { margin-top: 5mm; text-align: center; font-size: 6pt; }
            </style>
        ', \Mpdf\HTMLParserMode::HEADER_CSS);

        $taHtml = '
        <div class="card">
            <div class="header">
                தமிழ்நாடு மின்அனுமதி வாரியம்<br>
                திரு வி.க. தொழிற் பகுதி, கிண்டி, சென்னை - 600 032.
            </div>
            <div class="content">
                <table width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td width="70%" valign="top">
                            <table class="info-table">
                                <tr>
                                    <td class="lbl">சான்றிதழ் எண்</td>
                                    <td class="colon">:</td>
                                    <td class="val">'.$applicant->license_number.'</td>
                                </tr>
                                <tr>
                                    <td class="lbl">வழங்கிய தேதி</td>
                                    <td class="colon">:</td>
                                    <td class="val">'.date('d-m-Y', strtotime($applicant->issued_at)).'</td>
                                </tr>
                                <tr>
                                    <td class="lbl">செல்லுபடியாகும் தேதி</td>
                                    <td class="colon">:</td>
                                    <td class="val">'.format_date($applicant->issued_at).' முதல் '.format_date($applicant->expires_at).'</td>
                                </tr>
                                <tr>
                                    <td class="lbl">பெயர்</td>
                                    <td class="colon">:</td>
                                    <td class="val">'.$applicant->name.'</td>
                                </tr>
                                <tr>
                                    <td class="lbl">தந்தை/கணவர் பெயர்</td>
                                    <td class="colon">:</td>
                                    <td class="val">'.$applicant->fathers_name.'</td>
                                </tr>
                                <tr>
                                    <td class="lbl">முகவரி</td>
                                    <td class="colon">:</td>
                                    <td class="val">'.$applicant->applicants_address.'</td>
                                </tr>
                            </table>
                        </td>
                        <td width="30%" valign="top">
                            <table width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center">
                                        <div class="photo">
                                            '.($photoPath
                                                ? '<img src="'.$photoPath.'" style="width:22mm; height:22mm; object-fit:cover;">'
                                                : '').'
                                        </div>
                                    </td>
                                </tr>
                                <tr><td height="3mm"></td></tr>
                                <tr>
                                    <td align="center">
                                        <barcode code="'.$qrValue.'" type="QR" size="0.6" error="M" />
                                    </td>
                                </tr>
                                <tr><td height="4mm"></td></tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="footer">
                தமிழ்நாடு மின்அனுமதி வாரியத்தால் வழங்கப்பட்டது
            </div>
        </div>';

        // Back side content (Tamil explanation + signatures), similar to other Tamil licences
        $backTaHtml = '
            <div class="card">
                <div class="content" style="font-size:9.5pt; line-height:1.4; font-family:notosanstamil;">
                    <div style="text-align:right; font-size:7pt; margin-bottom:2mm;">
                        எங்களை தொடர்பு கொள்ள : www.tnelb.gov.in
                    </div>
                    <div style="margin-top:4mm; text-align: justify;">
                        இச்சான்றிதழ் பெற்றவர் மின் உற்பத்தி நிலையங்களில் (Form P திறன் சான்றிதழ்) 
                        பணிபுரிய தகுதி பெற்றவர் என்பதையும், தமிழ்நாடு மின்அனுமதி வாரியம் 
                        நிர்ணயித்த விதிமுறைகளின்படி செயல்பட வேண்டும் என்பதையும் அறிவிக்கப்படுகிறது.
                    </div>

                    <br><br><br><br>

                    <!-- SIGNATURE AREA -->
                    <table width="100%" style="margin-top:15mm;">
                        <tr>
                            <td width="45%" style="text-align:left;">
                                <div style="height:12mm;"></div>
                                <strong>செயலாளர்</strong>
                            </td>
                            <td width="55%" style="text-align:right;">
                                <div style="height:12mm;"></div>
                                <strong>தலைவர்</strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>';

        // Front + explicit page break + back on page 2
        $mpdfTa->WriteHTML($taHtml . '<pagebreak />' . $backTaHtml);
        $fileNameTa    = $applicationId . '_FORMP_TA.pdf';
        $pdfBinaryTa   = $mpdfTa->Output($fileNameTa, 'S');

        $hasBilingualCol = Schema::hasTable($licenceTable)
            && Schema::hasColumn($licenceTable, 'license_pdf_bilingual');

        if ($hasBilingualCol) {
            try {
                $pdfMerged = $this->mergePdfBinaries($pdfBinaryEn, $pdfBinaryTa);
            } catch (\Throwable $e) {
                Log::error('Form P bilingual PDF merge failed', [
                    'application_id' => $applicationId,
                    'error'          => $e->getMessage(),
                ]);

                return null;
            }

            $encryptedPathBl = 'private_documents/license_pdfs/' . $applicationId . '_FORMP_BL.pdf.enc';
            Storage::disk('local')->put($encryptedPathBl, Crypt::encryptString($pdfMerged));

            $payload = ['license_pdf_bilingual' => $encryptedPathBl];
            if (Schema::hasColumn($licenceTable, 'license_pdf_en')) {
                $payload['license_pdf_en'] = null;
            }
            if (Schema::hasColumn($licenceTable, 'license_pdf_ta')) {
                $payload['license_pdf_ta'] = null;
            }

            try {
                DB::table($licenceTable)
                    ->where('application_id', $applicationId)
                    ->update($payload);
            } catch (\Throwable $e) {
                Log::warning('Failed to update Form P bilingual licence PDF path', [
                    'application_id' => $applicationId,
                    'table'          => $licenceTable,
                    'path'           => $encryptedPathBl,
                    'error'          => $e->getMessage(),
                ]);
            }

            return $encryptedPathBl;
        }

        // Legacy: separate encrypted EN / TA files
        $encryptedPathEn = 'private_documents/license_pdfs/' . $fileNameEn . '.enc';
        $encryptedPathTa = 'private_documents/license_pdfs/' . $fileNameTa . '.enc';
        Storage::disk('local')->put($encryptedPathEn, Crypt::encryptString($pdfBinaryEn));
        Storage::disk('local')->put($encryptedPathTa, Crypt::encryptString($pdfBinaryTa));

        try {
            $legacyPayload = [
                'license_pdf_en' => $encryptedPathEn,
                'license_pdf_ta' => $encryptedPathTa,
            ];
            DB::table($licenceTable)
                ->where('application_id', $applicationId)
                ->update($legacyPayload);
        } catch (\Throwable $e) {
            Log::warning('Failed to update licence PDF paths for Form P', [
                'application_id' => $applicationId,
                'table'          => $licenceTable,
                'path_en'        => $encryptedPathEn,
                'path_ta'        => $encryptedPathTa,
                'error'          => $e->getMessage(),
            ]);
        }

        return $encryptedPathEn;
    }

   public function generateLicensePDF($application_id)
    {
        
        $application = DB::table('tnelb_application_tbl')
        ->where('application_id', $application_id)
        ->first();
        $applicant_photo = TnelbApplicantPhoto::where('application_id', $application_id)->first();
        $applicant_sign  = TnelbApplicantsSign::where('application_id', $application_id)->first();
        if ($application && $application->appl_type === 'R') {
            $applicant = DB::table('tnelb_application_tbl')
            ->join('tnelb_renewal_license', 'tnelb_renewal_license.application_id', '=', 'tnelb_application_tbl.application_id')
            ->where('tnelb_application_tbl.application_id', $application_id)
            ->select(
                'tnelb_application_tbl.application_id',
                'tnelb_application_tbl.applicant_name AS name',
                'tnelb_application_tbl.fathers_name',
                'tnelb_application_tbl.applicants_address',
                'tnelb_application_tbl.d_o_b',
                'tnelb_application_tbl.age',
                'tnelb_application_tbl.license_name',
                'tnelb_application_tbl.form_name',
                'tnelb_renewal_license.license_number',
                'tnelb_renewal_license.issued_by',
                'tnelb_renewal_license.issued_at',
                'tnelb_renewal_license.issued_from',
                'tnelb_renewal_license.expires_at'
            )
            ->first();
        } else {
            $applicant = DB::table('tnelb_application_tbl')
            ->join('tnelb_license', 'tnelb_license.application_id', '=', 'tnelb_application_tbl.application_id')
            ->where('tnelb_application_tbl.application_id', $application_id)
            ->select(
                'tnelb_application_tbl.application_id',
                'tnelb_application_tbl.applicant_name AS name',
                'tnelb_application_tbl.fathers_name',
                'tnelb_application_tbl.applicants_address',
                'tnelb_application_tbl.d_o_b',
                'tnelb_application_tbl.age',
                'tnelb_application_tbl.license_name',
                'tnelb_application_tbl.form_name',
                'tnelb_license.license_number',
                'tnelb_license.issued_by',
                'tnelb_license.issued_at',
                'tnelb_license.issued_from',
                'tnelb_license.expires_at'
            )
            ->first();
        }
        if (!$applicant) {
            return back()->with('error', 'Application not found.');
        }
        $issuedFromFresh = DB::table('tnelb_license as l')
            ->join('tnelb_application_tbl as ta', 'ta.application_id', '=', 'l.application_id')
            ->where('ta.login_id', $application->login_id)
            ->whereDate('l.expires_at', '>=', Carbon::now()) // valid (not expired)
            ->select('l.license_number', 'l.issued_at', 'l.expires_at');

        $issuedFromRenewal = DB::table('tnelb_renewal_license as rl')
        ->join('tnelb_application_tbl as ta', 'ta.application_id', '=', 'rl.application_id')
        ->where('ta.login_id', $application->login_id)
        ->whereDate('rl.expires_at', '>=', Carbon::now()) // valid (not expired)
        ->select('rl.license_number', 'rl.issued_at', 'rl.expires_at');
        
        $certificateList = $issuedFromFresh->union($issuedFromRenewal)->get()->sortByDesc('issued_at')->values();
        $certificateRowsHtml = '';
        foreach ($certificateList as $index => $certificate) {
            $isExpired = !empty($certificate->expires_at) && strtotime((string) $certificate->expires_at) < strtotime(date('Y-m-d'));
            $statusInner = $isExpired
                ? '<div class="st-en">Expired</div><div class="st-ta" lang="ta">காலாவதியானது</div>'
                : '<div class="st-en">Active</div><div class="st-ta" lang="ta">செயலில்</div>';
            $statusClass = $isExpired ? 'status-expired' : 'status-active';
            $certificateRowsHtml .= '
                        <tr>
                            <td width="6%" align="center">'.($index + 1).'</td>
                            <td width="28%">'.$certificate->license_number.'</td>
                            <td width="20%">'.date('d M Y', strtotime($certificate->issued_at)).'</td>
                            <td width="20%">'.format_date($certificate->expires_at).'</td>
                        </tr>';
        }
        if ($certificateRowsHtml === '') {
            $certificateRowsHtml = '<tr><td colspan="5"><div class="table-empty-msg"><div class="bi-en">No certificate history available.</div><div class="bi-ta" lang="ta">சான்றிதழ் வரலாறு ஏதுமில்லை.</div></div></td></tr>';
        }


        $payment = DB::table('payments')->where('application_id', $application_id)->first();
        // Tamil: prefer dejavusans Regular (OTL); avoids Bold faces missing Indic. Marutham if file exists.
        $tamilFontFamily = 'dejavusans';
        $mpdfConfig = [
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
            'default_font' => 'helvetica',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            // Pick missing glyphs from backup fonts; helps Tamil/Latin mix
            'useSubstitutions' => true,
            // 0 = embed full TTF (no subset) so Tamil codepoints are not stripped
            'percentSubset' => 0,
        ];
        $maruthamPath = public_path('fonts/Marutham.ttf');
        if (is_readable($maruthamPath)) {
            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];
            $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];
            $mpdfConfig['fontDir'] = array_merge($fontDirs, [
                public_path('fonts'),
            ]);
            $mpdfConfig['fontdata'] = $fontData + [
                'marutham' => [
                    'R' => 'Marutham.ttf',
                    'useOTL' => 0xFF,
                ],
            ];
            $tamilFontFamily = 'marutham';
        }

        $mpdf = new \Mpdf\Mpdf($mpdfConfig);

        $mpdf->SetTitle('TNELB Application License ' . $applicant->license_name);
        $mpdf->WriteHTML(str_replace(
            'TAMIL_FONT_PLACEHOLDER',
            $tamilFontFamily,
            '<style>
            body { font-family: helvetica; font-size: 14pt; }
            /* Bilingual: English on top, Tamil below — same Helvetica as original */
            .bi-en {
                display: block;
                font-family: helvetica;
                font-weight: bold;
                line-height: 1.2;
                margin: 0;
                padding: 0;
            }
            /* Tamil: always Regular weight — Bold TTFs often lack Indic glyphs (tofu).
               !important beats bold inherited from th, .lbl, .status-pill, .summary-heading */
            .bi-ta {
                display: block;
                font-family: TAMIL_FONT_PLACEHOLDER;
                font-weight: normal !important;
                line-height: 1.2;
                margin: 0.12em 0 0 0;
                padding: 0;
                font-size: 88%;
            }
            .card {
                border: 1px solid #000; padding: 18px; box-sizing: border-box; width: 100%;
                min-height: 178mm;
            }
            .header { color: #003366; text-align: center; margin-bottom: 16px;
                border-bottom: 0.35mm solid #c5d4e6; padding-bottom: 10px; }
            .hdr-stack { margin-bottom: 2mm; }
            .hdr-stack:last-child { margin-bottom: 0; }
            .header-main .bi-en { font-size: 16pt; }
            .header-main .bi-ta { font-size: 12.5pt; margin-top: 0.12em; font-weight: normal !important; }
            .header-title .bi-en { font-size: 14pt; }
            .header-title .bi-ta { font-size: 11pt; margin-top: 0.12em; font-weight: normal !important; }
            .header-sub .bi-en { font-size: 10.5pt; }
            .header-sub .bi-ta { font-size: 10pt; margin-top: 0.12em; font-weight: normal !important; }
            .content { font-size: 14pt; }
            .lbl-bi { padding: 0; margin: 0; line-height: 1.2; }
            .lbl-bi .lbl-en {
                display: block;
                font-family: helvetica;
                font-weight: bold;
                font-size: 11pt;
                line-height: 1.15;
                margin: 0;
                padding: 0;
            }
            .lbl-bi .lbl-ta {
                display: block;
                margin: 0.1em 0 0 0;
                padding: 0;
                font-family: TAMIL_FONT_PLACEHOLDER;
                font-weight: normal !important;
                font-size: 9.5pt;
                line-height: 1.15;
            }
            .photo-frame, .qr-box {
                width: 38mm;
                height: 38mm;
                border: none;
                box-sizing: border-box;
                margin: 0 auto;
                overflow: hidden;
                padding: 0;
                text-align: center;
            }
            .photo-inner {
                width: 100%;
                height: 100%;
                overflow: hidden;
                text-align: center;
            }
            .sign-frame {
                width: 38mm;
                min-height: 14mm;
                height: auto;
                border: none;
                box-sizing: border-box;
                margin: 0 auto;
                text-align: center;
            }
            .sign-inner {
                width: 100%;
                min-height: 14mm;
                overflow: hidden;
                line-height: 1.2;
                padding: 1mm 0;
            }
            .qr-box table { border-collapse: collapse; }
            .qr-box td { padding: 0; vertical-align: middle; }
           .info-table {
                font-size: 11pt;
                border-collapse: collapse;
                width: 100%;
                table-layout: fixed;
            }
            .info-table td { padding: 1.65mm 0; vertical-align: top; border-bottom: 0.22mm solid #e8edf4; }
            .info-table td.lbl {
                width: 50%;
                padding-right: 1mm;
            }
            .info-table td.lbl,
            .info-table td.lbl .lbl-bi {
                font-family: helvetica;
            }
            /* mPDF: force Tamil face inside label cells (nested tables ignore class-only font). */
            .info-table td.lbl .lbl-ta {
                font-family: TAMIL_FONT_PLACEHOLDER !important;
            }
            .info-table .colon {
                width: 3mm;
                text-align: center;
                font-weight: bold;
                padding-top: 0.35mm;
            }
            .info-table .val {
                width: 50%;
                font-size: 11pt;
                font-weight: normal;
                line-height: 1.3;
                padding-left: 1mm;
                word-wrap: break-word;
            }
            .summary-card { border: 0.4mm solid #cfd8e3; margin-top: 2mm; overflow: hidden; }
            .summary-heading {
                background: #edf3fa;
                font-weight: bold;
                color: #0b3b6e;
                padding: 2mm 2.2mm 1.2mm 2.2mm;
                border-bottom: 0.3mm solid #d8e2ef;
                text-align: center;
            }
            .summary-heading .bi-en { font-size: 11.5pt; margin: 0; line-height: 1.2; }
            .summary-heading .bi-ta { font-size: 10pt; margin-top: 0.12em; font-weight: normal !important; line-height: 1.2; }
            .summary-table { border-collapse: collapse; font-size: 10.2pt; width: 100%; }
            .summary-table th {
                background: #edf3fa;
                color: #123c66;
                font-weight: bold;
                padding: 1.6mm 1.4mm;
                border-bottom: 0.3mm solid #d7e1ee;
                text-transform: none;
                font-size: 9.2pt;
                letter-spacing: 0.2px;
                text-align: center;
                vertical-align: middle;
            }
            .summary-table .th-bi .th-en {
                display: block;
                font-family: helvetica;
                font-size: 8.8pt;
                font-weight: bold;
                text-transform: uppercase;
                line-height: 1.15;
                margin: 0;
                padding: 0;
            }
            .summary-table .th-bi .th-ta {
                display: block;
                font-family: TAMIL_FONT_PLACEHOLDER;
                font-size: 8.2pt;
                margin: 0.12em 0 0 0;
                padding: 0;
                font-weight: normal !important;
                text-transform: none;
                line-height: 1.15;
            }
            .summary-table td { padding: 1.6mm 1.4mm; border-bottom: 0.25mm solid #e7edf5; text-align: center; vertical-align: middle; }
            .summary-table tr:nth-child(even) td { background: #fafcff; }
            .status-pill {
                display: inline-block;
                padding: 0.6mm 1.6mm;
                border-radius: 2mm;
                font-size: 8.6pt;
                font-weight: bold;
                text-align: center;
            }
            .status-pill .st-en {
                display: block;
                font-family: helvetica;
                line-height: 1.15;
                margin: 0;
            }
            .status-pill .st-ta {
                display: block;
                font-family: TAMIL_FONT_PLACEHOLDER;
                font-size: 7.8pt;
                margin: 0.12em 0 0 0;
                font-weight: normal !important;
                line-height: 1.15;
            }
            .status-active { background: #e8f7ed; color: #196b33; border: 0.25mm solid #b7dfc2; }
            .status-expired { background: #fdeaea; color: #9b1c1c; border: 0.25mm solid #efb8b8; }
            .footer {
                margin-top: 16px;
                text-align: center;
                font-size: 12pt;
            }
            .footer .bi-en { font-family: helvetica; font-weight: bold; margin: 0; line-height: 1.2; }
            .footer .bi-ta { font-family: TAMIL_FONT_PLACEHOLDER; font-size: 11pt; margin-top: 0.12em; font-weight: normal !important; line-height: 1.2; }
            .range-sep-inline {
                display: inline;
                white-space: nowrap;
                margin: 0 1.2mm;
                font-weight: bold;
            }
            .range-sep-inline .rs-en { font-family: helvetica; font-size: inherit; font-weight: bold; }
            .range-sep-inline .rs-ta { font-family: TAMIL_FONT_PLACEHOLDER; font-size: inherit; font-weight: normal !important; }
            .sign-missing { text-align: center; line-height: 1.2; color: #666; }
            .sign-missing .bi-en { font-size: 8pt; font-weight: bold; margin: 0; }
            .sign-missing .bi-ta { font-family: TAMIL_FONT_PLACEHOLDER; font-size: 7.5pt; margin-top: 0.12em; font-weight: normal !important; }
            .table-empty-msg { text-align: center; padding: 3mm 2mm; font-size: 14pt; line-height: 1.25; }
            .table-empty-msg .bi-en { font-weight: bold; margin: 0; line-height: 1.2; }
            .table-empty-msg .bi-ta { font-family: TAMIL_FONT_PLACEHOLDER; font-size: 12pt; margin-top: 0.12em; font-weight: normal !important; line-height: 1.2; }
            </style>'
        ), \Mpdf\HTMLParserMode::HEADER_CSS);
                
        $photoPath = !empty($applicant_photo->upload_path) ? public_path($applicant_photo->upload_path): null;
        // var_dump($photoPath);exit;
        $signPath  = !empty($applicant_sign?->uploaded_doc) ? public_path($applicant_sign->uploaded_doc) : null;
        

        // var_dump($photoPath);
        // var_dump($signPath);
        // exit;

        $qrValue = 'Tnelb QR Testing';

        $html = '
        <div class="card">

            <!-- HEADER -->
            <div class="header">
                <div class="hdr-stack header-main">
                    <div class="bi-en">GOVERNMENT OF TAMIL NADU</div>
                    <div class="bi-ta" lang="ta">தமிழ்நாடு அரசு</div>
                </div>
                <div class="hdr-stack header-title">
                    <div class="bi-en">TAMIL NADU ELECTRICAL LICENCING BOARD</div>
                    <div class="bi-ta" lang="ta">மின்சார உரிமையாளர்கள் வாரியம்</div>
                </div>
                <div class="hdr-stack header-sub">
                    <div class="bi-en">Thiru Vi. Ka. Industrial Estate, Guindy, Chennai - 600 032.</div>
                    <div class="bi-ta" lang="ta">திரு.வி.கா. தொழிற்சாலை, கிண்டி, சென்னை – 600032.</div>
                </div>
            </div>

            <!-- BODY -->
            <div class="content">

               <table width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <!-- LEFT : DETAILS -->
                        <td width="70%" valign="top">

                            <table class="info-table">
                                <tr>
                                    <td class="lbl"><div class="lbl-bi"><div class="lbl-en">Certificate Number</div><div class="lbl-ta" lang="ta">சான்றிதழ் எண்</div></div></td>
                                    <td class="colon">:</td>
                                    <td class="val">'.$applicant->license_number.'</td>
                                </tr>
                                <tr>
                                    <td class="lbl"><div class="lbl-bi"><div class="lbl-en">Date of Issue</div><div class="lbl-ta" lang="ta">வழங்கப்பட்ட தேதி</div></div></td>
                                    <td class="colon">:</td>
                                    <td class="val">'.date('d M Y', strtotime($applicant->issued_at)).'</td>
                                </tr>
                                 <tr>
                                    <td class="lbl"><div class="lbl-bi"><div class="lbl-en">Validity</div><div class="lbl-ta" lang="ta">செல்லுபடியாகும் காலம்</div></div></td>
                                    <td class="colon">:</td>
                                    <td class="val">'.format_date($applicant->issued_from).' <span class="range-sep-inline"><span class="rs-en">To</span> <span class="rs-ta" lang="ta">வரை</span></span> '.format_date($applicant->expires_at).'</td>
                                </tr>
                                <tr>
                                    <td class="lbl"><div class="lbl-bi"><div class="lbl-en">Name</div><div class="lbl-ta" lang="ta">பெயர்</div></div></td>
                                    <td class="colon">:</td>
                                    <td class="val">'.$applicant->name.'</td>
                                </tr>
                                <tr>
                                    <td class="lbl"><div class="lbl-bi"><div class="lbl-en">Father / Husband Name</div><div class="lbl-ta" lang="ta">தந்தை / கணவர் பெயர்</div></div></td>
                                    <td class="colon">:</td>
                                    <td class="val">'.$applicant->fathers_name.'</td>
                                </tr>
                                <tr>
                                    <td class="lbl"><div class="lbl-bi"><div class="lbl-en">Date of Birth</div><div class="lbl-ta" lang="ta">பிறந்த தேதி</div></div></td>
                                    <td class="colon">:</td>
                                    <td class="val">'.format_date($applicant->d_o_b).'</td>
                                </tr>
                                <tr>
                                    <td class="lbl"><div class="lbl-bi"><div class="lbl-en">Address</div><div class="lbl-ta" lang="ta">முகவரி</div></div></td>
                                    <td class="colon">:</td>
                                    <td class="val">'.$applicant->applicants_address.'</td>
                                </tr>
                            </table>

                        </td>

                        <!-- RIGHT : PHOTO -->
                        <td width="30%" valign="top">
                            <table width="100%" cellspacing="0" cellpadding="0">
                                <!-- PHOTO ROW -->
                                <tr>
                                    <td align="center">
                                        <div class="photo-frame">
                                            <div class="photo-inner">
                                            '.($photoPath
                                                ? '<img src="'.$photoPath.'" style="width:38mm; height:38mm; object-fit:cover; display:block; margin:0 auto;">'
                                                : '').'
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <!-- SPACE BETWEEN PHOTO & QR -->
                                <tr>
                                    <td height="3mm"></td>
                                </tr>

                                <!-- SIGNATURE ROW -->
                                <tr>
                                    <td align="center">
                                        <div class="sign-frame">
                                            <div class="sign-inner">
                                            '.($signPath
                                                ? '<img src="'.$signPath.'" style="width:34mm; height:10mm; object-fit:contain; vertical-align:middle;">'
                                                : '<div class="sign-missing"><div class="bi-en">Signature not available</div><div class="bi-ta" lang="ta">கையெழுத்து இல்லை</div></div>').'
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <!-- SPACE BETWEEN SIGNATURE & QR -->
                                <tr>
                                    <td height="3mm"></td>
                                </tr>

                                <!-- QR ROW (same 38×38 mm box as photo) -->
                                <tr>
                                    <td align="center">
                                        <div class="qr-box">
                                            <table width="100%" height="100%"><tr><td align="center" valign="middle">
                                            <barcode code="'.$qrValue.'" type="QR" size="1.42" error="M" />
                                            </td></tr></table>
                                        </div>
                                    </td>
                                </tr>

                                <!-- BOTTOM SAFE SPACE -->
                                <tr>
                                    <td height="4mm"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <div class="summary-card">
                    <div class="summary-heading"><div class="bi-en">Issued Certificates</div><div class="bi-ta" lang="ta">வழங்கப்பட்ட சான்றிதழ்கள்</div></div>
                    <table class="summary-table" width="100%" cellspacing="0" cellpadding="0">
                    <thead>
                        <tr>
                            <th width="6%"><div class="th-bi"><div class="th-en">#</div><div class="th-ta" lang="ta">எண்</div></div></th>
                            <th width="28%"><div class="th-bi"><div class="th-en">Cert. No.</div><div class="th-ta" lang="ta">சான்றிதழ் எண்</div></div></th>
                            <th width="20%"><div class="th-bi"><div class="th-en">Date Of Issue</div><div class="th-ta" lang="ta">வழங்கிய தேதி</div></div></th>
                            <th width="20%"><div class="th-bi"><div class="th-en">Expires On</div><div class="th-ta" lang="ta">காலாவதி தேதி</div></div></th>
                        </tr>
                    </thead>
                    <tbody>
                    '.$certificateRowsHtml.'
                    </tbody>
                    </table>
                </div>

            </div>

            <div class="footer-spacer"></div>

            <!-- FOOTER -->
            <div class="footer">
                <div class="bi-en">Issued by TNELB | Tamil Nadu</div>
                <div class="bi-ta" lang="ta">TNELB வழங்கியது | தமிழ்நாடு</div>
            </div>

        </div>
        ';
        // Inline Tamil font — mPDF often applies stylesheet fonts in header/body blocks but not in nested <td>
        $html = preg_replace(
            '/<(div|span) class="(bi-ta|lbl-ta|th-ta|st-ta)" lang="ta">/u',
            '<$1 class="$2" lang="ta" style="font-family: ' . $tamilFontFamily . '; font-weight: normal;">',
            $html
        );
        $html = preg_replace(
            '/<span class="rs-ta" lang="ta">/u',
            '<span class="rs-ta" lang="ta" style="font-family: ' . $tamilFontFamily . '; font-weight: normal;">',
            $html
        );

        $mpdf->WriteHTML($html);
        return response($mpdf->Output('Application_Details.pdf', 'I'))->header('Content-Type', 'application/pdf');

    }


    public function generateFormaPDF($application_id)
    {
        // Fetch application details

        // dd($application_id);
        // exit;
        $application = DB::table('tnelb_ea_applications')->where('application_id', $application_id)->first();

        $appltype = trim($application->appl_type);
       
        if($appltype === 'N'){
                $applicant = DB::table('tnelb_license')
            ->join('tnelb_ea_applications', 'tnelb_license.application_id', '=', 'tnelb_ea_applications.application_id')
            ->where('tnelb_license.application_id', $application_id)
            ->select(
                'tnelb_license.application_id',
                'tnelb_license.issued_by',
                'tnelb_license.issued_at',
                'tnelb_license.expires_at',
                'tnelb_ea_applications.applicant_name AS name',
                // 'tnelb_applicant_formA.fathers_name',
                // 'tnelb_applicant_formA.applicants_address',
                // 'tnelb_applicant_formA.d_o_b',
                // 'tnelb_applicant_formA.age',
                'tnelb_ea_applications.license_name',
                'tnelb_ea_applications.form_name',
                'tnelb_license.license_number'
            )
            ->first();

            $staffDetails = DB::table('tnelb_applicant_cl_staffdetails')
            ->where('application_id', $application_id)
            ->orderby('id')
            // ->where('staff_flag', 1)
            ->get();

        }else{
    $applicant = DB::table('tnelb_renewal_license')
            ->join('tnelb_ea_applications', 'tnelb_renewal_license.application_id', '=', 'tnelb_ea_applications.application_id')
            ->where('tnelb_renewal_license.application_id', $application_id)
            ->select(
                'tnelb_renewal_license.application_id',
                'tnelb_renewal_license.issued_by',
                'tnelb_renewal_license.issued_at',
                'tnelb_renewal_license.expires_at',
                'tnelb_ea_applications.applicant_name AS name',
                // 'tnelb_applicant_formA.fathers_name',
                // 'tnelb_applicant_formA.applicants_address',
                // 'tnelb_applicant_formA.d_o_b',
                // 'tnelb_applicant_formA.age',
                'tnelb_ea_applications.license_name',
                'tnelb_ea_applications.form_name',
                'tnelb_renewal_license.license_number'
            )
            ->first();

            $staffDetails = DB::table('tnelb_applicant_cl_staffdetails')
            ->where('application_id', $application_id)
            ->orderby('id')
            // ->where('staff_flag', 1)
            ->get();

        }
    

    
        if (!$applicant) {
            return back()->with('error', 'Application not found.');
        }
    
        
        $payment = DB::table('payments')->where('application_id', $application_id)->first();
    
        // Initialize mPDF
        $mpdf = new Mpdf(['default_font_size' => 10]);
        $mpdf->SetTitle('TNELB Application License ' . $applicant->license_name);

        
        $mpdf->WriteHTML('<style>
        body {  }
        p, td, th { padding: 0px; }
        p 
        .tbl_center { text-align: center; }
        .mt-2 { margin-top: 5; }
        table { border-collapse: collapse; width: 100%; }
        th, td {  padding: 8px; text-align:left; }
        .highlight { font-weight: bold; color: white; background-color: green; padding: 5px; text-align:center; font-size:16px; }
        .photo-container { text-align: right; padding-right: 10px; }
        .photo-container img { width: 132px; height: 170px; border: 1px solid #000; object-fit: cover; display: block; }
        .highlight_text{color:green;}
        .staff_tbl td{text-align:center;}
    </style>', \Mpdf\HTMLParserMode::HEADER_CSS);
    
        // Start building the PDF content
        $html = '
        <h3 style="text-align: center;" class="">GOVERNMENT OF TAMILNADU</h3>
        <h4 style="text-align: center;" class="">THE ELECTRICAL LICENSING BOARD</h4>
        <p style="text-align: center;">Thiru.Vi.Ka.Indl.Estate, Guindy, Chennai – 600032.</p>
        <h4 style="text-align: center;" class="highlight_text"> Form ' . $applicant->form_name . ' License "' . $applicant->license_name . '"</h4>
        <h3  style="text-align: center;"><strong>License for Contractor Certificate</strong></h3>
        <h3 style="text-align: center;" class=""> License Number : <span class = "highlight_text">' . $applicant->license_number . '</span></h3>';
    
     if($appltype === 'N') 
     {
        $apply_type= "Fresh Application";
     }else{
        $apply_type= "Renewal Application";
     }

    
        $html .= '
        <h4 class="mt-2 highlight"> License Summary</h4>
        <table>
            <tr><th class="">Applicantion ID</th><td>' . $applicant->application_id . '</td></tr>
            <tr><th class="">Name of Electrical Contractor/s <br> licence  applied for </th><td>' . $applicant->name . '</td></tr>
            <tr><th class="">License Name</th><td>' . $applicant->license_name . '</td></tr>
            <tr><th class="">License Type</th><td>' . $apply_type . '</td></tr>
            <tr><th class="">Issued By</th><td>' . $applicant->issued_by . '</td></tr>
            <tr><th class="">Issued At</th><td>' . date('d-m-Y', strtotime($applicant->issued_at)) . '</td></tr>
            <tr><th class="">Expired At</th><td>' . date('d-m-Y', strtotime($applicant->expires_at)) . '</td></tr>
        </table>';

       $html .= '
<h4 class="mt-2 highlight">Details of Staff appointed under this Contractor License</h4>
<table class="staff_tbl" border="1">
    <tr>
        <th>S.No</th>
        <th>Staff Name</th>
        <th>Qualification </th>
        <th>Category </th>
        <th>Competency Certificate Number & Validity </th>
    </tr>';

if ($staffDetails->count() > 0) {
    $i = 1;
    foreach ($staffDetails as $staff) {
        $html .= '
        <tr>
            <td>' . $i++ . '</td>
            <td>' . strtoupper($staff->staff_name) . '</td>
            <td>' . strtoupper($staff->staff_qualification) . '</td>
            <td>' . strtoupper($staff->staff_category) . '</td>
            <td>' . $staff->cc_number . ', ' . (!empty($staff->cc_validity) ? date('d-m-Y', strtotime($staff->cc_validity)) : 'N/A') . '</td>

        </tr>';
    }
} else {
    $html .= '<tr><td colspan="5" style="text-align:center;">No staff found</td></tr>';
}

$html .= '</table>';

    
        
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
        $html .= '
        <br>
       
        <p><strong>Date:</strong> ' . date('d-m-Y', strtotime($applicant->issued_at)) . '</p>';
    
        // Write HTML to PDF
        $mpdf->WriteHTML($html);
    
        // Output PDF
        return response($mpdf->Output('License_A_approval.pdf', 'I'))
            ->header('Content-Type', 'application/pdf');
    }

   // -----------for all contractor license generateFormcontractor_download-------------------

   public function generateFormcontractor_download($application_id)
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

      $mpdf = new Mpdf([
        'format' => [210, 175], 
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
        .license_name{ font-size:15px;text-align:center;font-weight:bold; text-decoration:underline;}
        .font-weight{font-weight:bold;}
        .blue{color:#074282;}
        .orange{color:#ec4b05;}
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
        
    </style>', \Mpdf\HTMLParserMode::HEADER_CSS);
    $grade_name = $applicant->license_name;
    // dd($grade_name);
    // exit;

        if($grade_name == 'EA') {
            $grade_name_txt= 'EA Grade Contractor Licence';
        } elseif($grade_name == 'ESA'){
             $grade_name_txt= 'ESA Grade Contractor Licence';
        }elseif($grade_name == 'ESB'){
             $grade_name_txt= 'ESB Grade Contractor Licence';
        }elseif($grade_name == 'EB'){
            $grade_name_txt= 'EB Grade Contractor Licence ';
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

                <p class="blue mb-10 pb-10">
                    Thiru.Vi.Ka. Indl. Estate, Guindy, Chennai - 600 032.
                </p>
                <br>

                <p class="license_name orange mt-10">
                    ' . $grade_name_txt . '
                </p>
            </div>

        </td>

         <!-- RIGHT QR -->
        <td width="10%" style="text-align:right; vertical-align:top; padding-top:10px;">
            <barcode 
                code="' . htmlspecialchars($qrData) . '" 
                type="QR" 
                size="1"
                error="H"
            />
        </td>

  
    </tr>
</table>';

    
   
        
  
         $html .= '
        <table style="width:100%; border:0; mt-1 mb-1">
            <tr>
                <td class="label " style="text-align:left;"><h4 class="orange  font-size-14">Licence No : '. $applicant->license_number . '</h4></td>
                <td class="label" style="text-align:right;"><h4 class="orange  font-size-14">Date of Issue : ' . format_date($applicant->issued_at) .'</h4></td>
            </tr>
        </table>';

         if($grade_name == 'ESB' || $grade_name == 'EB' ) {
            $grade_name_txt= 'EA Grade Contractor Licence';
       
        $html .='<p class="mt-1 mb-1 line-height-30 font-size-14 blue font-weight text-indent-40 text-justify"> Thiru/Thiruvalargal <span class="text-black"> '.$applicant->name .' (Application ID. '.$applicant->application_id.') </span> are licensed to undertake electrical system works for low and medium voltage consumers in Tamil Nadu, limited to a maximum of 50 kW (63 kVA generator). This license is granted according to the regulations of the Electrical Licensing Board, approved by the Government of Tamil Nadu in the following Government Orders, under Rule 45(1) of the Indian Electricity Rules, 1956. </p>';
         }

         else{

                $html .='<p class="mt-1 mb-1 line-height-30 font-size-14 blue font-weight text-indent-40 text-justify"> Thiru/Thiruvalargal <span class="text-black"> '.$applicant->name .' (Application ID. '.$applicant->application_id.') </span> is/are hereby authorised to carryout High Voltage, Medium Voltage and Low Voltage electrical works in the state of Tamil Nadu. This licence is issued under the regulations issued by the Government of Tamil Nadu in the following G.Os. under Rule 45(1) of the Indian Electricity Rules 1956. </p>';

         }
     
        $html .= '
        <p class="mt-5 mb-5 blue font-size-14 font-weight  text-indent-60">1) GO.M.S.No. 1246 Public works Dated 31.03.1955 </p>
        <p class="mt-5 mb-5 blue font-weight font-size-14 text-indent-60">2) GO.MS.No.1983 Public works Dated 7.10.1987 </p>
         <p class="mt-5 mb-5 blue font-weight font-size-14 text-indent-60">3) GO.MS.No.2744 Public works (vi) Department Dated 24.12.1990 </p>
         <p class="mt-5 mb-5 blue font-weight font-size-14 text-indent-60">4) GO.MS.No.27 Energy (B,) Department Dated 8.3.2000 </p>';

            $html .= '<p class=" mb-5 orange font-size-14 font-weight  text-indent-60 ">This license is valid for the following period : <span class="text-black"> '.   format_date($applicant->expires_at) .' </span></p>';



        $proprietors = DB::table('proprietordetailsform_A')
            ->where('application_id', $application_id)
            ->where('proprietor_flag', '1')
            ->orderBy('id')
            ->get();

        $html .= '
        <table style="width:100%; border:0;" class="mt-1 mb-1">
            <tr>
                <td class="label blue txt_uppercase" style="text-align:left;">
                    <h4>Proprietor / Partner / Director Name </h4>
                </td>
                <td> : </td>
                <td>
            ';

        if ($proprietors->count() > 0) {
            foreach ($proprietors as $proprietor) {
                $html .= '
                
                        ' . strtoupper($proprietor->proprietor_name) . ',
                ';
            }
        } else {
            $html .= '
            —';
        }

        $html .= '
        </td>
        </tr>
        <tr>
        <td class="label blue txt_uppercase" style="text-align:left;">
                    <h4>Name of the authorized person
        and specimen signature </h4>  </td>
        <td> : </td>
        <td> Authorized Person Name </td>
        
        </tr>
        </table>';


           $html .= '
           <br><br>
         
            <table style="width:100%; border:0;" class="mt-1 mb-1">
                <tr>
                    <td class="label font-size-14 blue font-weight" style="text-align:left;">Secretary </td>
                    <td class="label font-size-14 blue font-weight" style="text-align:right;">President</td>
                </tr>
            </table>';



      

    

        $html .= "<pagebreak />";


        $html .= '
        <h4 class="mt-2 orange font-size-14 font-weight">Staff Details</h4>
        <table class=" width = 50%" >';

        if ($staffDetails->count() > 0) {
            $i = 1;
            foreach ($staffDetails as $staff) {

                $dates = \App\Http\Controllers\Admin\LicensepdfController::getStaffExpiryDate(
                    $staff->cc_number
                );

                $html .= '
                    <tr>
                        <td>' . $i++ . ') ' . strtoupper($staff->staff_name) . ' - ' . $staff->cc_number .  '  Valid From : <b>' . $dates['valid_from'] . '</b> Valid Upto : <b>' . $dates['valid_upto'] . '</b></td>
                      
                    </tr>';
            }
        }
        $html .= '</table>';
   $equiplist = DB::table('mst_equipment_tbls')
            ->where('equip_licence_name', $licence_id->id)
            ->orderBy('id')
            ->get();

        $equipmentlist = DB::table('equipmentforma_tbls')
            ->where('application_id', $application_id)
            ->get();

        // dd($equipmentlist->first()->licence_id);
        // exit;

        /* Map equip_id => equipment_value */
        $equipmentMap = $equipmentlist->pluck('equipment_value', 'equip_id')
            ->toArray();

        $html .= '
<h4 class="mt-2 orange font-size-14 font-weight">
    Equipments / Instruments Details
</h4>
<table class="width=50%">';
        $licenceId = optional($equipmentlist->first())->licence_id;
        if ($equiplist->count() > 0) {

            $i = 1;

            foreach ($equiplist as $index => $equip) {


                if ($equip->equip_licence_name == $licenceId) {

                    $equipmentValue = $equipmentMap[$equip->id] ?? 'N/A';

                    $html .= '
        <tr>
            <td>' . $i++ . ') ' . strtoupper($equip->equip_name) . '</td>
            <td>' . strtoupper($equipmentValue) . '</td>
        </tr>';
                }
            }
        } else {

            $html .= '
    <tr>
        <td colspan="2" style="text-align:center;">No equipment found</td>
    </tr>';
        }

        $html .= '</table>';
    
        
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




 public function generateFormcontractor_download_final_bk($application_id)
{
    // ---------------------------------------
    // 1. DETECT WHICH TABLE HAS THE APPLICATION
    // ---------------------------------------
    $application = null;
    $table_name = null;

    $tables = [
        'tnelb_esa_applications',
        'tnelb_esb_applications',
        'tnelb_eb_applications',
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

      $mpdf = new Mpdf([
        'format' => [210, 175], 
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 10,
        'margin_bottom' => 10,
        'default_font_size' => 9
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
        .license_name{ font-size:15px;text-align:center;font-weight:bold; text-decoration:underline;}
        .font-weight{font-weight:bold;}
        .blue{color:#074282;}
        .orange{color:#ec4b05;}
        .txt_uppercase{text-transform:uppercase;}
        .line-height-30{line-height:25px;}
        .text-indent-40 {text-indent: 40px;}
        .text-indent-60 {text-indent: 50px;}
        .text-justify {text-align: justify;}
        .font-size-14{ font-size:13px;}
        .mb-5{margin-bottom:5px;}
        .mb-1{margin-bottom:1px;}
        .mb-10{margin-bottom:10px;}
        .pb-10{padding: bottom 10px!important;}
        .mt-5{margin-top:5px;}
        .mt-1{margin-top:1px;}
        
    </style>', \Mpdf\HTMLParserMode::HEADER_CSS);
    $grade_name = $applicant->license_name;
    // dd($grade_name);
    // exit;

        if($grade_name == 'EA') {
            $grade_name_txt= 'EA Grade Contractor Licence';
        } elseif($grade_name == 'ESA'){
             $grade_name_txt= 'ESA Grade Contractor Licence';
        }elseif($grade_name == 'ESB'){
             $grade_name_txt= 'ESB Grade Contractor Licence';
        }elseif($grade_name == 'EB'){
            $grade_name_txt= 'EB Grade Contractor Licence ';
        }
        $qrData = url('/verify-certificate/' . $applicant->application_id);

    
        
        // Start building the PDF content
      $html = '
<table width="100%" class="mt-1 mb-1">
    <tr>
        <!-- LEFT CONTENT -->
        <td width="90%" style="text-align:center; padding-top:10px;">

            <div class="header-block">
                <h3 class="blue mb-10 pb-10 ">GOVERNMENT OF TAMILNADU</h3>
                <br>

                <h3 class="blue mb-10 pb-10">ELECTRICAL LICENSING BOARD</h3>
                <br>

                <p class="blue mb-10 pb-10">
                    Thiru.Vi.Ka. Indl. Estate, Guindy, Chennai - 600 032.
                </p>
                <br>

                <p class="license_name orange mt-10">
                    ' . $grade_name_txt . '
                </p>
            </div>

        </td>

        <!-- RIGHT QR -->
        <td width="10%" style="text-align:right; vertical-align:top; padding-top:10px;">
            <barcode 
                code="' . htmlspecialchars($qrData) . '" 
                type="QR" 
                size="1"
                error="H"
            />
        </td>
    </tr>
</table>';

    
   
        
  
         $html .= '
        <table style="width:100%; border:0; mt-1 mb-1">
            <tr>
                <td class="label " style="text-align:left;"><h4 class="orange  font-size-14">Licence No : '. $applicant->license_number . '</h4></td>
                <td class="label" style="text-align:right;"><h4 class="orange  font-size-14"> Issued Date : ' . format_date($applicant->issued_at) .'</h4></td>
            </tr>
        </table>';

         
        $html .='<p class="mt-1 mb-1 line-height-30 font-size-14 blue font-weight text-indent-40 text-justify"> Mr./Ms./Messrs. '.$applicant->name .' (Application ID. '.$applicant->application_id.') are licensed to undertake electrical system works for low and medium voltage consumers in Tamil Nadu, limited to a maximum of 50 kW (63 kVA generator). This license is granted according to the regulations of the Electrical Licensing Board, approved by the Government of Tamil Nadu in the following Government Orders, under Rule 45(1) of the Indian Electricity Rules, 1956. </p>';

     
        $html .= '
        <p class="mt-5 mb-5 blue font-size-14 font-weight  text-indent-60">1.Government Order No. M.S.No. 1246 Public Works Department, dated 31.3.1955 </p>
        <p class="mt-5 mb-5 blue font-weight font-size-14 text-indent-60">2.Government Order No. M.S.No.1983 Public Works Department, dated 07.10.1987 </p>
         <p class="mt-5 mb-5 blue font-weight font-size-14 text-indent-60">3.Government Order No. M.S.No.2744 Public Works Department, dated 24.12.1990 </p>
         <p class="mt-5 mb-5 blue font-weight font-size-14 text-indent-60">4.Government Order No. M.S.No.27 Energy (B.) Department, dated 08.03.2000 </p>';

            $html .= '<p class=" mb-5 orange font-size-14 font-weight  text-indent-60 txt_uppercase">This license is valid for the following period :</p>';



        $proprietors = DB::table('proprietordetailsform_A')
            ->where('application_id', $application_id)
            ->where('proprietor_flag', '1')
            ->orderBy('id')
            ->get();

        $html .= '
        <table style="width:100%; border:0;" class="mt-1 mb-1">
            <tr>
                <td class="label blue txt_uppercase" style="text-align:left;">
                    <h4>Proprietor / Partner / Director Name </h4>
                </td>
                <td> : </td>
                <td>
            ';

        if ($proprietors->count() > 0) {
            foreach ($proprietors as $proprietor) {
                $html .= '
                
                        ' . strtoupper($proprietor->proprietor_name) . ',
                ';
            }
        } else {
            $html .= '
            —';
        }

        $html .= '
        </td>
        </tr>
        <tr>
        <td class="label blue txt_uppercase" style="text-align:left;">
                    <h4>Name of the authorized person
        and specimen signature </h4>  </td>
        <td> : </td>

        <td> Authorized Person Name </td>

        
        </tr>
        </table>';


           $html .= '
         
            <table style="width:100%; border:0;" class="mt-1 mb-1">
                <tr>
                    <td class="label font-size-14 blue font-weight" style="text-align:left;">Secretary </td>
                    <td class="label font-size-14 blue font-weight" style="text-align:right;">President</td>
                </tr>
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
        // $mpdf->SetFooter('
        // <table style="width:100%; font-size:12px;">
        //     <tr>
        //         <td style="text-align:left;">TNELB</td>
        //         <td class="label" style="text-align:right;">Date : ' . date('d-m-Y') . '</td>
                
        //     </tr>
        // </table>
        // ');

        return response($mpdf->Output('License_A_approval.pdf', 'I'))
            ->header('Content-Type', 'application/pdf');
}



// -----------for all contractor license generateFormcontractor_download-------------------

   public function generateFormcontractor_download_bk($application_id)
{
    // ---------------------------------------
    // 1. DETECT WHICH TABLE HAS THE APPLICATION
    // ---------------------------------------
    $application = null;
    $table_name = null;

    $tables = [
        'tnelb_esa_applications',
        'tnelb_esb_applications',
        'tnelb_eb_applications',
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

        $mpdf = new Mpdf(['default_font_size' => 10]);
        $mpdf->SetTitle('TNELB Application License ' . $applicant->license_name);

        $formname = $applicant->form_name;

        // dd($formname);
        // exit;

        $license_name = DB::table('mst_licences')->where('form_code', $formname)->first();


        // dd($license_name);
        // exit;
        
        $mpdf->WriteHTML('<style>
        body {  }
        p, td, th { padding: 0px; }
        p 
        .tbl_center { text-align: center;!important }
        .mt-2 { margin-top: 5; }
        table { border-collapse: collapse; width: 100%; }
        th, td {  padding: 8px; text-align:left; }
        .highlight { font-weight: bold; color: white; background-color: green; padding: 5px; text-align:center; font-size:16px; }
        .photo-container { text-align: right; padding-right: 10px; }
        .photo-container img { width: 132px; height: 170px; border: 1px solid #000; object-fit: cover; display: block; }
        .highlight_text{color:green;}
        .staff_tbl td{text-align:center;}
    </style>', \Mpdf\HTMLParserMode::HEADER_CSS);
    
        // Start building the PDF content
        $html = '
        <h3 style="text-align: center;" class="">GOVERNMENT OF TAMILNADU</h3>
        <h4 style="text-align: center;" class="">THE ELECTRICAL LICENSING BOARD</h4>
        <p style="text-align: center;">Thiru.Vi.Ka.Indl.Estate, Guindy, Chennai – 600032.</p>
        <h4 style="text-align: center;" class=""> Form  "' . $license_name->form_code . '" License "' . $license_name->licence_name . '"</h4>
        
        <h3 style="text-align: center;" class=""> License Number : <span class = "">' . $applicant->license_number . '</span></h3>';
    
     if($appltype === 'N') 
     {
        $apply_type= "Fresh Application";
     }else{
        $apply_type= "Renewal Application";
     }
        
  
       

        $html .= '<table class="tbl-no-border" style="margin: 0 auto; width: 70%;">';
        

       
        $html .= '  <tr><td class="label">Applicantion ID :</td><td class="value">'. $applicant->application_id .'</td></tr>';
        $html .= '  <tr><td class="label">Applicant Name :</td><td class="value">'. $applicant->name .'</td></tr>';

        $html .= '  <tr><td class="label">Application Type :</td><td class="value">'. $apply_type .'</td></tr>';
        
       
        $html .= '</table>';
  

       $html .= '
<h4 class="mt-2 tbl_center">Details of Staff appointed under this Contractor License</h4>
<table class="staff_tbl" border="1">
    <tr>
        <th>S.No</th>
        <th>Staff Name</th>
        <th>Qualification </th>
        <th>Category </th>
        <th>Competency Certificate Number & Validity </th>
    </tr>';

    if ($staffDetails->count() > 0) {
        $i = 1;
        foreach ($staffDetails as $staff) {
            $html .= '
            <tr>
                <td>' . $i++ . '</td>
                <td>' . strtoupper($staff->staff_name) . '</td>
                <td>' . strtoupper($staff->staff_qualification) . '</td>
                <td>' . strtoupper($staff->staff_category) . '</td>
                <td>' . $staff->cc_number . ', ' . (!empty($staff->cc_validity) ? date('d-m-Y', strtotime($staff->cc_validity)) : 'N/A') . '</td>

            </tr>';
        }
    } else {
        $html .= '<tr><td colspan="5" style="text-align:center;">No staff found</td></tr>';
    }

    $html .= '</table>';

      $html .= '
        <table style="width:100%; border:0;">
            <tr>
                <td class="label " style="text-align:left;"><h4>Issued At : '. format_date($applicant->issued_at) . '</h4></td>
                <td class="label" style="text-align:right;"><h4>Expires At : ' . format_date($applicant->expires_at) .'</h4></td>
            </tr>
        </table>';


         $html .= '
            <br><br>
            <br><br>
            <br><br>
            <table style="width:100%; border:0;">
                <tr>
                    <td class="label" style="text-align:left;">Date : ' . date('d-m-Y') . '</td>
                    <td class="label" style="text-align:right;">Issued By : ' . $applicant->issued_by . '</td>
                </tr>
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
                <td style="text-align:right;">TNELB</td>
                
            </tr>
        </table>
        ');

        return response($mpdf->Output('License_A_approval.pdf', 'I'))
            ->header('Content-Type', 'application/pdf');
}
  // ---------------------------- admin side final pdf forma download---------------------------

   public function generateForma_downloadPDF($application_id)
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

      $mpdf = new Mpdf([
        'format' => [210, 175], 
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 10,
        'margin_bottom' => 10,
        'default_font_size' => 9,
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
        .license_name{ font-size:15px;text-align:center;font-weight:bold; text-decoration:underline;}
        .font-weight{font-weight:bold;}
        .blue{color:#074282;}
        .orange{color:#ec4b05;}
        .txt_uppercase{text-transform:uppercase;}
        .line-height-30{line-height:20px;}
        .text-indent-40 {text-indent: 40px;}
        .text-indent-60 {text-indent: 50px;}
        .text-justify {text-align: justify;}
        .font-size-14{ font-size:13px;}
        .mb-5{margin-bottom:5px;}
        .mb-1{margin-bottom:1px;}
        .mb-10{margin-bottom:10px;}
        .pb-10{padding: bottom 10px!important;}
        .mt-5{margin-top:5px;}
        .mt-1{margin-top:1px;}
        .text-black{color:black;}
        
    </style>', \Mpdf\HTMLParserMode::HEADER_CSS);
    $grade_name = $applicant->license_name;
    // dd($grade_name);
    // exit;

        if($grade_name == 'EA') {
            $grade_name_txt= 'EA Grade Contractor Licence';
        } elseif($grade_name == 'ESA'){
             $grade_name_txt= 'ESA Grade Contractor Licence';
        }elseif($grade_name == 'ESB'){
             $grade_name_txt= 'ESB Grade Contractor Licence';
        }elseif($grade_name == 'EB'){
            $grade_name_txt= 'EB Grade Contractor Licence ';
        }
        // $qrData = url('/verify-certificate/' . $applicant->application_id);

    
        
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

                <p class="blue mb-10 pb-10">
                    Thiru.Vi.Ka. Indl. Estate, Guindy, Chennai - 600 032.
                </p>
                <br>

                <p class="license_name orange mt-10">
                    ' . $grade_name_txt . '
                </p>
            </div>

        </td>

  
    </tr>
</table>';

    
   
        
  
         $html .= '
        <table style="width:100%; border:0; mt-1 mb-1">
            <tr>
                <td class="label " style="text-align:left;"><h4 class="orange  font-size-14">Licence No : '. $applicant->license_number . '</h4></td>
                <td class="label" style="text-align:right;"><h4 class="orange  font-size-14">Date of Issue : ' . format_date($applicant->issued_at) .'</h4></td>
            </tr>
        </table>';

         if($grade_name == 'ESB' || $grade_name == 'EB' ) {
            $grade_name_txt= 'EA Grade Contractor Licence';
       
        $html .='<p class="mt-1 mb-1 line-height-30 font-size-14 blue font-weight text-indent-40 text-justify"> Thiru/Thiruvalargal <span class="text-black"> '.$applicant->name .' (Application ID. '.$applicant->application_id.') </span> are licensed to undertake electrical system works for low and medium voltage consumers in Tamil Nadu, limited to a maximum of 50 kW (63 kVA generator). This license is granted according to the regulations of the Electrical Licensing Board, approved by the Government of Tamil Nadu in the following Government Orders, under Rule 45(1) of the Indian Electricity Rules, 1956. </p>';
         }

         else{

                $html .='<p class="mt-1 mb-1 line-height-30 font-size-14 blue font-weight text-indent-40 text-justify"> Thiru/Thiruvalargal <span class="text-black"> '.$applicant->name .' (Application ID. '.$applicant->application_id.') </span> is/are hereby authorised to carryout High Voltage, Medium Voltage and Low Voltage electrical works in the state of Tamil Nadu. This licence is issued under the regulations issued by the Government of Tamil Nadu in the following G.Os. under Rule 45(1) of the Indian Electricity Rules 1956. </p>';

         }
     
        $html .= '
        <p class="mt-5 mb-5 blue font-size-14 font-weight  text-indent-60">1) GO.M.S.No. 1246 Public works Dated 31.03.1955 </p>
        <p class="mt-5 mb-5 blue font-weight font-size-14 text-indent-60">2) GO.MS.No.1983 Public works Dated 7.10.1987 </p>
         <p class="mt-5 mb-5 blue font-weight font-size-14 text-indent-60">3) GO.MS.No.2744 Public works (vi) Department Dated 24.12.1990 </p>
         <p class="mt-5 mb-5 blue font-weight font-size-14 text-indent-60">4) GO.MS.No.27 Energy (B,) Department Dated 8.3.2000 </p>';

            $html .= '<p class=" mb-5 orange font-size-14 font-weight  text-indent-60 ">This license is valid for the following period :</p>';



        $proprietors = DB::table('proprietordetailsform_A')
            ->where('application_id', $application_id)
            ->where('proprietor_flag', '1')
            ->orderBy('id')
            ->get();

        $html .= '
        <table style="width:100%; border:0;" class="mt-1 mb-1">
            <tr>
                <td class="label blue txt_uppercase" style="text-align:left;">
                    <h4>Proprietor / Partner / Director Name </h4>
                </td>
                <td> : </td>
                <td>
            ';

        if ($proprietors->count() > 0) {
            foreach ($proprietors as $proprietor) {
                $html .= '
                
                        ' . strtoupper($proprietor->proprietor_name) . ',
                ';
            }
        } else {
            $html .= '
            —';
        }

        $html .= '
        </td>
        </tr>
        <tr>
        <td class="label blue txt_uppercase" style="text-align:left;">
                    <h4>Name of the authorized person
        and specimen signature </h4>  </td>
        <td> : </td>
        <td> Authorized Person Name </td>
        
        </tr>
        </table>';


           $html .= '
           <br><br>
         
            <table style="width:100%; border:0;" class="mt-1 mb-1">
                <tr>
                    <td class="label font-size-14 blue font-weight" style="text-align:left;">Secretary </td>
                    <td class="label font-size-14 blue font-weight" style="text-align:right;">President</td>
                </tr>
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

//   ----------------------------------------------------

      public function generateForma_downloadPDF_bk($application_id)
    {
        // Fetch application details

        // dd($application_id);
       // Detect application from 4 form tables
    $application = DB::table('tnelb_esa_applications')->where('application_id', $application_id)->first()
        ?? DB::table('tnelb_esb_applications')->where('application_id', $application_id)->first()
        ?? DB::table('tnelb_eb_applications')->where('application_id', $application_id)->first()
        ?? DB::table('tnelb_ea_applications')->where('application_id', $application_id)->first();

    if (!$application) {
        return back()->with('error', 'Application not found.');
    }

    $formname = trim($application->form_name);
    $appltype = trim($application->appl_type); // N or R

    // Dynamic form table mapping
    $formTables = [
        'SA' => 'tnelb_esa_applications',
        'SB' => 'tnelb_esb_applications',
        'A'  => 'tnelb_ea_applications',
        'B'  => 'tnelb_eb_applications',
    ];

    // Pick correct application table
    $formTable = $formTables[$formname];

    // License table based on fresh/renew
    $licenseTable = ($appltype === 'N') ? 'tnelb_license' : 'tnelb_renewal_license';

    // Applicant details (License + Form Table Join)
    $applicant = DB::table($licenseTable)
        ->join($formTable, "$licenseTable.application_id", '=', "$formTable.application_id")
        ->where("$licenseTable.application_id", $application_id)
        ->select(
            "$licenseTable.application_id",
            "$licenseTable.issued_by",
            "$licenseTable.issued_at",
            "$licenseTable.expires_at",
            "$licenseTable.license_number",
            "$formTable.applicant_name AS name",
            "$formTable.license_name",
            "$formTable.form_name"
        )
        ->first();

    if (!$applicant) {
        return back()->with('error', 'Application details missing.');
    }
    

    
       $staffDetails = DB::table('tnelb_applicant_cl_staffdetails')
        ->where('application_id', $application_id)
        ->orderBy('id')
        ->get();
        
       $license_name = DB::table('mst_licences')->where('form_code', $formname)->first();
        $payment = DB::table('payments')->where('application_id', $application_id)->first();    
    
        // Initialize mPDF
        $mpdf = new Mpdf(['default_font_size' => 10]);
        $mpdf->SetTitle('TNELB Application License ' . $applicant->license_name);

        $formname = $applicant->form_name;

        // dd($formname);
        // exit;

        $license_name = DB::table('mst_licences')->where('form_code', $formname)->first();


        // dd($license_name);
        // exit;
        
        $mpdf->WriteHTML('<style>
        body {  }
        p, td, th { padding: 0px; }
        p 
        .tbl_center { text-align: center;!important }
        .mt-2 { margin-top: 5; }
        table { border-collapse: collapse; width: 100%; }
        th, td {  padding: 8px; text-align:left; }
        .highlight { font-weight: bold; color: white; background-color: green; padding: 5px; text-align:center; font-size:16px; }
        .photo-container { text-align: right; padding-right: 10px; }
        .photo-container img { width: 132px; height: 170px; border: 1px solid #000; object-fit: cover; display: block; }
        .highlight_text{color:green;}
        .staff_tbl td{text-align:center;}
    </style>', \Mpdf\HTMLParserMode::HEADER_CSS);
    
        // Start building the PDF content
        $html = '
        <h3 style="text-align: center;" class="">GOVERNMENT OF TAMILNADU</h3>
        <h4 style="text-align: center;" class="">THE ELECTRICAL LICENSING BOARD</h4>
        <p style="text-align: center;">Thiru.Vi.Ka.Indl.Estate, Guindy, Chennai – 600032.</p>
        <h4 style="text-align: center;" class="highlight_text"> Form  "' . $license_name->form_code . '" License "' . $license_name->licence_name . '"</h4>
        
        <h3 style="text-align: center;" class=""> License Number : <span class = "highlight_text">' . $applicant->license_number . '</span></h3>';
    
     if($appltype === 'N') 
     {
        $apply_type= "Fresh Application";
     }else{
        $apply_type= "Renewal Application";
     }
        
  
       

        $html .= '<table class="tbl-no-border" style="margin: 0 auto; width: 70%;">';
        

       
        $html .= '  <tr><td class="label">Applicantion ID :</td><td class="value">'. $applicant->application_id .'</td></tr>';
        $html .= '  <tr><td class="label">Applicant Name :</td><td class="value">'. $applicant->name .'</td></tr>';

        $html .= '  <tr><td class="label">Application Type :</td><td class="value">'. $apply_type .'</td></tr>';
        
       
        $html .= '</table>';
  

       $html .= '
<h4 class="mt-2 tbl_center">Details of Staff appointed under this Contractor License</h4>
<table class="staff_tbl" border="1">
    <tr>
        <th>S.No</th>
        <th>Staff Name</th>
        <th>Qualification </th>
        <th>Category </th>
        <th>Competency Certificate Number & Validity </th>
    </tr>';

    if ($staffDetails->count() > 0) {
        $i = 1;
        foreach ($staffDetails as $staff) {
            $html .= '
            <tr>
                <td>' . $i++ . '</td>
                <td>' . strtoupper($staff->staff_name) . '</td>
                <td>' . strtoupper($staff->staff_qualification) . '</td>
                <td>' . strtoupper($staff->staff_category) . '</td>
                <td>' . $staff->cc_number . ', ' . (!empty($staff->cc_validity) ? date('d-m-Y', strtotime($staff->cc_validity)) : 'N/A') . '</td>

            </tr>';
        }
    } else {
        $html .= '<tr><td colspan="5" style="text-align:center;">No staff found</td></tr>';
    }

    $html .= '</table>';

      $html .= '
        <table style="width:100%; border:0;">
            <tr>
                <td class="label " style="text-align:left;"><h4>Issued At : '. format_date($applicant->issued_at) . '</h4></td>
                <td class="label" style="text-align:right;"><h4>Expires At : ' . format_date($applicant->expires_at) .'</h4></td>
            </tr>
        </table>';


         $html .= '
            <br><br>
            <br><br>
            <br><br>
            <table style="width:100%; border:0;">
                <tr>
                    <td class="label" style="text-align:left;">Date : ' . date('d-m-Y') . '</td>
                    <td class="label" style="text-align:right;">Issued By : ' . $applicant->issued_by . '</td>
                </tr>
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
                <td style="text-align:right;">TNELB</td>
                
            </tr>
        </table>
        ');

        return response($mpdf->Output('License_A_approval.pdf', 'I'))
            ->header('Content-Type', 'application/pdf');

    
        
    }
    


    // -------------formsa print--------------------------------------------

    public function generateFormsaPDF($application_id)
    {
        // Fetch application details

       
        $application = DB::table('tnelb_esa_applications')->where('application_id', $application_id)->first();

        $appltype = trim($application->appl_type);
       
        if($appltype === 'N'){
                $applicant = DB::table('tnelb_license')
            ->join('tnelb_esa_applications', 'tnelb_license.application_id', '=', 'tnelb_esa_applications.application_id')
            ->where('tnelb_license.application_id', $application_id)
            ->select(
                'tnelb_license.application_id',
                'tnelb_license.issued_by',
                'tnelb_license.issued_at',
                'tnelb_license.expires_at',
                'tnelb_esa_applications.applicant_name AS name',
                // 'tnelb_applicant_formA.fathers_name',
                // 'tnelb_applicant_formA.applicants_address',
                // 'tnelb_applicant_formA.d_o_b',
                // 'tnelb_applicant_formA.age',
                'tnelb_esa_applications.license_name',
                'tnelb_esa_applications.form_name',
                'tnelb_license.license_number'
            )
            ->first();

            $staffDetails = DB::table('tnelb_applicant_cl_staffdetails')
            ->where('application_id', $application_id)
            ->orderby('id')
            // ->where('staff_flag', 1)
            ->get();

        }else{
    $applicant = DB::table('tnelb_renewal_license')
            ->join('tnelb_esa_applications', 'tnelb_renewal_license.application_id', '=', 'tnelb_esa_applications.application_id')
            ->where('tnelb_renewal_license.application_id', $application_id)
            ->select(
                'tnelb_renewal_license.application_id',
                'tnelb_renewal_license.issued_by',
                'tnelb_renewal_license.issued_at',
                'tnelb_renewal_license.expires_at',
                'tnelb_esa_applications.applicant_name AS name',
                // 'tnelb_applicant_formA.fathers_name',
                // 'tnelb_applicant_formA.applicants_address',
                // 'tnelb_applicant_formA.d_o_b',
                // 'tnelb_applicant_formA.age',
                'tnelb_esa_applications.license_name',
                'tnelb_esa_applications.form_name',
                'tnelb_renewal_license.license_number'
            )
            ->first();

            $staffDetails = DB::table('tnelb_applicant_cl_staffdetails')
            ->where('application_id', $application_id)
            ->orderby('id')
            // ->where('staff_flag', 1)
            ->get();

        }
    

    
        if (!$applicant) {
            return back()->with('error', 'Application not found.');
        }
    
        
        $payment = DB::table('payments')->where('application_id', $application_id)->first();
    
        // Initialize mPDF
        $mpdf = new Mpdf(['default_font_size' => 10]);
        $mpdf->SetTitle('TNELB Application License ' . $applicant->license_name);

        
        $mpdf->WriteHTML('<style>
        body {  }
        p, td, th { padding: 0px; }
        p 
        .tbl_center { text-align: center; }
        .mt-2 { margin-top: 5; }
        table { border-collapse: collapse; width: 100%; }
        th, td {  padding: 8px; text-align:left; }
        .highlight { font-weight: bold; color: white; background-color: #332ec7; padding: 5px; text-align:center; font-size:16px; }
        .photo-container { text-align: right; padding-right: 10px; }
        .photo-container img { width: 132px; height: 170px; border: 1px solid #000; object-fit: cover; display: block; }
        .highlight_text{color:#423a3a;}
        .staff_tbl td{text-align:center;}
    </style>', \Mpdf\HTMLParserMode::HEADER_CSS);
    
        // Start building the PDF content
        $html = '
        <h3 style="text-align: center;" class="">GOVERNMENT OF TAMILNADU</h3>
        <h4 style="text-align: center;" class="">THE ELECTRICAL LICENSING BOARD</h4>
        <p style="text-align: center;">Thiru.Vi.Ka.Indl.Estate, Guindy, Chennai – 600032.</p>
        <h4 style="text-align: center;" class="highlight_text"> Form ' . $applicant->form_name . ' License "' . $applicant->license_name . '"</h4>
        <h3  style="text-align: center;"><strong>License for Contractor Certificate</strong></h3>
        <h3 style="text-align: center;" class=""> License Number : <span class = "highlight_text">' . $applicant->license_number . '</span></h3>';
    
     if($appltype === 'N') 
     {
        $apply_type= "Fresh Application";
     }else{
        $apply_type= "Renewal Application";
     }

    
        $html .= '
        <h4 class="mt-2 highlight"> License Summary</h4>
        <table>
            <tr><th class="">Applicantion ID</th><td>' . $applicant->application_id . '</td></tr>
            <tr><th class="">Name of Electrical Contractor/s <br> licence  applied for </th><td>' . $applicant->name . '</td></tr>
            <tr><th class="">License Name</th><td>' . $applicant->license_name . '</td></tr>
            <tr><th class="">License Type</th><td>' . $apply_type . '</td></tr>
            <tr><th class="">Issued By</th><td>' . $applicant->issued_by . '</td></tr>
            <tr><th class="">Issued At</th><td>' . date('d-m-Y', strtotime($applicant->issued_at)) . '</td></tr>
            <tr><th class="">Expired At</th><td>' . date('d-m-Y', strtotime($applicant->expires_at)) . '</td></tr>
        </table>';

       $html .= '
<h4 class="mt-2 highlight">Details of Staff appointed under this Contractor License</h4>
<table class="staff_tbl" border="1">
    <tr>
        <th>S.No</th>
        <th>Staff Name</th>
        <th>Qualification </th>
        <th>Category </th>
        <th>Competency Certificate Number & Validity </th>
    </tr>';

    

    if ($staffDetails->count() > 0) {
        $i = 1;
        foreach ($staffDetails as $staff) {
            $html .= '
            <tr>
                <td>' . $i++ . '</td>
                <td>' . strtoupper($staff->staff_name) . '</td>
                <td>' . strtoupper($staff->staff_qualification) . '</td>
                <td>' . strtoupper($staff->staff_category) . '</td>
                <td>' . $staff->cc_number . ', ' . (!empty($staff->cc_validity) ? date('d-m-Y', strtotime($staff->cc_validity)) : 'N/A') . '</td>

            </tr>';
        }
    } else {
        $html .= '<tr><td colspan="5" style="text-align:center;">No staff found</td></tr>';
    }

    $html .= '</table>';

    
        
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
        $html .= '
        <br>
       
        <p><strong>Date:</strong> ' . date('d-m-Y', strtotime($applicant->issued_at)) . '</p>';
    
        // Write HTML to PDF
        $mpdf->WriteHTML($html);
    
        // Output PDF
        return response($mpdf->Output('License_A_approval.pdf', 'I'))
            ->header('Content-Type', 'application/pdf');
    }
    // ------------------------



     // -------------formsa print--------------------------------------------

    public function generateFormsbPDF($application_id)
    {
        // Fetch application details

       
        $application = DB::table('tnelb_esb_applications')->where('application_id', $application_id)->first();

        $appltype = trim($application->appl_type);
       
        if($appltype === 'N'){
                $applicant = DB::table('tnelb_license')
            ->join('tnelb_esb_applications', 'tnelb_license.application_id', '=', 'tnelb_esb_applications.application_id')
            ->where('tnelb_license.application_id', $application_id)
            ->select(
                'tnelb_license.application_id',
                'tnelb_license.issued_by',
                'tnelb_license.issued_at',
                'tnelb_license.expires_at',
                'tnelb_esb_applications.applicant_name AS name',
                // 'tnelb_applicant_formA.fathers_name',
                // 'tnelb_applicant_formA.applicants_address',
                // 'tnelb_applicant_formA.d_o_b',
                // 'tnelb_applicant_formA.age',
                'tnelb_esb_applications.license_name',
                'tnelb_esb_applications.form_name',
                'tnelb_license.license_number'
            )
            ->first();

            $staffDetails = DB::table('tnelb_applicant_cl_staffdetails')
            ->where('application_id', $application_id)
            ->orderby('id')
            // ->where('staff_flag', 1)
            ->get();

        }else{
    $applicant = DB::table('tnelb_renewal_license')
            ->join('tnelb_esb_applications', 'tnelb_renewal_license.application_id', '=', 'tnelb_esb_applications.application_id')
            ->where('tnelb_renewal_license.application_id', $application_id)
            ->select(
                'tnelb_renewal_license.application_id',
                'tnelb_renewal_license.issued_by',
                'tnelb_renewal_license.issued_at',
                'tnelb_renewal_license.expires_at',
                'tnelb_esb_applications.applicant_name AS name',
                // 'tnelb_applicant_formA.fathers_name',
                // 'tnelb_applicant_formA.applicants_address',
                // 'tnelb_applicant_formA.d_o_b',
                // 'tnelb_applicant_formA.age',
                'tnelb_esb_applications.license_name',
                'tnelb_esb_applications.form_name',
                'tnelb_renewal_license.license_number'
            )
            ->first();

            $staffDetails = DB::table('tnelb_applicant_cl_staffdetails')
            ->where('application_id', $application_id)
            ->orderby('id')
            // ->where('staff_flag', 1)
            ->get();

        }
    

    
        if (!$applicant) {
            return back()->with('error', 'Application not found.');
        }
    
        
        $payment = DB::table('payments')->where('application_id', $application_id)->first();
    
        // Initialize mPDF
        $mpdf = new Mpdf(['default_font_size' => 10]);
        $mpdf->SetTitle('TNELB Application License ' . $applicant->license_name);

        
        $mpdf->WriteHTML('<style>
        body {  }
        p, td, th { padding: 0px; }
        p 
        .tbl_center { text-align: center; }
        .mt-2 { margin-top: 5; }
        table { border-collapse: collapse; width: 100%; }
        th, td {  padding: 8px; text-align:left; }
        .highlight { font-weight: bold; color: white; background-color: #423a3a; padding: 5px; text-align:center; font-size:16px; }
        .photo-container { text-align: right; padding-right: 10px; }
        .photo-container img { width: 132px; height: 170px; border: 1px solid #000; object-fit: cover; display: block; }
        .highlight_text{color:#423a3a;}
        .staff_tbl td{text-align:center;}
    </style>', \Mpdf\HTMLParserMode::HEADER_CSS);
    
        // Start building the PDF content
        $html = '
        <h3 style="text-align: center;" class="">GOVERNMENT OF TAMILNADU</h3>
        <h4 style="text-align: center;" class="">THE ELECTRICAL LICENSING BOARD</h4>
        <p style="text-align: center;">Thiru.Vi.Ka.Indl.Estate, Guindy, Chennai – 600032.</p>
        <h4 style="text-align: center;" class="highlight_text"> Form ' . $applicant->form_name . ' License "' . $applicant->license_name . '"</h4>
        <h3  style="text-align: center;"><strong>License for Contractor Certificate</strong></h3>
        <h3 style="text-align: center;" class=""> License Number : <span class = "highlight_text">' . $applicant->license_number . '</span></h3>';
    
     if($appltype === 'N') 
     {
        $apply_type= "Fresh Application";
     }else{
        $apply_type= "Renewal Application";
     }

    
        $html .= '
        <h4 class="mt-2 highlight"> License Summary</h4>
        <table>
            <tr><th class="">Applicantion ID</th><td>' . $applicant->application_id . '</td></tr>
            <tr><th class="">Name of Electrical Contractor/s <br> licence  applied for </th><td>' . $applicant->name . '</td></tr>
            <tr><th class="">License Name</th><td>' . $applicant->license_name . '</td></tr>
            <tr><th class="">License Type</th><td>' . $apply_type . '</td></tr>
            <tr><th class="">Issued By</th><td>' . $applicant->issued_by . '</td></tr>
            <tr><th class="">Issued At</th><td>' . date('d-m-Y', strtotime($applicant->issued_at)) . '</td></tr>
            <tr><th class="">Expired At</th><td>' . date('d-m-Y', strtotime($applicant->expires_at)) . '</td></tr>
        </table>';

       $html .= '
<h4 class="mt-2 highlight">Details of Staff appointed under this Contractor License</h4>
<table class="staff_tbl" border="1">
    <tr>
        <th>S.No</th>
        <th>Staff Name</th>
        <th>Qualification </th>
        <th>Category </th>
        <th>Competency Certificate Number & Validity </th>
    </tr>';

if ($staffDetails->count() > 0) {
    $i = 1;
    foreach ($staffDetails as $staff) {
        $html .= '
        <tr>
            <td>' . $i++ . '</td>
            <td>' . strtoupper($staff->staff_name) . '</td>
            <td>' . strtoupper($staff->staff_qualification) . '</td>
            <td>' . strtoupper($staff->staff_category) . '</td>
            <td>' . $staff->cc_number . ', ' . (!empty($staff->cc_validity) ? date('d-m-Y', strtotime($staff->cc_validity)) : 'N/A') . '</td>

        </tr>';
    }
} else {
    $html .= '<tr><td colspan="5" style="text-align:center;">No staff found</td></tr>';
}

$html .= '</table>';

    
        
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
        $html .= '
        <br>
       
        <p><strong>Date:</strong> ' . date('d-m-Y', strtotime($applicant->issued_at)) . '</p>';
    
        // Write HTML to PDF
        $mpdf->WriteHTML($html);
    
        // Output PDF
        return response($mpdf->Output('License_A_approval.pdf', 'I'))
            ->header('Content-Type', 'application/pdf');
    }
    // ------------------------



     public function generateFormbPDF($application_id)
    {
        // Fetch application details

       
        $application = DB::table('tnelb_eb_applications')->where('application_id', $application_id)->first();

     

        $appltype = trim($application->appl_type);
      
        if($appltype === 'N'){
                $applicant = DB::table('tnelb_license')
            ->join('tnelb_eb_applications', 'tnelb_license.application_id', '=', 'tnelb_eb_applications.application_id')
            ->where('tnelb_license.application_id', $application_id)
            ->select(
                'tnelb_license.application_id',
                'tnelb_license.issued_by',
                'tnelb_license.issued_at',
                'tnelb_license.expires_at',
                'tnelb_eb_applications.applicant_name AS name',
                // 'tnelb_applicant_formA.fathers_name',
                // 'tnelb_applicant_formA.applicants_address',
                // 'tnelb_applicant_formA.d_o_b',
                // 'tnelb_applicant_formA.age',
                'tnelb_eb_applications.license_name',
                'tnelb_eb_applications.form_name',
                'tnelb_license.license_number'
            )
            ->first();

            $staffDetails = DB::table('tnelb_applicant_cl_staffdetails')
            ->where('application_id', $application_id)
            ->orderby('id')
            // ->where('staff_flag', 1)
            ->get();

        }else{
    $applicant = DB::table('tnelb_renewal_license')
            ->join('tnelb_eb_applications', 'tnelb_renewal_license.application_id', '=', 'tnelb_esb_applications.application_id')
            ->where('tnelb_renewal_license.application_id', $application_id)
            ->select(
                'tnelb_renewal_license.application_id',
                'tnelb_renewal_license.issued_by',
                'tnelb_renewal_license.issued_at',
                'tnelb_renewal_license.expires_at',
                'tnelb_esb_applications.applicant_name AS name',
                // 'tnelb_applicant_formA.fathers_name',
                // 'tnelb_applicant_formA.applicants_address',
                // 'tnelb_applicant_formA.d_o_b',
                // 'tnelb_applicant_formA.age',
                'tnelb_eb_applications.license_name',
                'tnelb_eb_applications.form_name',
                'tnelb_renewal_license.license_number'
            )
            ->first();

            $staffDetails = DB::table('tnelb_applicant_cl_staffdetails')
            ->where('application_id', $application_id)
            ->orderby('id')
            // ->where('staff_flag', 1)
            ->get();

        }
    

    
        if (!$applicant) {
            return back()->with('error', 'Application not found.');
        }
    
        
        $payment = DB::table('payments')->where('application_id', $application_id)->first();
    
        // Initialize mPDF
        $mpdf = new Mpdf(['default_font_size' => 10]);
        $mpdf->SetTitle('TNELB Application License ' . $applicant->license_name);

        
        $mpdf->WriteHTML('<style>
        body {  }
        p, td, th { padding: 0px; }
        p 
        .tbl_center { text-align: center; }
        .mt-2 { margin-top: 5; }
        table { border-collapse: collapse; width: 100%; }
        th, td {  padding: 8px; text-align:left; }
        .highlight { font-weight: bold; color: white; background-color: #423a3a; padding: 5px; text-align:center; font-size:16px; }
        .photo-container { text-align: right; padding-right: 10px; }
        .photo-container img { width: 132px; height: 170px; border: 1px solid #000; object-fit: cover; display: block; }
        .highlight_text{color:#423a3a;}
        .staff_tbl td{text-align:center;}
    </style>', \Mpdf\HTMLParserMode::HEADER_CSS);
    
        // Start building the PDF content
        $html = '
        <h3 style="text-align: center;" class="">GOVERNMENT OF TAMILNADU</h3>
        <h4 style="text-align: center;" class="">THE ELECTRICAL LICENSING BOARD</h4>
        <p style="text-align: center;">Thiru.Vi.Ka.Indl.Estate, Guindy, Chennai – 600032.</p>
        <h4 style="text-align: center;" class="highlight_text"> Form ' . $applicant->form_name . ' License "' . $applicant->license_name . '"</h4>
        <h3  style="text-align: center;"><strong>License for Contractor Certificate</strong></h3>
        <h3 style="text-align: center;" class=""> License Number : <span class = "highlight_text">' . $applicant->license_number . '</span></h3>';
    
     if($appltype === 'N') 
     {
        $apply_type= "Fresh Application";
     }else{
        $apply_type= "Renewal Application";
     }

    
        $html .= '
        <h4 class="mt-2 highlight"> License Summary</h4>
        <table>
            <tr><th class="">Applicantion ID</th><td>' . $applicant->application_id . '</td></tr>
            <tr><th class="">Name of Electrical Contractor/s <br> licence  applied for </th><td>' . $applicant->name . '</td></tr>
            <tr><th class="">License Name</th><td>' . $applicant->license_name . '</td></tr>
            <tr><th class="">License Type</th><td>' . $apply_type . '</td></tr>
            <tr><th class="">Issued By</th><td>' . $applicant->issued_by . '</td></tr>
            <tr><th class="">Issued At</th><td>' . date('d-m-Y', strtotime($applicant->issued_at)) . '</td></tr>
            <tr><th class="">Expired At</th><td>' . date('d-m-Y', strtotime($applicant->expires_at)) . '</td></tr>
        </table>';

       $html .= '
<h4 class="mt-2 highlight">Details of Staff appointed under this Contractor License</h4>
<table class="staff_tbl" border="1">
    <tr>
        <th>S.No</th>
        <th>Staff Name</th>
        <th>Qualification </th>
        <th>Category </th>
        <th>Competency Certificate Number & Validity </th>
    </tr>';

if ($staffDetails->count() > 0) {
    $i = 1;
    foreach ($staffDetails as $staff) {
        $html .= '
        <tr>
            <td>' . $i++ . '</td>
            <td>' . strtoupper($staff->staff_name) . '</td>
            <td>' . strtoupper($staff->staff_qualification) . '</td>
            <td>' . strtoupper($staff->staff_category) . '</td>
            <td>' . $staff->cc_number . ', ' . (!empty($staff->cc_validity) ? date('d-m-Y', strtotime($staff->cc_validity)) : 'N/A') . '</td>

        </tr>';
    }
} else {
    $html .= '<tr><td colspan="5" style="text-align:center;">No staff found</td></tr>';
}

$html .= '</table>';

    
        
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
        $html .= '
        <br>
       
        <p><strong>Date:</strong> ' . date('d-m-Y', strtotime($applicant->issued_at)) . '</p>';
    
        // Write HTML to PDF
        $mpdf->WriteHTML($html);
    
        // Output PDF
        return response($mpdf->Output('License_A_approval.pdf', 'I'))
            ->header('Content-Type', 'application/pdf');
    }


    public function generateFormaPDF1($application_id)
    {
         $application = DB::table('tnelb_ea_applications')
        ->where('application_id', $application_id)
        ->first();
      
       $appl_type = preg_replace('/\s+/', '', $application->appl_type);

        if ($application && $appl_type === 'R') {
            // Renewal application → use tnelb_renewal_license
            $applicant = DB::table('tnelb_ea_applications')
                ->join('tnelb_renewal_license', 'tnelb_renewal_license.application_id', '=', 'tnelb_ea_applications.application_id')
                ->where('tnelb_ea_applications.application_id', $application_id)
                ->select(
                   'tnelb_renewal_license.application_id',
                'tnelb_renewal_license.issued_by',
                'tnelb_renewal_license.issued_at',
                'tnelb_renewal_license.expires_at',
                'tnelb_ea_applications.applicant_name AS name',
                // 'tnelb_applicant_formA.fathers_name',
                // 'tnelb_applicant_formA.applicants_address',
                // 'tnelb_applicant_formA.d_o_b',
                // 'tnelb_applicant_formA.age',
                'tnelb_ea_applications.license_name',
                'tnelb_ea_applications.form_name',
                'tnelb_renewal_license.license_number'
                )
                ->first();
        } else {
        // Fetch application details
        $applicant = DB::table('tnelb_license')
            ->join('tnelb_ea_applications', 'tnelb_license.application_id', '=', 'tnelb_ea_applications.application_id')
            ->where('tnelb_license.application_id', $application_id)
            ->select(
                'tnelb_license.application_id',
                'tnelb_license.issued_by',
                'tnelb_license.issued_at',
                'tnelb_license.expires_at',
                'tnelb_ea_applications.applicant_name AS name',
                // 'tnelb_applicant_formA.fathers_name',
                // 'tnelb_applicant_formA.applicants_address',
                // 'tnelb_applicant_formA.d_o_b',
                // 'tnelb_applicant_formA.age',
                'tnelb_ea_applications.license_name',
                'tnelb_ea_applications.form_name',
                'tnelb_license.license_number'
            )
            ->first();
        }
    
        if (!$applicant) {
            return back()->with('error', 'Application not found.');
        }
    
        
        $payment = DB::table('payments')->where('application_id', $application_id)->first();
    
        // Initialize mPDF
        $mpdf = new Mpdf(['default_font_size' => 10]);
        $mpdf->SetTitle('TNELB Application License ' . $applicant->license_name);

        
        $mpdf->WriteHTML('<style>
        body { line-height: 1.5; }
        p, td, th { padding: 5px; }
        .tbl_center { text-align: center; }
        .mt-2 { margin-top: 10px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 8px; }
        .highlight { font-weight: bold; color: white; background-color: green; padding: 5px; }
        .photo-container { text-align: right; padding-right: 10px; }
        .photo-container img { width: 132px; height: 170px; border: 1px solid #000; object-fit: cover; display: block; }
    </style>', \Mpdf\HTMLParserMode::HEADER_CSS);
    
        // Start building the PDF content
        $html = '
        <h3 style="text-align: center;" class="">GOVERNMENT OF TAMILNADU</h3>
        <h4 style="text-align: center;" class="">THE ELECTRICAL LICENSING BOARD</h4>
        <p style="text-align: center;">Thiru.Vi.Ka.Indl.Estate, Guindy, Chennai – 600032.</p>
        <h4 style="text-align: center;" class=""> Form ' . $applicant->form_name . ' License "' . $applicant->license_name . '"</h4>
        <p style="text-align: center;">License for Contractor Certificate</p>
        <h3 style="text-align: center;" class="">' . $applicant->license_number . '</h3>';
    
    

    
        $html .= '
        <h4 class="mt-2 "> License Summary</h4>
        <table>
            <tr><th class="highlight">Applicant ID</th><td>' . $applicant->application_id . '</td></tr>
            <tr><th class="highlight">Name</th><td>' . $applicant->name . '</td></tr>
            <tr><th class="highlight">License Name</th><td>' . $applicant->license_name . '</td></tr>
            <tr><th class="highlight">Issued By</th><td>' . $applicant->issued_by . '</td></tr>
            <tr><th class="highlight">Issued At</th><td>' . $applicant->issued_at . '</td></tr>
            <tr><th class="highlight">Expired At</th><td>' . $applicant->expires_at . '</td></tr>
        </table>';
    
        
        $html .= '<h4 class="mt-2 "> Payment Details</h4>
        <table class="tbl_center">
            <tr>
                <th class="highlight">Bank Name</th>
                <th class="highlight">Mode of Payment</th>
                <th class="highlight">Amount</th>
                <th class="highlight">Payment Date</th>
                <th class="highlight">Transaction ID</th>
            </tr>
            <tr>
                <td>State Bank of India</td>
                <td>UPI</td>
                <td>' . ($payment->amount ?? 'N/A') . '</td>
                <td>25-02-2025</td>
                <td>' . ($payment->transaction_id ?? 'N/A') . '</td>
            </tr>
        </table>';
    
        // Declaration
        $html .= '
        <br>
        <p><strong>Place:</strong> Chennai</p>
        <p><strong>Date:</strong> ' . date('d-m-Y') . '</p>';
    
        // Write HTML to PDF
        $mpdf->WriteHTML($html);
    
        // Output PDF
        return response($mpdf->Output('Application_Details.pdf', 'I'))
            ->header('Content-Type', 'application/pdf');
    }
    

    // -------------License tamil pdf------------------------

    // -----------for all contractor license generateFormcontractor_download-------------------

    public function generateFormcontractor_download_tamil($application_id)
    {

        // dd('Tamil');
        // exit;
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

        // dd($application->license_name);
        // exit;

        $licence_id = DB::table('mst_licences')
            ->where('cert_licence_code', $application->license_name)
            ->first();

        // dd($licence_id->id);
        // exit;


        // dd($equipment_list->first()->equip_name);
        // exit;


        if (!$application) {
            return back()->with('error', 'Application ID not found.');
        }

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

            $staffDetails = DB::table('tnelb_applicant_formA_staffdetails')
                ->where('application_id', $application_id)
                ->orderBy('id')
                ->get();
        }




        // ---------------------------------------
        // 4. RETURN OR LOAD PDF VIEW
        // ---------------------------------------

        // $mpdf = new Mpdf([
        //     'format' => [210, 175],
        //     'margin_left' => 10,
        //     'margin_right' => 10,
        //     'margin_top' => 10,
        //     'margin_bottom' => 10,
        //     'default_font_size' => 10,
        //     'default_font' => 'marutham'

        // ]);
        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
              'format' => [210, 175],
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





        $mpdf->SetTitle('TNELB Application License ' . $applicant->form_name);

        $formname = $applicant->form_name;

        // dd($formname);
        // exit;

        $license_name = DB::table('mst_licences')->where('form_code', $formname)->first();


        // dd($license_name);
        // exit;

        $mpdf->WriteHTML('<style>

            .english_font{ font-family:helvetica!important;}
        body { font-weight:bold;}
        .ta_font{font-size:15px;}
        p, td, th { padding: 0px;  }
        h3 { padding: 0px; font-weight:bold; }
        .fw-bold{}
        p {}
        .tbl_center { text-align: center;!important }
        .mt-2 { margin-top: 5; }
        table { border-collapse: collapse; width: 100%; }
        th, td {  padding: 8px; text-align:left; }
        .highlight { font-weight: bold; color: white; background-color: green; padding: 5px; text-align:center; font-size:16px; }
        .photo-container { text-align: right; padding-right: 10px; }
        .photo-container img { width: 132px; height: 170px; border: 1px solid #000; object-fit: cover; display: block; }
        .highlight_text{color:green;}
        .staff_tbl td{text-align:center;}
        .license_name{ font-size:15px;text-align:center;font-weight:bold; text-decoration:underline;}
        .font-weight{font-weight:bold;}
        .blue{color:#074282;}
        .orange{color:#ec4b05;}
        .txt_uppercase{text-transform:uppercase;}
        .line-height-30{line-height:20px;}
        .text-indent-40 {text-indent: 40px;}
        .text-indent-60 {text-indent: 50px;}
        .text-justify {text-align: justify;}
        .font-size-14{ font-size:13px;}
        .mb-5{margin-bottom:5px;}
        .mb-1{margin-bottom:1px;}
        .mb-10{margin-bottom:10px;}
        .pb-10{padding: bottom 10px!important;}
        .pb-5{padding: bottom 5px!important;}
        .mt-5{margin-top:5px;}
        .mt-1{margin-top:1px;}
        .text-black{color:black;}
        
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
                        <h3 class="blue mb-5 pb-5 ta_font  fw-bold " style="font-weight:bold">தமிழ்நாடு அரசு</h3>
                        <br>

                        <h3 class="blue mb-5 pb-5 ta_font  fw-bold">மின் உரிமம் வழங்கும் வாரியம்</h3>
                        <br>

                        <p class="blue mb-5 pb-5 ta_font  fw-bold">
                        திரு.வி.க. தொழிற்பேட்டை, கிண்டி, சென்னை -600 032.
                        </p>
                        <br>

                        <p class="license_name orange mt-10">
                            ' . $grade_name_txt . '
                        </p>
                    </div>

                </td>

                <!-- RIGHT QR -->
                <td width="10%" style="text-align:right; vertical-align:top; padding-top:10px;">
                    <barcode 
                        code="' . htmlspecialchars($qrData) . '" 
                        type="QR" 
                        size="1"
                        error="H"
                    />
                </td>

        
            </tr>
        </table>';





        $html .= '
        <table style="width:100%; border:0; mt-1 mb-1">
            <tr>
                <td class="label " style="text-align:left;"><h4 class="orange ta_font  ">உரிமம் எண்: <span class="font-size-14">' . $applicant->license_number . '</span></h4></td>
                <td class="label" style="text-align:right;"><h4 class="orange  ">வழங்கிய நாள் : ' . format_date($applicant->issued_at) . '</h4></td>
            </tr>
        </table>';

        if ($grade_name == 'ESB' || $grade_name == 'EB') {
            $grade_name_txt = 'EA Grade Contractor Licence';

            $html .= '<p class="mt-1 mb-1 line-height-30 ta_font blue font-weight text-indent-40 text-justify"> திரு /திருவாளர்கள் <span class="text-black font-size-14"> ' . $applicant->name . ' (Application ID. ' . $applicant->application_id . ') </span> தமிழ்நாட்டில் உள்ள குறைந்த மற்றும் நடுத்தர மின்னழுத்த பயனீட்டாளர்களின் மின் அமைப்பு வேலைகளை 50 கி.வா (63 கே.வி.ஏ மின்னாக்கி) க்கு மிகாமல் மட்டும் மேற்கொள்ள உரிமம் வழங்கப்படுகிறது. 1956-ம் வருடத்திய இந்திய மின்விதிகளில் விதி 45(1)ன் கீழ் தமிழக அரசால் கீழ்கண்ட அரசாணைகளில் ஒப்புதலளிக்கப்பட்ட மின் உரிமம் வழங்கும் வாரியத்தின் ஒழுங்குமுறை விதிகளின்படி இந்த உரிமம் வழங்கப்படுகிறது. </p>';
        } else {

            $html .= '<p class="mt-1 mb-1 line-height-30 ta_font blue font-weight text-indent-40 text-justify"> திரு/திருவாளர்கள் <span class="text-black font-size-14"> ' . $applicant->name . ' (Application ID. ' . $applicant->application_id . ') </span> அவர்கள், தமிழ்நாட்டில் உயர் மின்னழுத்தம்,
            மத்திய மின்னழுத்தம் மற்றும் குறைந்த மின்னழுத்த மின் வேலைகளைச் செய்வதற்கு இதன்மூலம் அங்கீகரிக்கப்படுகிறார்கள். இந்த உரிமம், இந்திய மின்சார விதிகள் 1956-இன் விதி 45(1)-இன் கீழ், பின்வரும் அரசாணைகளில் தமிழ்நாடு அரசால் வெளியிடப்பட்ட விதிமுறைகளின்படி வழங்கப்படுகிறது.</p>';
        }

        $html .= '
        <p class="mt-5 mb-5 blue font-size-14 font-weight  text-indent-60 ta_font">1. அ.ஆணை எண். <span class="font-size-14">M.S.No. 1246 </span>  பொது பணித்துறை நாள் 31.3.1955 </p>
        <p class="mt-5 mb-5 blue font-weight font-size-14 text-indent-60 ta_font">2. அ.ஆணை எண். <span class="font-size-14">M.S.No. 1983</span> பொது பணித்துறை நாள் 7.10.1987 </p>
         <p class="mt-5 mb-5 blue font-weight font-size-14 text-indent-60 ta_font">3. அ.ஆணை எண். <span class="font-size-14">M.S.No. 2744 </span>பொது பணித்துறை நாள் 24.12.1990 </p>
         <p class="mt-5 mb-5 blue font-weight font-size-14 text-indent-60 ta_font">4. அ.ஆணை எண். <span class="font-size-14"> M.S.No.27 </span>எரிசக்தி <span class="font-size-14">(B.)</span> துறை நாள் 08.03.2000 </p>';

        $html .= '<p class=" mb-1 orange  font-weight  text-indent-60 ta_font ">இந்த உரிமம் செல்லுபடியாகும் காலம் :</p>';



        $proprietors = DB::table('proprietordetailsform_A')
            ->where('application_id', $application_id)
            ->where('proprietor_flag', '1')
            ->orderBy('id')
            ->get();

        $html .= '
        <table style="width:100%; border:0;" class="mt-1 mb-1">
            <tr>
                <td class="label blue txt_uppercase" style="text-align:left;">
                    <h4 class="ta_font">உரிமதாரர் / பங்குதாரர் பெயர் </h4>
                </td>
                <td> : </td>
                <td><span class="font-size-14">
            ';

        if ($proprietors->count() > 0) {
            foreach ($proprietors as $proprietor) {
                $html .= '
                
                        ' . strtoupper($proprietor->proprietor_name) . ',
                ';
            }
        } else {
            $html .= '
            —';
        }

        $html .= '</span>
        </td>
        </tr>
        <tr>
        <td class="label blue txt_uppercase" style="text-align:left;">
                    <h4 class="ta_font">அங்கீகாரம் பெற்றவர் பெயர்
மற்றும் மாதிரி ஒப்பம் </h4>  </td>
        <td> : </td>
        <td> Authorized Person Name </td>
        
        </tr>
        </table>';


        $html .= '
           <br><br>
         
            <table style="width:100%; border:0;" class="mt-1 mb-1">
                <tr>
                    <td class="label ta_font blue font-weight" style="text-align:left;">செயலாளர் </td>
                    <td class="label ta_font blue font-weight" style="text-align:right;">தலைவர்</td>
                </tr>
            </table>';



        $html .= "<pagebreak />";


        $html .= '
        <h4 class="mt-2 orange ta_font font-weight english_font">பணியாளர் விவரங்கள்</h4>
        <table style="width = 50%" class="english_font" >';

        if ($staffDetails->count() > 0) {
            $i = 1;
            foreach ($staffDetails as $staff) {

                $dates = \App\Http\Controllers\Admin\LicensepdfController::getStaffExpiryDate(
                    $staff->cc_number
                );

                $html .= '
                    <tr>
                        <td>' . $i++ . ') ' . strtoupper($staff->staff_name) . ' - ' . $staff->cc_number .  '  Valid From : ' . $dates['valid_from'] . ' Valid Upto : ' . $dates['valid_upto'] . '</td>
                      
                    </tr>';
            }
        }
        $html .= '</table>';

        $equiplist = DB::table('mst_equipment_tbls')
            ->where('equip_licence_name', $licence_id->id)
            ->orderBy('id')
            ->get();

        $equipmentlist = DB::table('equipmentforma_tbls')
            ->where('application_id', $application_id)
            ->get();

        // dd($equipmentlist->first()->licence_id);
        // exit;

        /* Map equip_id => equipment_value */
        $equipmentMap = $equipmentlist->pluck('equipment_value', 'equip_id')
            ->toArray();

        $html .= '
<h4 class="mt-2 orange font-size-14 font-weight">
    உபகரணங்கள் / கருவிகளின் விவரங்கள்
</h4>
<table class="english_font" style="width=50%">';
        $licenceId = optional($equipmentlist->first())->licence_id;
        if ($equiplist->count() > 0) {

            $i = 1;

            foreach ($equiplist as $index => $equip) {


                if ($equip->equip_licence_name == $licenceId) {

                    $equipmentValue = $equipmentMap[$equip->id] ?? 'N/A';

                    $html .= '
        <tr>
            <td>' . $i++ . ') ' . strtoupper($equip->equip_name) . '</td>
            <td>' . strtoupper($equipmentValue) . '</td>
        </tr>';
                }
            }
        } else {

            $html .= '
    <tr>
        <td colspan="2" style="text-align:center;">No equipment found</td>
    </tr>';
        }

        $html .= '</table>';




        // $html .= '
        // <h4 class="mt-2  orange font-size-14 font-weight">Equipment / Instrument</h4>
        // <table class="" >
        // ';

        //     if ($equipment_list->count() > 0) {
        //         $i = 1;
        //         foreach ($equipment_list as $equipment) {
        //             $html .= '
        //             <tr>
        //                 <td>' . $i++  .') '. strtoupper($equipment->staff_name) . '-  '   .  $staff->cc_number  .'</td>


        //             </tr>';
        //         }
        //     } else {
        //         $html .= '<tr><td colspan="5" style="text-align:center;">No staff found</td></tr>';
        //     }

        //     $html .= '</table>';


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



    
    
  public static function getStaffExpiryDate($ccNumber)
{
    // Default response (NIL)
    $default = [
        'valid_from' => 'NIL',
        'valid_upto' => 'NIL'
    ];

    if (empty($ccNumber)) {
        return $default;
    }

    // Get first character (B / H / C)
    $type = strtoupper(substr(trim($ccNumber), 0, 1));

    $tableMap = [
        'B' => 'wcert',
        'H' => 'whcert',
        'C' => 'scert'
    ];

    // Invalid certificate type
    if (!isset($tableMap[$type])) {
        return $default;
    }

    // Extract numeric part (B123 → 123)
    $certNo = preg_replace('/\D/', '', $ccNumber);

    if (empty($certNo)) {
        return $default;
    }

    /* -------------------------------------------------
     * 1️⃣ Renewal license (highest priority)
     * ------------------------------------------------- */
    $renewal = DB::table('tnelb_renewal_license')
        ->select('issued_at', 'expires_at')
        ->where('license_number', $certNo)
        ->orderBy('expires_at', 'desc')
        ->first();

    if ($renewal) {
        return [
            'valid_from' => date('d-m-Y', strtotime($renewal->issued_at)),
            'valid_upto' => date('d-m-Y', strtotime($renewal->expires_at)),
        ];
    }

    /* -------------------------------------------------
     * 2️⃣ New license
     * ------------------------------------------------- */
    $license = DB::table('tnelb_license')
        ->select('issued_at', 'expires_at')
        ->where('license_number', $certNo)
        ->orderBy('expires_at', 'desc')
        ->first();

    if ($license) {
        return [
            'valid_from' => date('d-m-Y', strtotime($license->issued_at)),
            'valid_upto' => date('d-m-Y', strtotime($license->expires_at)),
        ];
    }

    /* -------------------------------------------------
     * 3️⃣ Certificate table fallback
     * ------------------------------------------------- */
    $cert = DB::table($tableMap[$type])
        ->select('frdate1', 'vdate')
        ->where('certno', $certNo)
        ->orderBy('vdate', 'desc')
        ->first();

    if ($cert) {
        return [
            'valid_from' => date('d-m-Y', strtotime($cert->frdate1)),
            'valid_upto' => date('d-m-Y', strtotime($cert->vdate)),
        ];
    }

    // ❗ Not found anywhere → NIL
    return $default;
}


}
