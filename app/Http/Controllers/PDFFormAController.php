<?php

namespace App\Http\Controllers;

use App\Models\Admin\Mst_equipment_tbl;
use App\Models\EA_Application_model;
use App\Models\Equipment_storetmp_A;
use App\Models\ESA_Application_model;
use App\Services\Competency\CompetencyApplicationService;
use App\Models\ProprietorformA;
use App\Models\Tnelb_Addressproof_cl;
use App\Models\Tnelb_banksolvency_a;
use App\Models\TnelbApplicantStaffDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PDFFormAController extends Controller
{

public function generateaPDF($newApplicationId)
    {
        // return '111';

        $applicationId = $newApplicationId;

        // $form = EA_Application_model::where('application_id', $newApplicationId)->first() ;

        $form = DB::table('ccl_forma_meta')->where('application_id', $newApplicationId)->first()
            ?? DB::table('tnelb_esa_applications')->where('application_id', $newApplicationId)->first()
            ?? DB::table('tnelb_esb_applications')->where('application_id', $newApplicationId)->first()
            ?? DB::table('tnelb_eb_applications')->where('application_id', $newApplicationId)->first()
            ?? app(CompetencyApplicationService::class)->findApplicantWithPayment($newApplicationId);

        $education = TnelbApplicantStaffDetail::where('application_id', $newApplicationId)->orderby('id', 'ASC')->get();
        $proprietor = ProprietorformA::where('application_id', $newApplicationId)->where('proprietor_flag', '1')->get();

        $license_name = DB::table('mst_licences')->where('form_code', $form->form_name)->first();
        $banksolvency = Tnelb_banksolvency_a::where('application_id', $newApplicationId)->where('status', '1')->first();

        $Addressproof = Tnelb_Addressproof_cl::where('application_id', $newApplicationId)->first();

        // $equipmentlist = Equipment_storetmp_A::where('application_id', $newApplicationId)->first();
        // $documents = Mst_documents::where('application_id', $newApplicationId)->first();
        $payment = DB::table('payments')->where('application_id', $newApplicationId)->first();


        if (!$form) {
            return redirect()->back()->with('error', 'No records found!');
        }


        try {
             $application = DB::table('ccl_forma_meta')->where('application_id', $newApplicationId)->first()
            ?? DB::table('tnelb_esa_applications')->where('application_id', $newApplicationId)->first()
            ?? DB::table('tnelb_esb_applications')->where('application_id', $newApplicationId)->first()
            ?? DB::table('tnelb_eb_applications')->where('application_id', $newApplicationId)->first()
            ?? app(CompetencyApplicationService::class)->findApplicantWithPayment($newApplicationId);

            // dd($application); exit;

                 $form = DB::table('mst_licences')
                ->where('form_code', $application->form_name)
                // ->where('status', '1')
                ->first();

                // dd($form->licence_name); exit;

                  $licenceName = $form->licence_name;



                $education = TnelbApplicantStaffDetail::where('application_id', $newApplicationId)->orderby('id', 'ASC')->get();
                $proprietor = ProprietorformA::where('application_id', $newApplicationId)->where('proprietor_flag', '1')->get();

                $license_name = DB::table('mst_licences')->where('form_code', $form->form_name)->first();
                $banksolvency = Tnelb_banksolvency_a::where('application_id', $newApplicationId)->where('status', '1')->first();

                $Addressproof = Tnelb_Addressproof_cl::where('application_id', $newApplicationId)->first();

                // $equipmentlist = Equipment_storetmp_A::where('application_id', $newApplicationId)->first();
                // $documents = Mst_documents::where('application_id', $newApplicationId)->first();
                $payment = DB::table('payments')->where('application_id', $newApplicationId)->first();

                // dd($payment); exit;

            $applType = strtoupper(trim((string) ($application->appl_type ?? '')));
            $typeOfForm = match ($applType) {
                'N' => 'New',
                'R' => 'Renewal',
                'A' => 'Alteration',
                'D' => 'Digitization',
                default => 'Application',
            };

            $paymentType = mb_strtoupper((string) ($payment->payment_mode ?? 'ONLINE'), 'UTF-8');
            $transactionNo = mb_strtoupper((string) ($payment->transaction_id ?? 'N/A'), 'UTF-8');
            $paymentDateRaw = $payment->created_at ?? null;
            $paymentDate = $paymentDateRaw
                ? \Carbon\Carbon::parse($paymentDateRaw)->format('d-m-Y')
                : 'N/A';
            $amountValue = $payment->amount ?? 'Nil';
            $statusValue = mb_strtoupper((string) ($payment->payment_status ?? 'N/A'), 'UTF-8');
            $applicantName = $application->applicant_name ?? 'N/A';
            $formName = strtoupper(trim((string) ($application->form_name ?? $application->form_name ?? '')));
          

            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'default_font' => 'helvetica',
                'margin_top' => 12,
                'margin_bottom' => 12,
                'margin_left' => 12,
                'margin_right' => 12,
            ]);
            $mpdf->SetTitle('TNELB Payment Receipt ' . $applicationId);

            $html = '
            <style>
                .no_space { margin: 3px; line-height: 1.3; }
                .table-border { border-collapse: collapse; width: 100%; margin-top: 10px; }
                .table-border td, .table-border th {
                    padding: 6px;
                    font-size: 14px;
                    text-align: left;
                    border-bottom: 1px solid #e5e5e5;
                }
                .table-border th { width: 40%; }
            </style>
            <div style="text-align:center;">
                <h3 class="no_space" style="font-size:18px;">GOVERNMENT OF TAMIL NADU</h3>
                <h4 class="no_space" style="font-size:16px;">THE ELECTRICAL LICENSING BOARD</h4>
                <p class="no_space" style="font-size:14px;">Thiru.Vi.Ka. Industrial Estate, Guindy, Chennai – 600 032.</p>
            </div>
            <hr style="margin:10px 0; border:0; border-top:1px solid #000;">
            <p style="font-size:15px; font-weight:bold; margin-top:5px; text-align:center;">Payment Acknowledgement Receipt</p>
            <table class="table-border">
                <tr>
                    <th>Application ID</th>
                    <td>: ' . e($applicationId) . '</td>
                </tr>
                <tr>
                    <th>Applicant Name</th>
                    <td>: ' . e($applicantName) . '</td>
                </tr>
                <tr>
                    <th>Certificate</th>
                    <td>: ' . e($licenceName) . '</td>
                </tr>
                <tr>
                    <th>Form</th>
                    <td>: ' . e($formName) . '</td>
                </tr>
                <tr>
                    <th>Type of Form</th>
                    <td>: ' . e($typeOfForm) . '</td>
                </tr>
                <tr>
                    <th>Payment Type</th>
                    <td>: ' . e($paymentType) . '</td>
                </tr>
                <tr>
                    <th>Payment Date</th>
                    <td>: ' . e($paymentDate) . '</td>
                </tr>
                <tr>
                    <th>Transaction Number</th>
                    <td>: ' . e($transactionNo) . '</td>
                </tr>
                <tr>
                    <th>Amount</th>
                    <td>: &#8377; ' . e((string) $amountValue) . '</td>
                </tr>
                <tr>
                    <th>Payment Status</th>
                    <td>: ' . e($statusValue) . '</td>
                </tr>
            </table>';

            $mpdf->WriteHTML($html);

            return response($mpdf->Output('payment_receipt_cl.pdf', 'S'), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="payment_receipt.pdf"');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Failed to generate payment details: ' . $e->getMessage());
        }


       
        
    }

     public function paymentDetails_cl($newApplicationId)
    {
        // return '111';

        $applicationId = $newApplicationId;

        // $form = EA_Application_model::where('application_id', $newApplicationId)->first() ;

        $form = DB::table('ccl_forma_meta')->where('application_id', $newApplicationId)->first()
            ?? DB::table('tnelb_esa_applications')->where('application_id', $newApplicationId)->first()
            ?? DB::table('tnelb_esb_applications')->where('application_id', $newApplicationId)->first()
            ?? DB::table('tnelb_eb_applications')->where('application_id', $newApplicationId)->first()
            ?? app(CompetencyApplicationService::class)->findApplicantWithPayment($newApplicationId);

        $education = TnelbApplicantStaffDetail::where('application_id', $newApplicationId)->orderby('id', 'ASC')->get();
        $proprietor = ProprietorformA::where('application_id', $newApplicationId)->where('proprietor_flag', '1')->get();

        $license_name = DB::table('mst_licences')->where('form_code', $form->form_name)->first();
        $banksolvency = Tnelb_banksolvency_a::where('application_id', $newApplicationId)->where('status', '1')->first();

        $Addressproof = Tnelb_Addressproof_cl::where('application_id', $newApplicationId)->first();

        // $equipmentlist = Equipment_storetmp_A::where('application_id', $newApplicationId)->first();
        // $documents = Mst_documents::where('application_id', $newApplicationId)->first();
        $payment = DB::table('payments')->where('application_id', $newApplicationId)->first();


        if (!$form) {
            return redirect()->back()->with('error', 'No records found!');
        }


        try {
             $application = DB::table('ccl_forma_meta')->where('application_id', $newApplicationId)->first()
            ?? DB::table('tnelb_esa_applications')->where('application_id', $newApplicationId)->first()
            ?? DB::table('tnelb_esb_applications')->where('application_id', $newApplicationId)->first()
            ?? DB::table('tnelb_eb_applications')->where('application_id', $newApplicationId)->first()
            ?? app(CompetencyApplicationService::class)->findApplicantWithPayment($newApplicationId);

            // dd($application); exit;

                 $form = DB::table('mst_licences')
                ->where('form_code', $application->form_name)
                // ->where('status', '1')
                ->first();

                // dd($form->licence_name); exit;

                  $licenceName = $form->licence_name;



                $education = TnelbApplicantStaffDetail::where('application_id', $newApplicationId)->orderby('id', 'ASC')->get();
                $proprietor = ProprietorformA::where('application_id', $newApplicationId)->where('proprietor_flag', '1')->get();

                $license_name = DB::table('mst_licences')->where('form_code', $form->form_name)->first();
                $banksolvency = Tnelb_banksolvency_a::where('application_id', $newApplicationId)->where('status', '1')->first();

                $Addressproof = Tnelb_Addressproof_cl::where('application_id', $newApplicationId)->first();

                // $equipmentlist = Equipment_storetmp_A::where('application_id', $newApplicationId)->first();
                // $documents = Mst_documents::where('application_id', $newApplicationId)->first();
                $payment = DB::table('payments')->where('application_id', $newApplicationId)->first();

                // dd($payment); exit;

            $applType = strtoupper(trim((string) ($application->appl_type ?? '')));
            $typeOfForm = match ($applType) {
               'N' => 'New',
                'R' => 'Renewal',
                'A' => 'Alteration',
                'D' => 'Digitization',
                default => 'Application',
            };

            $paymentType = mb_strtoupper((string) ($payment->payment_mode ?? 'ONLINE'), 'UTF-8');
            $transactionNo = mb_strtoupper((string) ($payment->transaction_id ?? 'N/A'), 'UTF-8');
            $paymentDateRaw = $payment->created_at ?? null;
            $paymentDate = $paymentDateRaw
                ? \Carbon\Carbon::parse($paymentDateRaw)->format('d-m-Y')
                : 'N/A';
            $amountValue = $payment->amount ?? 'Nil';
            $statusValue = mb_strtoupper((string) ($payment->payment_status ?? 'N/A'), 'UTF-8');
            $applicantName = $application->applicant_name ?? 'N/A';
            $formName = strtoupper(trim((string) ($application->form_name ?? $application->form_name ?? '')));
          

            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'default_font' => 'helvetica',
                'margin_top' => 12,
                'margin_bottom' => 12,
                'margin_left' => 12,
                'margin_right' => 12,
            ]);
            $mpdf->SetTitle('TNELB Payment Receipt ' . $applicationId);

            $html = '
            <style>
                .no_space { margin: 3px; line-height: 1.3; }
                .table-border { border-collapse: collapse; width: 100%; margin-top: 10px; }
                .table-border td, .table-border th {
                    padding: 6px;
                    font-size: 14px;
                    text-align: left;
                    border-bottom: 1px solid #e5e5e5;
                }
                .table-border th { width: 40%; }
            </style>
            <div style="text-align:center;">
                <h3 class="no_space" style="font-size:18px;">GOVERNMENT OF TAMIL NADU</h3>
                <h4 class="no_space" style="font-size:16px;">THE ELECTRICAL LICENSING BOARD</h4>
                <p class="no_space" style="font-size:14px;">Thiru.Vi.Ka. Industrial Estate, Guindy, Chennai – 600 032.</p>
            </div>
            <hr style="margin:10px 0; border:0; border-top:1px solid #000;">
            <p style="font-size:15px; font-weight:bold; margin-top:5px; text-align:center;">Payment Acknowledgement Receipt</p>
            <table class="table-border">
                <tr>
                    <th>Application ID</th>
                    <td>: ' . e($applicationId) . '</td>
                </tr>
                <tr>
                    <th>Applicant Name</th>
                    <td>: ' . e($applicantName) . '</td>
                </tr>
                <tr>
                    <th>Certificate</th>
                    <td>: ' . e($licenceName) . '</td>
                </tr>
                <tr>
                    <th>Form</th>
                    <td>: ' . e($formName) . '</td>
                </tr>
                <tr>
                    <th>Type of Form</th>
                    <td>: ' . e($typeOfForm) . '</td>
                </tr>
                <tr>
                    <th>Payment Type</th>
                    <td>: ' . e($paymentType) . '</td>
                </tr>
                <tr>
                    <th>Payment Date</th>
                    <td>: ' . e($paymentDate) . '</td>
                </tr>
                <tr>
                    <th>Transaction Number</th>
                    <td>: ' . e($transactionNo) . '</td>
                </tr>
                <tr>
                    <th>Amount</th>
                    <td>: &#8377; ' . e((string) $amountValue) . '</td>
                </tr>
                <tr>
                    <th>Payment Status</th>
                    <td>: ' . e($statusValue) . '</td>
                </tr>
            </table>';

            $mpdf->WriteHTML($html);

            return response($mpdf->Output('payment_receipt_cl.pdf', 'S'), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="payment_receipt.pdf"');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Failed to generate payment details: ' . $e->getMessage());
        }


       
        
    }


    public function generatesaPDF($newApplicationId)
    {


        // Fetch form details
        $form = ESA_Application_model::where('application_id', $newApplicationId)->first();
        $education = TnelbApplicantStaffDetail::where('application_id', $newApplicationId)->orderby('id', 'ASC')->get();
        $proprietor = ProprietorformA::where('application_id', $newApplicationId)->where('proprietor_flag', '1')->get();

        $license_name = DB::table('mst_licences')->where('form_code', $form->form_name)->first();

        $banksolvency = Tnelb_banksolvency_a::where('application_id', $newApplicationId)->where('status', '1')->first();

        
        // $documents = Mst_documents::where('application_id', $newApplicationId)->first();
        // $payment = DB::table('payments')->where('application_id', $newApplicationId)->first();

        if (!$form) {
            return redirect()->back()->with('error', 'No records found!');
        }



        // Initialize mPDF
        // $mpdf = new Mpdf();
        $mpdf = new Mpdf(['default_font_size' => 10]);
        $mpdf->SetFont('helvetica', '', 10);
        $mpdf->WriteHTML('<style> 
            body { line-height: 0.8; } 
            p, td, th { line-height: 2.0; padding: 2px; }
             td, th { line-height: 2.0; padding: 2px; }
             th{font-size:13px;}
             h3, h4, p {
            margin: 2px 0; /* Reduces top & bottom margin */
            line-height: 1.5; /* Adjusts spacing between lines */
        }
            .tbl_center tr td{
            text-align:center;
            }
            .mt-2{

            margin-top:10px;
        }
        </style>', \Mpdf\HTMLParserMode::HEADER_CSS);


        $mpdf->SetTitle('TNELB Application License ' . $form->license_name . ' Form ' . $form->form_name);

        // Application Title
        $html = '
    <h3 style="text-align: center;">GOVERNMENT OF TAMILNADU</h3>
    <h4 style="text-align: center;">THE ELECTRICAL LICENSING BOARD</h4>
    <p style="text-align: center;">Thiru.Vi.Ka.Indl.Estate, Guindy, Chennai – 600032.</p>
';

        $appl_type = trim($form->appl_type);




        if ($appl_type === 'R') {

            $html .= '<h4 style="text-align: center;">Form "' . $form->form_name . '" Renewal  Application</h4>';
        } else {
            $html .= '<h4 style="text-align: center;">Form "' . $form->form_name . '" New Application</h4>';
        }

        // dd($form->license_name);
        // exit;
        $html .= '
         <p style="text-align: center;"> Application For "' . $license_name->licence_name . '"</p>
         <p style="text-align: center;"><strong>Application ID : ' . $newApplicationId . '</strong></p>';

        $ownership = '';

        if ($form->application_ownershiptype == 'pr') {
            $ownership = 'Proprietorship';
        } elseif ($form->application_ownershiptype == 'pt') {
            $ownership = 'Partnership';
        } elseif ($form->application_ownershiptype == 'pvt') {
            $ownership = 'Private Limited (Pvt LTD)';
        } elseif ($form->application_ownershiptype == 'ltd') {
            $ownership = 'Limited (LTD)';
        } else {
            $ownership = '-';
        }

        // Applicant Details
        $html .= '
        <table width="80%"  style="border-collapse: collapse;">
            <tr>
                <td ></td>
                <td ></td>
                <td >
    ';


        $html .= '
                </td>
            </tr>
            <tr>
                
                <td><strong>1.Name of the Electrical Contractor/s licence applied</strong> </td>
                <td>' . $form->applicant_name . '</td>
            </tr>
            <tr>
                
                <td><strong>2.Business address</strong> </td>
                <td> ' . $form->business_address . '</td>
            </tr>
         
        </table>';

        $html .= '<h4 class="mt-2">3 & 4) Ownership Type - Proprietor / Partners / Directors Details </h4>

          <table width="100%"  style="border-collapse: collapse;">
            <tr>
                
                <td><strong>Type of Ownership</strong> </td>
                <td>' . $ownership . '</td>
            </tr>
           
         
        </table>

        <table border="1" width="100%" cellspacing="0" cellpadding="5" class="text-center tbl_center" >
        <tr>
            <th>Ownership Type </th>
            <th >Name and Address</th>
            <th>Age and Qualifications</th>
            <th>Father/s Husband/s Name</th>
            <th>Present business of the applicant</th>
            
            <th>Competency Certificate and Validity</th>
            <th>Presently Employed and Address</th>

            <th>If holding a competency certificate - Contractor Details </th>
            
        </tr>';

        if ($proprietor->isNotEmpty()) {
            $sortedProprietors = collect($proprietor)
                ->sortBy(function ($item) {
                    return match ($item->ownership_type) {
                        'pr' => 1,
                        'pt' => 2,
                        'dr' => 3,
                        default => 4,
                    };
                });

            foreach ($sortedProprietors as $pro) {
                $c_validity = $pro->competency_certificate_validity
                    ? date('d-m-Y', strtotime($pro->competency_certificate_validity))
                    : '';

                $previous_experience_lnumber_validity = $pro->previous_experience_lnumber_validity
                    ? date('d-m-Y', strtotime($pro->previous_experience_lnumber_validity))
                    : '';

                if ($pro->ownership_type === 'pr') {
                    $ownership_type = 'Proprietor';
                } elseif ($pro->ownership_type === 'pt') {
                    $ownership_type = 'Partner';
                } else {
                    $ownership_type = 'Director';
                }
                $html .= '<tr>
                    <td>' . $ownership_type . '</td>
                    <td>' . $pro->proprietor_name . ', ' . $pro->proprietor_address . '</td>
                    <td>' . $pro->age . ' ' . $pro->qualification . '</td>
                    <td>' . $pro->fathers_name . '</td>
                    <td>' . $pro->present_business . '</td>
                    <td>' . $pro->competency_certificate_number . '<br>'  . $c_validity . '</td>
                    <td>' . $pro->presently_employed_name . '- ' . $pro->presently_employed_address . '</td>
                    <td>' . $pro->previous_experience_name . '- ' . $pro->previous_experience_address .  '- ' . $pro->previous_experience_lnumber . '- ' . $previous_experience_lnumber_validity . '</td>
                </tr>';
            }
        } else {
            $html .= '<tr><td colspan="6" class="text-center">No proprietor records found</td></tr>';
        }

        $html .= '</table>'; //  Close Proprietor Table Properly

        $html .= '
    <table> 
        <tr>
            <td width="80%">
                <h4>5. Name and designation of authorised signatory (if any, in the case of a limited company)</h4>
            </td>
            <td width="20%">' . strtoupper($form->authorised_name_designation) . '</td>
        </tr>
    </table>';

        if ($form->authorised_name_designation === 'yes') {
            $html .= '
        <table width="50%" cellspacing="0" cellpadding="3" class="">
            <tr>
                <td></td>
                <td>Name:</td>
                <td>' . strtoupper($form->authorised_name) . '</td>
            </tr>
            <tr>
                <td></td>
                <td>Designation:</td>
                <td>' . strtoupper($form->authorised_designation) . '</td>
            </tr>
        </table>';
        }

        $html .= '
    <table> 
        <tr>
            <td width="80%">
                <h4>6. Whether any application for
                    Contractor/s licence was made
                    previously? If so, details thereof</h4>
            </td>
            <td width="20%">' . strtoupper($form->previous_contractor_license) . '</td>
        </tr>
    </table>';

        if ($form->previous_contractor_license === 'yes') {
            $formattedValidity = '';
            if (!empty($form->previous_application_validity)) {
                $formattedValidity = \Carbon\Carbon::parse($form->previous_application_validity)->format('d-m-Y');
            }

            $html .= '
        <table width="50%" cellspacing="0" cellpadding="3" class="">
            <tr>
                <td></td>
                <td>Previous License Number</td>
                <td>' . strtoupper($form->previous_application_number) . '</td>
            </tr>
            <tr>
                <td></td>
                <td>Previous License Validity</td>
                <td>' . $formattedValidity . '</td>
            </tr>
        </table>';
        }


        // $html .= "<pagebreak />";

        $html .= '<h4 class="mt-2">7. Staff Details</h4>
        <table border="1" width="100%" cellspacing="0" cellpadding="5" class="text-center tbl_center">
            <tr>
                <th>S.No</th>
                <th>Staff Name</th>
                <th>Staff Qualification</th>
                <th>Staff Category</th>
                <th>Competency Certificate Number</th>
                <th>Competency Certificate Validity</th>
                
            </tr>';

        $i = 1;
        foreach ($education as $edu) {
            $cc_validity = $edu->cc_validity
                ? date('d-m-Y', strtotime($edu->cc_validity))
                : '';
            $html .= '<tr >
                <td class="text-center">' . $i . '</td>
                <td class="text-center">' . $edu->staff_name . '</td>
                <td class="text-center">' . $edu->staff_qualification . '</td>
                
                <td class="text-center">' . $edu->staff_category . '</td>
                <td class="text-center">' . $edu->cc_number . '</td>
                <td class="text-center">' . $cc_validity . '</td>
                
            </tr>';
            $i++;
        }
        $html .= '</table>';

        $bank_validity = $banksolvency->bank_validity
            ? date('d-m-Y', strtotime($banksolvency->bank_validity))
            : '';

        $html .= '
        <h4 class="mt-2">8. Bank Solvency Certificate Details</h4>
            <table  width="50%" cellspacing="0" cellpadding="3" class="">
               

                 <tr>
                   <td></td>
                    <td>Name of the Bank and Address</td>
                    <td>' . $banksolvency->bank_address . '</td>
                </tr>

                  <tr>
                   <td></td>
                    <td>Validity Period</td>
                    <td>' . $bank_validity . '</td>
                </tr>

                  <tr>
                   <td></td>
                    <td>Amount Rs</td>
                    <td>' . $banksolvency->bank_amount . '</td>
                </tr>

                
            </table> <br><br>';






        $html .= '
           
                <table  width="80%" cellspacing="0" cellpadding="3" class="">
                   
    
                     <tr>
                       <td><strong>9. (i). Whether consent letter, of the competency certificate holder are enclosed. (including for self)</strong></td>
                        
                        <td>' . strtoupper($form->consent_letter_enclose) . '</td>
                    </tr>
    
                       <tr>
                       <td><strong>(ii). Whether original booklet of competency certificate holders are enclosed? (including for self)</strong></td>
                        
                        <td>' . strtoupper($form->cc_holders_enclosed) . '</td>
                    </tr>
                     
    
                    
                </table> ';

        $html .= '
           
                <table  width="80%" cellspacing="0" cellpadding="3" class="">
                   
    
                     <tr>
                       <td><strong>10. (i). Whether purchase bill for all the
                                        instruments are enclosed in
                                        Original</strong></td>
                        
                        <td>' . strtoupper($form->purchase_bill_enclose) . '</td>
                    </tr>
    
                       <tr>
                       <td><strong>(ii). Whether the test reports for
                                    instruments and deeds for possess
                                    of the instruments are enclosed in
                                    original</strong></td>
                        
                        <td>' . strtoupper($form->test_reports_enclose) . '</td>
                    </tr>
                     
    
                    
                </table> ';









        $html .= '           
                    <table  width="80%" cellspacing="0" cellpadding="3" class="">
                    
        
                        <tr>
                        <td><strong> 11 (i). Whether specimen signature of
                            the Proprietor or of the authorised
                            signatory (in case of limited
                            company in triplicate is enclosed) </strong></td>
                            
                            <td>' . strtoupper($form->specimen_signature_enclose) . '</td>
                        </tr>
        
                        
        
                        
                    </table> ';
        $name_of_authorised_to_sign = $form->name_of_authorised_to_sign;
        $age_of_authorised_to_sign = $form->age_of_authorised_to_sign;
        $qualification_of_authorised_to_sign = $form->qualification_of_authorised_to_sign;

        // Decode JSON if valid, otherwise convert from comma-separated string
        if (is_string($name_of_authorised_to_sign) && $decodedNames = json_decode($name_of_authorised_to_sign, true)) {
            $decodedAges = json_decode($age_of_authorised_to_sign, true);
            $decodedQualifications = json_decode($qualification_of_authorised_to_sign, true);

            $name_of_authorised_to_sign = $decodedNames;
            $age_of_authorised_to_sign = $decodedAges;
            $qualification_of_authorised_to_sign = $decodedQualifications;
        } else {
            $name_of_authorised_to_sign = explode(',', $name_of_authorised_to_sign);
            $age_of_authorised_to_sign = explode(',', $age_of_authorised_to_sign);
            $qualification_of_authorised_to_sign = explode(',', $qualification_of_authorised_to_sign);
        }

        $html .= '
        
        <p> <strong>(ii). The name of the person(s) whom the applicant has authorized to sign, if any, on his/their behalf in case of Proprietor or Partnership concern</strong></p>
      <table border="1" width="100%" cellspacing="0" cellpadding="5" class="text-center tbl_center">
      
            <tr>
            <th>Name of Signatory </th>
            <th>Age of Signatory </th>
            <th>Qualification of Signatory </th>
            </tr>
          
               ';

        if (!empty($name_of_authorised_to_sign) && is_array($name_of_authorised_to_sign)) {
            foreach ($name_of_authorised_to_sign as $index => $name) {
                $nameVal = strtoupper(trim(is_array($name) ? ($name['name'] ?? '') : $name));
                $ageVal = isset($age_of_authorised_to_sign[$index]) ? trim($age_of_authorised_to_sign[$index]) : '-';
                $qualVal = isset($qualification_of_authorised_to_sign[$index]) ? strtoupper(trim($qualification_of_authorised_to_sign[$index])) : '-';

                $html .= '
        <tr>
            <td>' . $nameVal . '</td>
            <td>' . $ageVal . '</td>
            <td>' . $qualVal . '</td>
        </tr>';
            }
        } else {
            $html .= '
    <tr>
        <td colspan="3">-</td>
    </tr>';
        }

        $html .=    '
        </table>';



        $html .= '
           
                    <table  width="80%" cellspacing="0" cellpadding="3" class="">
                       
        
                         <tr>
                           <td><strong> (iii). Whether the applicant enclosed
                                the specimen signature of the
                                above person/ persons in triplicate
                                in a separate sheet of paper </strong></td>
                            
                            <td>' . strtoupper($form->separate_sheet) . '</td>
                        </tr>
        
                         
        
                        
                    </table> ';


        try {
            $decryptedaadhaar = Crypt::decryptString($form->aadhaar);
            $maskaadhaar = str_repeat('X', strlen($decryptedaadhaar) - 4) . substr($decryptedaadhaar, -4);

            $decryptedpancard = Crypt::decryptString($form->pancard);
            $maskpancard = str_repeat('X', strlen($decryptedpancard) - 4) . substr($decryptedpancard, -4);

            $decryptedgst_number = Crypt::decryptString($form->gst_number);
            $maskgst_number = str_repeat('X', strlen($decryptedgst_number) - 4) . substr($decryptedgst_number, -4);
        } catch (\Exception $e) {
            $maskaadhaar = 'Invalid or corrupted AAdhaar';
            $maskpancard = 'Invalid or corrupted Pancard';
            $maskgst_number = 'Invalid or corrupted GST Number';
        }

        $html .= '
    <table width="80%" cellspacing="0" cellpadding="3" style="text-align:left;">
        <tr>
            <td>12.</td>
            <td><strong>i) Aadhaar Number</strong></td>
            <td style="text-align:right;">' . strtoupper($maskaadhaar) . '</td>
        </tr>
        <tr>
            <td></td>
            <td><strong>ii) PAN Card Number</strong></td>
            <td style="text-align:right;">' . strtoupper($maskpancard) . '</td>
        </tr>
        <tr>
            <td></td>
            <td><strong>iii) GST Number</strong></td>
            <td style="text-align:right;">' . strtoupper($maskgst_number) . '</td>
        </tr>
    </table>';

          $equiplist = Mst_equipment_tbl::where('equip_licence_name', 7)
            ->where('status', 1)
            ->orderBy('id')
            ->get();

        $equipmentlist = DB::table('equipmentforma_tbls')
            ->where('login_id', Auth::user()->login_id)
            ->where('application_id', $newApplicationId)
            ->get();

        /*
            Create map:
            equip_id => equipment_value
            */
        $equipmentMap = collect($equipmentlist)
            ->pluck('equipment_value', 'equip_id')
            ->toArray();

        $html .= '<h4>13) Equipment / Instruments List</h4>';

        $html .= '
<table border="1" cellpadding="4" cellspacing="0" width="100%">
    <thead>
        <tr style="font-weight:bold; background-color:#f2f2f2;">
            <th width="6%">S.No</th>
            <th width="44%">Equipment Name</th>
            <th width="25%">Equipment Type</th>
            <th width="25%" align="center">Availability</th>
        </tr>
    </thead>
    <tbody>';

        $slno = 1;

        foreach ($equiplist as $equip) {

            // YES / NO (default NO)
            $value = strtoupper($equipmentMap[$equip->id] ?? 'no');

            $html .= '
        <tr>
            <td width="6%" align="center">' . $slno . '</td>
            <td width="44%" align="center">' . $equip->equip_name . '</td>
            <td width="25%" align="center">' . $equip->equipment_type . '</td>
            <td width="25%" align="center"><strong>' . $value . '</strong></td>
        </tr>';

            $slno++;
        }

        $html .= '
    </tbody>
</table>';


        // if ($equipmentlist->instrument3_yes === 'yes') {
        //     $formattedValidity = '';
        //     if (!empty($equipmentlist->validity_date_eq3)) {
        //         $formattedValidity = \Carbon\Carbon::parse($equipmentlist->validity_date_eq3)->format('d-m-Y');
        //     }

        //     $html .= '
        // <table width="50%" cellspacing="0" cellpadding="3" class="">
        //     <tr>
        //         <td></td>
        //         <td>Branch Office Details:</td>
        //         <td>' . strtoupper($equipmentlist->instrument3_id) . '</td>
        //     </tr>

        // </table>';
        // }



        // Declaration
        $html .= '
        <p class="mt-2">I/We hereby declare that the particulars stated above are correct to the best of my/our
            knowledge and belief.</p>
        <p class="mt-2">I/We hereby declare that I/We have in my/our possession a latest copy of the Indian
            Electricity Rules,
            1956 and that I/We fully understand the terms and conditions under which an Electrical
            Contractor/s licence is granted, breach of which will render the licence liable for cancellation.</p>
        <br>
        <br>
     
    
        <p><strong>Place:</strong> </p>


        <p><strong>Date:</strong> ' . Carbon::parse($form->updated_at)->format('d-m-Y') . '</p>

      
        
        <p style="text-align: right;"><strong>Signature of the Candidate</strong></p>';



        // Write HTML to PDF
        $mpdf->WriteHTML($html);

        // Output PDF
        return response($mpdf->Output('Application_Details_Form_A' . $newApplicationId . '.pdf', 'I'))
            ->header('Content-Type', 'application/pdf');
    }
}
