<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mst_Form_s_w;
use App\Models\Mst_education;
use App\Models\Mst_experience;
use App\Models\Mst_documents;
use App\Models\TnelbApplicantPhoto;
use App\Models\TnelbFormP;
use App\Models\TnelbAppsInstitute;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use TCPDF;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use Stichoza\GoogleTranslate\GoogleTranslate;

class PDFController extends Controller
{

    public function formatAddressToThreeLines($address, $maxCharsPerLine = 45) {
       $address = preg_replace("/\r\n|\r|\n/", ' ', $address);
        $address = preg_replace('/\s+/', ' ', $address);
        $address = trim($address);

        $words = explode(' ', $address);

        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            if (strlen($currentLine . ' ' . $word) <= $maxCharsPerLine) {
                $currentLine .= ($currentLine ? ' ' : '') . $word;
            } else {
                $lines[] = $currentLine;
                $currentLine = $word;
            }
        }

        if ($currentLine) {
            $lines[] = $currentLine;
        }

        // Ensure max 3 lines
        $lines = array_slice($lines, 0, 3);

        return nl2br(e($lines[0] ?? '') . "\n" . e($lines[1] ?? '') . "\n" . e($lines[2] ?? ''));
    }

    private function safeDecryptString($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function generateFormPPDF($newApplicationId)
    {
        $form = TnelbFormP::where('application_id', $newApplicationId)->first();
        $education = Mst_education::where('application_id', $newApplicationId)->get();
        $experience = Mst_experience::where('application_id', $newApplicationId)->get();
        $institutes = TnelbAppsInstitute::where('application_id', $newApplicationId)->get();
        $applicant_photo = TnelbApplicantPhoto::where('application_id', $newApplicationId)->first();
        $payment = DB::table('payments')->where('application_id', $newApplicationId)->first();

        if (!$form) {
            return redirect()->back()->with('error', 'No records found!');
        }

        $decryptedaadhar = $this->safeDecryptString($form->aadhaar);
        $decryptedaadhar = $decryptedaadhar ? preg_replace('/\s+/', '', $decryptedaadhar) : '';
        $masked = strlen($decryptedaadhar) === 12 ? str_repeat('X', 8) . substr($decryptedaadhar, -4) : 'Invalid Aadhaar';
        $decryptedPan = $this->safeDecryptString($form->pancard);
        $decryptedPan = $decryptedPan ? strtoupper(preg_replace('/[^A-Z0-9]/i', '', $decryptedPan)) : '';
        $maskedPan = strlen($decryptedPan) === 10 ? str_repeat('X', 6) . substr($decryptedPan, -4) : '';
        $decryptedPan = $this->safeDecryptString($form->pancard);
        $decryptedPan = $decryptedPan ? strtoupper(preg_replace('/[^A-Z0-9]/i', '', $decryptedPan)) : '';
        $maskedPan = strlen($decryptedPan) === 10 ? str_repeat('X', 6) . substr($decryptedPan, -4) : '';
        $maskedUpper = mb_strtoupper($masked, 'UTF-8');

        // Match generatePDF(): A4, helvetica 10pt, acknowledgement-style layout
        $applicantNameUpper = mb_strtoupper($form->applicant_name ?? '', 'UTF-8');
        $fatherNameUpper = mb_strtoupper($form->fathers_name ?? '', 'UTF-8');
        $addressRaw = $form->applicants_address ?? '';
        $addressUpper = mb_strtoupper($addressRaw, 'UTF-8');
        $dobDisplay = trim(($form->d_o_b ?? '') . ' (' . ($form->age ?? '') . ' YEARS)');
        $dobDisplay = mb_strtoupper($dobDisplay, 'UTF-8');
        $appIdUpper = mb_strtoupper($form->application_id ?? '', 'UTF-8');
        $formNameUpper = mb_strtoupper($form->form_name ?? 'P', 'UTF-8');
        $applTypeCode = strtoupper(trim($form->appl_type ?? 'N'));

        $certificateText = match ($form->form_name) {
            'P' => 'Application for Power Generating Station Operation & Maintenance Competency Certificate',
            'S' => 'Application for Competency Certificate for Supervisor',
            'W' => 'Application for Competency Certificate for Wireman',
            'WH' => 'Application for Competency Certificate for Wireman Helper',
            default => 'Application for Power Generating Station Operation & Maintenance Competency Certificate',
        };
        $certificateTextUpper = mb_strtoupper($certificateText, 'UTF-8');

        if (empty($form->previously_number) || empty($form->previously_date)) {
            $prevAppValue = 'NO';
        } else {
            $prevAppValue = 'YES, ' . mb_strtoupper((string) ($form->previously_number ?? ''), 'UTF-8')
                . ', ' . mb_strtoupper(format_date($form->previously_date) ?? '', 'UTF-8');
        }

        $employerUpper = mb_strtoupper(trim($form->employer_detail ?? '') !== '' ? trim($form->employer_detail) : 'NIL', 'UTF-8');

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font_size' => 10,
            'default_font' => 'helvetica',
        ]);

        $mpdf->WriteHTML('
        <style>
            body { font-family: helvetica, sans-serif; font-size: 9pt; line-height: 1.4; }
            table { border-collapse: collapse; width: 100%; margin-top: 6px; }
            td, th { padding: 4px; vertical-align: top; }

            .header-table { width: 100%; margin-top: 0; }
            .header-table td { border: none; text-align: center; padding: 2px 4px; }

            /* Fixed grid: item # | label | : | value | photo (govt-style alignment) */
            table.form-p-grid {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
                margin-top: 0;
            }
            table.form-p-grid td {
                border: none;
                vertical-align: top;
                padding: 5px 3px;
            }
            table.form-p-grid td.fp-num {
                width: 6%;
                text-align: right;
                padding-right: 8px;
                padding-left: 0;
            }
            table.form-p-grid td.fp-label {
                width: 42%;
                text-align: left;
            }
            table.form-p-grid td.fp-colon {
                width: 3%;
                text-align: center;
                padding-left: 8px;
                padding-right: 2px;
            }
            table.form-p-grid td.fp-val {
                width: 25%;
                text-align: left;
                word-wrap: break-word;
                padding-left: 4px;
            }
            table.form-p-grid td.fp-photo {
                width: 24%;
                text-align: center;
            }
            table.form-p-grid td.fp-section-span {
                padding: 7px 6px;
                text-align: left;
            }
            table.form-p-grid.form-p-top { margin-top: 20px; }
            table.form-p-grid.form-p-section-hdr { margin-top: 10px; }

            .tbl-bordered td, .tbl-bordered th { border: 1px solid #000; text-align: center; }
            .tbl-bordered th:first-child,
            .tbl-bordered td:first-child { width: 8%; text-align: center; }

            .section-table { width: 95%; margin: 1% 0 0 5%; }

            .payment-table { width: 90%; margin: 10px auto 0 auto; }
            .payment-table th,
            .payment-table td { border: 1px solid #000; padding: 4px 6px; text-align: left; }
            .payment-table th { text-align: center; font-weight: bold; }

            table.form-p-grid.form-p-qs-final { margin-top: 22px; margin-bottom: 4px; }
            table.form-p-grid.form-p-qs-final tr.form-p-q6 > td,
            table.form-p-grid.form-p-qs-final tr.form-p-q7 > td {
                padding-top: 14px;
                padding-bottom: 6px;
            }
        </style>', HTMLParserMode::HEADER_CSS);

        $words = preg_split('/\s+/', trim($addressUpper));
        $formattedAddress = '';
        foreach (array_chunk($words, 3) as $chunk) {
            $formattedAddress .= implode(' ', $chunk) . '<br>';
        }

        $html = '
        <table class="header-table">
            <tr>
                <td style="font-size:12pt; font-weight:bold;">GOVERNMENT OF TAMILNADU</td>
            </tr>
            <tr>
                <td style="font-size:11pt; font-weight:bold;">THE ELECTRICAL LICENSING BOARD</td>
            </tr>
            <tr>
                <td>THIRU.VI.KA.INDUSTRIAL.ESTATE, GUINDY, CHENNAI – 600032.</td>
            </tr>
            <tr>
                <td style="font-size:11pt; font-weight:bold;">
                    FORM "' . $formNameUpper . ($applTypeCode === 'R' ? '" - RENEWAL' : '"') . '
                </td>
            </tr>
            <tr>
                <td>' . e($certificateTextUpper) . '</td>
            </tr>
            <tr>
                <td style="font-size:11pt; font-weight:bold;">
                    APPLICATION NUMBER: <span>' . e($appIdUpper) . '</span>
                </td>
            </tr>
        </table>';

        // Applicant & photo — single fixed grid so #, labels, colons, values align with (IV)/6/7
        $html .= '<table class="form-p-grid form-p-top" cellpadding="0" cellspacing="0">
        <tr>
            <td class="fp-num">1.</td>
            <td class="fp-label">NAME OF THE APPLICANT</td>
            <td class="fp-colon">:</td>
            <td class="fp-val">' . e($applicantNameUpper) . '</td>
            <td class="fp-photo" rowspan="4">';

        if ($applicant_photo && file_exists(public_path($applicant_photo->upload_path))) {
            $html .= '<img src="' . public_path($applicant_photo->upload_path) . '" style="width:120px; height:140px; border:1px solid;">';
        } else {
            $html .= '<span>No Photo</span>';
        }

        $html .= '</td>
        </tr>
        <tr>
            <td class="fp-num">2.</td>
            <td class="fp-label">FATHER\'S NAME</td>
            <td class="fp-colon">:</td>
            <td class="fp-val">' . e($fatherNameUpper) . '</td>
        </tr>
        <tr>
            <td class="fp-num">3.</td>
            <td class="fp-label">ADDRESS OF THE APPLICANT</td>
            <td class="fp-colon">:</td>
            <td class="fp-val">' . $formattedAddress . '</td>
        </tr>
        <tr>
            <td class="fp-num">4.</td>
            <td class="fp-label">DATE OF BIRTH AND AGE</td>
            <td class="fp-colon">:</td>
            <td class="fp-val">' . e($dobDisplay) . '</td>
        </tr>
        <tr>
            <td class="fp-num">5.</td>
            <td class="fp-section-span" colspan="4">(I) DETAILS OF TECHNICAL QUALIFICATION PASSED BY THE APPLICANT</td>
        </tr>
        </table>';

        $html .= '
        <div class="section-table">
        <table class="tbl-bordered">
        <tr>
        <th style="font-size:9pt;" rowspan="2">S.NO</th>
        <th style="font-size:9pt;" rowspan="2">EDUCATION LEVEL</th>
        <th style="font-size:9pt;" rowspan="2">INSTITUTION</th>
        <th style="font-size:9pt;" colspan="2">MONTH &amp; YEAR OF PASSING</th>
        <th style="font-size:9pt;" rowspan="2">CERTIFICATE NO</th>
        </tr>
        <tr>
        <th style="font-size:9pt;">MONTH</th>
        <th style="font-size:9pt;">YEAR</th>
        </tr>';
        foreach ($education as $i => $edu) {
            $passingMonth = trim((string) ($edu->month_passing ?? ''));
            $passingYear = trim((string) ($edu->year_of_passing ?? ''));
            $html .= '<tr>
                <td>' . ($i + 1) . '</td>
                <td>' . e($edu->educational_level ?? '') . '</td>
                <td>' . e($edu->institute_name ?? '') . '</td>
                <td>' . e($passingMonth !== '' ? $passingMonth : '-') . '</td>
                <td>' . e($passingYear !== '' ? $passingYear : '-') . '</td>
                <td>' . e($edu->certificate_no ?? '') . '</td>
            </tr>';
        }
        $html .= '</table></div>';

        $html .= '<table class="form-p-grid form-p-section-hdr" cellpadding="0" cellspacing="0">
        <tr>
            <td class="fp-num"></td>
            <td class="fp-section-span" colspan="4">(II) INSTITUTE IN WHICH THE APPLICANT HAS UNDERGONE THE TRAINING AND THE PERIOD</td>
        </tr>
        </table>
        <div class="section-table">
        <table class="tbl-bordered">
        <tr>
        <th style="font-size:9pt;">S.NO</th>
        <th style="font-size:9pt;">INSTITUTE NAME &amp; ADDRESS</th>
        <th style="font-size:9pt;">DURATION</th>
        <th style="font-size:9pt;">FROM DATE</th>
        <th style="font-size:9pt;">TO DATE</th>
        </tr>';
        foreach ($institutes as $i => $inst) {
            $html .= '<tr>
                <td>' . ($i + 1) . '</td>
                <td>' . e($inst->institute_name_address ?? '') . '</td>
                <td>' . e((string) ($inst->duration ?? '')) . ' YEARS</td>
                <td>' . e(format_date($inst->from_date) ?? '') . '</td>
                <td>' . e(format_date($inst->to_date) ?? '') . '</td>
            </tr>';
        }
        $html .= '</table></div>';

        $html .= '<table class="form-p-grid form-p-section-hdr" cellpadding="0" cellspacing="0">
        <tr>
            <td class="fp-num"></td>
            <td class="fp-section-span" colspan="4">(III) POWER STATION TO WHICH HE IS ATTACHED AT PRESENT</td>
        </tr>
        </table>
        <div class="section-table">
        <table class="tbl-bordered">
        <tr>
        <th style="font-size:9pt;">S.NO</th>
        <th style="font-size:9pt;">POWER STATION NAME</th>
        <th style="font-size:9pt;">EXPERIENCE</th>
        <th style="font-size:9pt;">DESIGNATION</th>
        </tr>';

        $hasExpData = $experience->contains(function ($exp) {
            return trim($exp->emp_cate ?? $exp->company_name ?? '') !== ''
                || trim($exp->total_exp ?? $exp->experience ?? '') !== ''
                || trim($exp->designation ?? '') !== '';
        });

        if (!$hasExpData) {
            $html .= '<tr><td>1</td><td>NIL</td><td>NIL</td><td>NIL</td></tr>';
        } else {
            foreach ($experience as $i => $exp) {
                $html .= '<tr>
                    <td>' . ($i + 1) . '</td>
                    <td>' . e(mb_strtoupper(($exp->emp_cate ?? $exp->company_name) ?: 'NIL', 'UTF-8')) . '</td>
                    <td>' . e(($exp->total_exp ?? $exp->experience) !== null && ($exp->total_exp ?? $exp->experience) !== '' ? mb_strtoupper(($exp->total_exp ?? $exp->experience) . ' YEARS', 'UTF-8') : 'NIL') . '</td>
                    <td>' . e(mb_strtoupper($exp->designation ?: 'NIL', 'UTF-8')) . '</td>
                </tr>';
            }
        }
        $html .= '</table></div>';

        $html .= '<table class="form-p-grid form-p-qs-final" cellpadding="0" cellspacing="0">
        <tr>
            <td class="fp-num"></td>
            <td class="fp-label">(IV) NAME OF THE EMPLOYER</td>
            <td class="fp-colon">:</td>
            <td class="fp-val">' . e($employerUpper) . '</td>
            <td class="fp-photo">&nbsp;</td>
        </tr>
        <tr class="form-p-q6">
            <td class="fp-num">6.</td>
            <td class="fp-label">HAVE YOU MADE ANY PREVIOUS APPLICATION? IF SO, STATE REFERENCE NO. AND DATE</td>
            <td class="fp-colon">:</td>
            <td class="fp-val">' . e($prevAppValue) . '</td>
            <td class="fp-photo">&nbsp;</td>
        </tr>
        <tr class="form-p-q7">
            <td class="fp-num">7.</td>
            <td class="fp-label">AADHAAR NUMBER</td>
            <td class="fp-colon">:</td>
            <td class="fp-val">' . e($maskedUpper) . '</td>
            <td class="fp-photo">&nbsp;</td>
        </tr>
        </table>';

        if ($payment) {
            $paymentType = mb_strtoupper($payment->payment_mode ?? 'ONLINE', 'UTF-8');
            $transactionNo = mb_strtoupper($payment->transaction_id ?? 'N/A', 'UTF-8');
            $paymentDate = mb_strtoupper(\Carbon\Carbon::parse($payment->created_at)->format('d-m-Y'), 'UTF-8');
            $amountValue = mb_strtoupper('₹ ' . ($payment->amount ?? 'N/A'), 'UTF-8');
            $statusValue = mb_strtoupper($payment->payment_status ?? 'N/A', 'UTF-8');

            $html .= '
            <br><br>
            <table class="header-table" style="border:none; margin-bottom:2px;">
                <tr><td style="font-size:11pt; font-weight:bold;">PAYMENT DETAILS</td></tr>
            </table>
            <table class="payment-table">
                <tr>
                    <th>PAYMENT TYPE</th>
                    <th>TRANSACTION NUMBER</th>
                    <th>PAYMENT DATE</th>
                    <th>AMOUNT</th>
                    <th>PAYMENT STATUS</th>
                </tr>
                <tr>
                    <td>' . e($paymentType) . '</td>
                    <td>' . e($transactionNo) . '</td>
                    <td>' . e($paymentDate) . '</td>
                    <td>' . e($amountValue) . '</td>
                    <td>' . e($statusValue) . '</td>
                </tr>
            </table>';
        }

        $html .= '<br><br>
        <table width="100%" style="border-collapse:collapse; margin-top:10px;">
            <tr>
                <td style="text-align:left;"><strong>Place:</strong> Chennai</td>
                <td style="text-align:right;"><strong>Date:</strong> ' . date('d-m-Y') . '</td>
            </tr>
        </table>';

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('Application_Details.pdf', 'I'))
            ->header('Content-Type', 'application/pdf');
    }


    public function generateFormPPDFTA($newApplicationId)
    {

        $form = TnelbFormP::where('application_id', $newApplicationId)->first();

        // var_dump(format_date($form->previously_number));die;
        $education = Mst_education::where('application_id', $newApplicationId)->get();
        $experience = Mst_experience::where('application_id', $newApplicationId)->get();
        $institutes = TnelbAppsInstitute::where('application_id', $newApplicationId)->get();
        $applicant_photo = TnelbApplicantPhoto::where('application_id', $newApplicationId)->first();
        $payment = DB::table('payments')->where('application_id', $newApplicationId)->first();

        if (!$form) {
            return redirect()->back()->with('error', 'No records found!');
        }

        $decryptedaadhar = $this->safeDecryptString($form->aadhaar);
        $decryptedaadhar = $decryptedaadhar ? preg_replace('/\s+/', '', $decryptedaadhar) : '';
        $masked = strlen($decryptedaadhar) === 12 ? str_repeat('X', 8) . substr($decryptedaadhar, -4) : 'Invalid Aadhaar';

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'default_font' => 'marutham',
            'fontDir' => array_merge(
                (new ConfigVariables())->getDefaults()['fontDir'],
                [public_path('fonts')]
            ),
            'fontdata' => array_merge(
                (new FontVariables())->getDefaults()['fontdata'],
                [
                    'marutham' => [
                        'R' => 'Marutham.ttf',
                        'useOTL' => 0xFF, // ✅ REQUIRED FOR TAMIL
                        'useKashida' => 75,
                    ],
                ]
            ),
            'default_font' => 'helvetica'
        ]);

        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;
    
        $mpdf->WriteHTML('
        <style>
            body { 
                font-family: helvetica;
                font-size: 10pt;
                line-height: 1.6;
            }
            .ta {
                font-family: marutham;
            }
            h3, h4, p { margin: 4px 0; }
            table { border-collapse: collapse; width: 100%; margin-top: 6px; }
            td, th { padding: 4px; vertical-align: top; }
            .label { width: 35%; text-align: left; font-weight: bold; }
            .value { width: 40%; text-align: left; }
            .tbl-bordered td, .tbl-bordered th { border: 1px solid #000; text-align: center; }
            .tbl-no-border td { border: none; padding-bottom: 12px; } /* ⬅ spacing between rows */
            .photo-cell { text-align:center; }
            .employer { display:flex }
            .value {
                line-height: 1.6;
            }
            
        </style>', HTMLParserMode::HEADER_CSS);
    
        $certificateText = match ($form->form_name) {
            'S' => 'Application for Competency Certificate for Supervisor',
            'W' => 'Application for Competency Certificate for Wireman',
            'WH' => 'Application for Competency Certificate for Wireman Helper',
            default => 'மின் உற்பத்தி நிலைய செயல்பாடு மற்றும் பராமரிப்பு திறன் சான்றிதழுக்கான விண்ணப்பம்',
        };
    
        $html = '
        <h3 class="ta" style="text-align:center;">தமிழ்நாடு அரசு</h3>
        <h4 class="ta" style="text-align:center;">மின்சார உரிம வாரியம்</h4>
        <p class="ta" style="text-align:center;">திரு.வி.கா. தொழிற்சாலை, கிண்டி, சென்னை – 600032.</p>
        <h4 class="ta" style="text-align:center;">படிவம் - "' . $form->form_name . ($form->appl_type == 'R' ? '" - Renewal' : '"') . '</h4>
        <p style="text-align:center;">' . $certificateText . '</p>
        <h4 class="ta" style="text-align:center;">விண்ணப்ப எண்: <strong>' . $form->application_id . '</strong></h4>';
    
        $html .= '<table class="tbl-no-border" style="table-layout:fixed; width:100%;">
        <tr>
            <td class="ta label">1. விண்ணப்பதாரரின் பெயர்</td>
            <td class="value">: ' . $form->applicant_name . '</td>
            <td rowspan="4" class="photo-cell">';
    
        if ($applicant_photo && file_exists(public_path($applicant_photo->upload_path))) {
            $html .= '<img src="' . public_path($applicant_photo->upload_path) . '" style="width:120px; height:150px; border:1px solid;">';
        } else {
            $html .= '<p>No Photo</p>';
        }
     
        $html .= '</td></tr>
        <tr>
            <td class="ta label">2. தகப்பனார் பெயர்</td>
            <td class="value">: ' . $form->fathers_name . '</td>
        </tr>
        <tr>
            <td class="ta label">3. விண்ணப்பதாரர் முகவரி (தெளிவாக இருக்க வேண்டும்)</td>
            <td class="value text-wrap" >:
                ' .
                $this->formatAddressToThreeLines($form->applicants_address)
                . '</td>
        </tr>
        <tr>
            <td class="ta label">4. பிறந்த நாள், மாதம், வருடம் மற்றும் வயது</td>
            <td class="value">: ' . $form->d_o_b . ' (' . $form->age . ' years)</td>
        </tr>
        </table>';
    
        // Education
        $html .= '<h4 class="ta">5 . (i). விண்ணப்பதாரியின் தொழில்நுட்ப தகுதி மற்றும் தேர்ச்சி பற்றிய விவரங்கள்
        (அசல் சான்றிதழ்களை புகைப்பட நகல்களுடன் இணைத்திடுக. அசல் பார்க்கப்பட்ட பின்பு திருப்பி அளிக்கப்படும்)</h4>
        <table class="tbl-bordered">
        <tr>
        <th class="ta">வரிைச எண</th><th class="ta">கல்வி நிலை</th><th class="ta">கல்வி நிறுவனம்</th><th class="ta">தேர்ச்சி பெற்ற மாதம் &amp; ஆண்டு</th>
        <th class="ta">சான்றிதழ் எண்</th>
        </tr>';
        foreach ($education as $i => $edu) {
            $passingMonth = trim((string) ($edu->month_passing ?? ''));
            $passingYear = trim((string) ($edu->year_of_passing ?? ''));
            $html .= '<tr>
                <td>' . ($i + 1) . '</td>
                <td>' . $edu->educational_level . '</td>
                <td>' . $edu->institute_name . '</td>
                <td>' . ($passingMonth !== '' ? $passingMonth : '-') . '</td>
                <td>' . ($passingYear !== '' ? $passingYear : '-') . '</td>
                <td>' . $edu->certificate_no . '</td>
            </tr>';
        }
        $html .= '</table>';
    
        // Experience
        $html .= '<h4 class="ta">(ii). விண்ணப்பதாரர் பயிற்சி பெற்ற நிறுவனம் மற்றும் காலம்</h4>
        <table class="tbl-bordered">
        <tr>
        <th class="ta">வரிைச எண</th><th class="ta">நிறுவனத்தின் பெயர் & முகவரி</th><th class="ta">பயிற்சி பெற்ற காலம்</th><th class="ta">தேதி முதல்</th><th class="ta">தேதி வரை</th>
        </tr>';
        foreach ($institutes as $i => $inst) {
            $html .= '<tr>
                <td>' . ($i + 1) . '</td>
                <td>' . $inst->institute_name_address . '</td>
                <td>' . $inst->duration . ' Years </td>
                <td>' . format_date($inst->from_date) . '</td>
                <td>' . format_date($inst->to_date) . '</td>
            </tr>';
        }
        $html .= '</table>';


        // Section 5 (iii) – Tamil – தற்போது பணியாற்றி வரும் மின் நிலையம்
        $html .= '<h4 class="ta">(iii). தற்போது பணியாற்றி வரும் மின் நிலையம்</h4>
        <table class="tbl-bordered">
        <tr>
        <th class="ta">வரிசை எண்</th><th class="ta">மின் நிலையத்தின் பெயர்</th><th class="ta">அனுபவம் (ஆண்டுகள்)</th><th class="ta">பதவி</th>
        </tr>';

        $hasExpDataTa = $experience->contains(function ($exp) {
            return trim($exp->emp_cate ?? $exp->company_name ?? '') !== ''
                || trim($exp->total_exp ?? $exp->experience ?? '') !== ''
                || trim($exp->designation ?? '') !== '';
        });

        if (!$hasExpDataTa) {
            $html .= '<tr>
                <td class="ta">1</td>
                <td class="ta">Nil</td>
                <td class="ta">Nil</td>
                <td class="ta">Nil</td>
            </tr>';
        } else {
            foreach ($experience as $i => $exp) {
                $html .= '<tr>
                    <td class="ta">' . ($i + 1) . '</td>
                    <td class="ta">' . (($exp->emp_cate ?? $exp->company_name) ?: 'Nil') . '</td>
                    <td class="ta">' . ((($exp->total_exp ?? $exp->experience) !== null && ($exp->total_exp ?? $exp->experience) !== '') ? ($exp->total_exp ?? $exp->experience) . ' Years ' : 'Nil') . '</td>
                    <td class="ta">' . ($exp->designation ?: 'Nil') . '</td>
                </tr>';
            }
        }
        $html .= '</table>';

        // Section 5 (iv) – Tamil – நிறுவனத்தின் பெயர்
        $employerNameTa = trim($form->employer_detail ?? '');
        if ($employerNameTa === '') {
            $employerNameTa = 'Nil';
        }
        $html .= '<div class="ta employer"><span class="label">(iv). நிறுவனத்தின் பெயர் :</span> ' . $employerNameTa . '</div>';


        // Question 6 – previous application (heading only)
        $html .='<h4 class="ta">6. முன்பு நீங்கள் ஏதேனும் விண்ணப்பம் சமர்ப்பித்துள்ளீர்களா? இருப்பின், அதன் குறிப்பு எண் மற்றும் தேதியை குறிப்பிடவும்.</h4>'; 

        // Question 7 – Aadhaar Number (masked, same value as English) in row format
        $html .= '
        <table style="width:100%; border-collapse:collapse; margin-top:6px;">
            <tr>
                <td style="width:5%; text-align:right; padding-right:6px;" class="ta">7.</td>
                <td style="width:65%; text-align:left;" class="ta">ஆதார் எண்</td>
                <td style="width:30%; text-align:left;">: ' . $masked . '</td>
            </tr>
        </table>';

        // Append English payment details at bottom, if available
        if ($payment) {
            $paymentType = mb_strtoupper($payment->payment_mode ?? 'ONLINE', 'UTF-8');
            $transactionNo = mb_strtoupper($payment->transaction_id ?? 'N/A', 'UTF-8');
            $paymentDate = mb_strtoupper(\Carbon\Carbon::parse($payment->created_at)->format('d-m-Y'), 'UTF-8');
            $amountValue = mb_strtoupper('₹ ' . ($payment->amount ?? 'N/A'), 'UTF-8');
            $statusValue = mb_strtoupper($payment->payment_status ?? 'N/A', 'UTF-8');

            $html .= '
            <br><br>
            <div style="font-family: Arial, sans-serif; font-size:10pt;">
            <p style="font-size:11pt; font-weight:bold; margin-top:10px; text-align:center;">PAYMENT DETAILS</p>
            <table class="payment-table">
                <tr>
                    <th>PAYMENT TYPE</th>
                    <th>TRANSACTION NUMBER</th>
                    <th>PAYMENT DATE</th>
                    <th>AMOUNT</th>
                    <th>PAYMENT STATUS</th>
                </tr>
                <tr>
                    <td>' . e($paymentType) . '</td>
                    <td>' . e($transactionNo) . '</td>
                    <td>' . e($paymentDate) . '</td>
                    <td>' . e($amountValue) . '</td>
                    <td>' . e($statusValue) . '</td>
                </tr>
            </table>
            </div>';
        }

        // Only keep Place / Date at the very bottom (no signature)
        $html .= '<br><br>
        <p><strong class="ta">இடம்:</strong> Chennai</p>
        <p><strong class="ta">தேதி:</strong> ' . date('d-m-Y') . '</p>';
    
        $mpdf->WriteHTML($html);
        return response($mpdf->Output('Application_Details.pdf', 'I'))->header('Content-Type', 'application/pdf');
    }

    public function generatePDF($newApplicationId)
    {
        
        $form = Mst_Form_s_w::where('application_id', $newApplicationId)->first();

        // var_dump(format_date($form->previously_number));die;
        $education = Mst_education::where('application_id', $newApplicationId)->get();
        $experience = Mst_experience::where('application_id', $newApplicationId)->get();
        $applicant_photo = TnelbApplicantPhoto::where('application_id', $newApplicationId)->first();
        $payment = DB::table('payments')->where('application_id', $newApplicationId)->first();

        if (!$form) {
            return redirect()->back()->with('error', 'No records found!');
        }

        $decryptedaadhar = $this->safeDecryptString($form->aadhaar);
        $decryptedaadhar = $decryptedaadhar ? preg_replace('/\s+/', '', $decryptedaadhar) : '';
        $masked = strlen($decryptedaadhar) === 12 ? str_repeat('X', 8) . substr($decryptedaadhar, -4) : 'Invalid Aadhaar';
        $decryptedPan = $this->safeDecryptString($form->pancard);
        $decryptedPan = $decryptedPan ? strtoupper(preg_replace('/[^A-Z0-9]/i', '', $decryptedPan)) : '';
        $maskedPan = strlen($decryptedPan) === 10 ? str_repeat('X', 6) . substr($decryptedPan, -4) : '';

        // $wrap = function ($text, $length = 20) {
        //     return wordwrap($text, $length, '<br>', true);
        // };

        $mpdf = new Mpdf([
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'default_font_size' => 10,
            'default_font'      => 'helvetica',
            'margin_left'       => 15,
            'margin_right'      => 15,
            'margin_top'        => 12,
            'margin_bottom'     => 12,
        ]);

        $mpdf->WriteHTML('
        <style>
            body { font-family: helvetica, sans-serif; font-size: 10pt; line-height: 1.5; color: #1a1a1a; margin: 0; padding: 0; }

            /* ── Page header ── */
            .hdr { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
            .hdr td { border: none; text-align: center; padding: 1px 0; line-height: 1.45; }

            /* ── Shared num column (used in pd-rows, sec-heading, q-tbl) ── */
            .col-num   { width: 6%;  font-weight: bold; text-align: left; white-space: nowrap;
                         vertical-align: top; padding: 4px 4px 4px 0; border: none; color: #1a1a1a; }

            /* ── Personal details outer (content | photo) ── */
            .pd-outer { width: 100%; border-collapse: collapse; margin-top: 10px; }
            .pd-outer td { border: none; padding: 0; vertical-align: top; }
            .pd-content { width: 78%; }
            .pd-photo   { width: 22%; text-align: center; padding-left: 8px; vertical-align: top; }

            /* ── Flat 4-col row: num | label | colon | value ── */
            .pd-rows { width: 100%; border-collapse: collapse; }
            .pd-rows td { border: none; vertical-align: top; padding: 4px 4px 4px 0; color: #1a1a1a; }
            .pd-num   { width: 6%;  font-weight: bold; text-align: left; white-space: nowrap; }
            .pd-label { width: 34%; text-align: left; }
            .pd-colon { width: 4%;  text-align: center; }
            .pd-val   { width: 56%; text-align: left; }

            /* ── Section heading row (5., 6.) ── */
            .sec-heading { width: 100%; border-collapse: collapse; margin-top: 6px; }
            .sec-heading td { border: none; padding: 4px 4px 4px 0; vertical-align: top; color: #1a1a1a; }
            .sec-heading .pd-num { width: 6%; font-weight: bold; }

            /* ── Data tables (education / experience) ── */
            .tbl-data { width: 100%; border-collapse: collapse; margin-top: 3px; font-size: 9pt; }
            .tbl-data th { border: 1px solid #333; background: #eeeeee; font-weight: bold;
                           text-align: center; padding: 5px 4px; vertical-align: middle; white-space: nowrap; }
            .tbl-data td { border: 1px solid #333; text-align: center;
                           padding: 4px 4px; vertical-align: middle; color: #1a1a1a; }
            .tbl-data td.td-left { text-align: left; }

            /* ── Data table cell color fix ── */
            .tbl-data td, .tbl-data th { color: #1a1a1a; }

            /* ── Q&A table (7, 8, 9, 10 …) ── */
            .q-tbl { width: 100%; border-collapse: collapse; margin-top: 8px; }
            .q-tbl td { border: none; padding: 4px 4px 4px 0; vertical-align: top; color: #1a1a1a; }
            .q-num  { width: 6%;  font-weight: bold; text-align: left; white-space: nowrap; }
            .q-qa   { width: 94%; text-align: left; }
            .q-qa-inner { width: 100%; border-collapse: collapse; }
            .q-qa-inner td { border: none; padding: 0; vertical-align: top; color: #1a1a1a; }
            .q-qa-text { width: 80%; text-align: left; }
            .q-qa-ans  { width: 20%; text-align: left; white-space: nowrap; }

            /* ── Certificate sub-detail row ── */
            .cert-sub { width: 90%; border-collapse: collapse; margin: 4px 0 6px 0; font-size: 9pt; }
            .cert-sub td { border: 1px solid #aaa; padding: 5px 10px; text-align: center;
                           background: #f9f9f9; color: #1a1a1a; }

            /* ── Payment table ── */
            .pay-tbl { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 9.5pt; }
            .pay-tbl th { border: 1px solid #333; background: #eeeeee; font-weight: bold;
                          text-align: center; padding: 6px 8px; color: #1a1a1a; }
            .pay-tbl td { border: 1px solid #333; text-align: center; padding: 6px 8px; color: #1a1a1a; }
        </style>', HTMLParserMode::HEADER_CSS);
    
        $certificateText = match ($form->form_name) {
            'S' => 'Acknowledgement Slip for Supervisor Competency Certificate',
            'W' => 'Acknowledgement Slip for Wireman Competency Certificate',
            'WH' => 'Acknowledgement Slip for Wireman Helper Competency Certificate',
            default => 'Acknowledgement Slip for Competency Certificate',
        };

        // Pre-format key personal details in CAPITAL LETTERS
        $applicantNameUpper = mb_strtoupper($form->applicant_name ?? '', 'UTF-8');
        $fatherNameUpper    = mb_strtoupper($form->fathers_name ?? '', 'UTF-8');
        $addressRaw         = $form->applicants_address ?? '';
        $addressUpper       = mb_strtoupper($addressRaw, 'UTF-8');

        $dobDisplay  = trim(($form->d_o_b ?? '') . ' (' . ($form->age ?? '') . ' YEARS)');
        $dobDisplay  = mb_strtoupper($dobDisplay, 'UTF-8');

        $appIdUpper    = mb_strtoupper($form->application_id ?? '', 'UTF-8');
        $formNameUpper = mb_strtoupper($form->form_name ?? '', 'UTF-8');
        $applTypeCode  = strtoupper(trim($form->appl_type ?? 'N'));
        $typeOfForm    = ($applTypeCode === 'N') ? 'NEW APPLICATION' : 'RENEWAL APPLICATION';

        // ── Page header ──────────────────────────────────────────────
        $html = '
        <table class="hdr">
            <tr><td style="font-size:13pt; font-weight:bold; letter-spacing:.5px;">GOVERNMENT OF TAMILNADU</td></tr>
            <tr><td style="font-size:11.5pt; font-weight:bold;">ELECTRICAL LICENSING BOARD</td></tr>
            <tr><td style="font-size:9.5pt;">THIRU.VI.KA.INDUSTRIAL.ESTATE, GUINDY, CHENNAI &ndash; 600032.</td></tr>
            <tr><td style="padding:4px 0; font-size:11pt; font-weight:bold;">
                FORM &ldquo;' . $formNameUpper . ($form->appl_type == 'R' ? '&rdquo; &ndash; RENEWAL' : '&rdquo;') . '
            </td></tr>
            <tr><td style="font-size:9.5pt; padding-top:2px;">' . mb_strtoupper($certificateText, 'UTF-8') . '</td></tr>
            <tr><td style="font-size:11pt; font-weight:bold; padding-top:3px;">APPLICATION NUMBER : ' . $appIdUpper . '</td></tr>
        </table>';

        // ── Format address (preserve line breaks, no forced word chunking) ──
        $formattedAddress = nl2br(e($addressUpper));

        // ── Photo HTML ───────────────────────────────────────────────────────
        if ($applicant_photo && file_exists(public_path($applicant_photo->upload_path))) {
            $photoHtml = '<img src="' . public_path($applicant_photo->upload_path)
                       . '" style="width:110px; height:130px; border:1px solid #555;">';
        } else {
            $photoHtml = '<div style="width:110px; height:130px; border:1px solid #999; display:inline-block; '
                       . 'text-align:center; vertical-align:middle; font-size:8pt; color:#777; padding-top:50px;">No Photo</div>';
        }

        // ── Items 1-4 (left) + photo (right) via fixed 2-col table ─────────
        $html .= '
        <table style="width:100%; border-collapse:collapse; margin-top:14px;">
          <tr>
            <td style="vertical-align:top; padding:0;">
              <table style="width:100%; border-collapse:collapse;">
                <tr>
                  <td style="width:28pt; font-weight:bold; vertical-align:top; padding:4px 4px 4px 0; color:#1a1a1a; white-space:nowrap;">1.</td>
                  <td style="width:34%; vertical-align:top; padding:4px 4px 4px 0; color:#1a1a1a;">NAME OF THE APPLICANT</td>
                  <td style="width:4%;  vertical-align:top; padding:4px 2px; text-align:center; color:#1a1a1a;">:</td>
                  <td style="vertical-align:top; padding:4px 0; color:#1a1a1a;">' . e($applicantNameUpper) . '</td>
                </tr>
                <tr>
                  <td style="width:28pt; font-weight:bold; vertical-align:top; padding:4px 4px 4px 0; color:#1a1a1a; white-space:nowrap;">2.</td>
                  <td style="vertical-align:top; padding:4px 4px 4px 0; color:#1a1a1a;">FATHER&rsquo;S NAME</td>
                  <td style="vertical-align:top; padding:4px 2px; text-align:center; color:#1a1a1a;">:</td>
                  <td style="vertical-align:top; padding:4px 0; color:#1a1a1a;">' . e($fatherNameUpper) . '</td>
                </tr>
                <tr>
                  <td style="width:28pt; font-weight:bold; vertical-align:top; padding:4px 4px 4px 0; color:#1a1a1a; white-space:nowrap;">3.</td>
                  <td style="vertical-align:top; padding:4px 4px 4px 0; color:#1a1a1a;">ADDRESS OF THE APPLICANT</td>
                  <td style="vertical-align:top; padding:4px 2px; text-align:center; color:#1a1a1a;">:</td>
                  <td style="vertical-align:top; padding:4px 0; color:#1a1a1a;">' . $formattedAddress . '</td>
                </tr>
                <tr>
                  <td style="width:28pt; font-weight:bold; vertical-align:top; padding:4px 4px 4px 0; color:#1a1a1a; white-space:nowrap;">4.</td>
                  <td style="vertical-align:top; padding:4px 4px 4px 0; color:#1a1a1a;">DATE OF BIRTH AND AGE</td>
                  <td style="vertical-align:top; padding:4px 2px; text-align:center; color:#1a1a1a;">:</td>
                  <td style="vertical-align:top; padding:4px 0; color:#1a1a1a;">' . e($dobDisplay) . '</td>
                </tr>
              </table>
            </td>
            <td style="width:125pt; vertical-align:top; text-align:center; padding-left:8px;">' . $photoHtml . '</td>
          </tr>
        </table>';

        // ── 5. Education ─────────────────────────────────────────────────────
        $html .= '
        <table style="width:100%; border-collapse:collapse; margin-top:10px;">
          <tr>
            <td style="width:28pt; font-weight:normal; vertical-align:top; padding:4px 4px 4px 0; color:#1a1a1a; white-space:nowrap;">5.</td>
            <td style="vertical-align:top; padding:4px 0; color:#1a1a1a;">DETAILS OF TECHNICAL QUALIFICATION AND EXAMINATION, IF ANY PASSED BY THE APPLICANT</td>
          </tr>
        </table>
        <table class="tbl-data" style="margin-top:4px;">
          <tr>
            <th rowspan="2" style="width:6%;">S.NO</th>
            <th rowspan="2">EDUCATION LEVEL</th>
            <th rowspan="2">INSTITUTION</th>
            <th colspan="2">MONTH &amp; YEAR OF PASSING</th>
            <th rowspan="2">CERTIFICATE NO</th>
          </tr>
          <tr>
            <th style="width:10%;">MONTH</th>
            <th style="width:10%;">YEAR</th>
          </tr>';
        foreach ($education as $i => $edu) {
            $passingMonth = trim((string) ($edu->month_passing ?? ''));
            $passingYear  = trim((string) ($edu->year_of_passing ?? ''));
            $html .= '<tr>
                <td>' . ($i + 1) . '</td>
                <td>' . e($edu->educational_level) . '</td>
                <td class="td-left">' . e($edu->institute_name) . '</td>
                <td>' . ($passingMonth !== '' ? $passingMonth : '-') . '</td>
                <td>' . ($passingYear  !== '' ? $passingYear  : '-') . '</td>
                <td>' . e($edu->certificate_no) . '</td>
            </tr>';
        }
        $html .= '</table>';

        // ── 6. Experience ────────────────────────────────────────────────────
        if ($form->form_name !== 'WH') {
            $isFormS = strtoupper((string) $form->form_name) === 'S';

            $html .= '
            <table style="width:100%; border-collapse:collapse; margin-top:8px;">
              <tr>
                <td style="width:28pt; font-weight:normal; vertical-align:top; padding:4px 4px 4px 0; color:#1a1a1a; white-space:nowrap;">6.</td>
                <td style="vertical-align:top; padding:4px 0; color:#1a1a1a;">DETAILS OF PAST AND PRESENT EXPERIENCE</td>
              </tr>
            </table>
            <table class="tbl-data" style="margin-top:4px;">';

            $hasContractorRow = $isFormS && $experience->contains(function ($exp) {
                return strtolower(trim((string) ($exp->emp_type ?? ''))) === 'contractor';
            });

            if ($isFormS) {
                $html .= '<tr>
                    <th rowspan="2" style="width:5%;">S.NO</th>
                    <th rowspan="2">EMPLOYMENT TYPE</th>
                    <th rowspan="2">EMPLOYER / ORGANIZATION</th>
                    <th colspan="3">YEAR OF EXPERIENCE</th>
                    <th rowspan="2">DESIGNATION</th>'
                    . ($hasContractorRow ? '<th rowspan="2">INTIMATION DATE</th>' : '') . '
                </tr>
                <tr>
                    <th>FROM (DATE)</th>
                    <th>TO (DATE)</th>
                    <th style="width:8%;">TOTAL YRS</th>
                </tr>';
            } else {
                $html .= '<tr>
                    <th rowspan="2" style="width:5%;">S.NO</th>
                    <th rowspan="2">EMPLOYER NAME</th>
                    <th colspan="3">YEAR OF EXPERIENCE</th>
                    <th rowspan="2">DESIGNATION</th>
                </tr>
                <tr>
                    <th>FROM (DATE)</th>
                    <th>TO (DATE)</th>
                    <th style="width:10%;">TOTAL YRS</th>
                </tr>';
            }

            $hasExpData = $experience->contains(function ($exp) use ($isFormS) {
                if ($isFormS) {
                    return trim((string) ($exp->emp_type ?? '')) !== ''
                        || trim((string) ($exp->emp_cate ?? $exp->company_name ?? '')) !== ''
                        || trim((string) ($exp->from_date ?? '')) !== ''
                        || trim((string) ($exp->to_date ?? '')) !== ''
                        || trim((string) ($exp->total_exp ?? $exp->experience ?? '')) !== ''
                        || trim((string) ($exp->designation ?? '')) !== '';
                }
                return trim((string) ($exp->emp_cate ?? $exp->company_name ?? '')) !== ''
                    || trim((string) ($exp->total_exp ?? $exp->experience ?? '')) !== ''
                    || trim((string) ($exp->designation ?? '')) !== '';
            });

            if (!$hasExpData) {
                if ($isFormS) {
                    $extraNilCell = $hasContractorRow ? '<td>-</td>' : '';
                    $html .= '<tr><td>1</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>' . $extraNilCell . '</tr>';
                } else {
                    $html .= '<tr><td>1</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td></tr>';
                }
            } else {
                foreach ($experience as $i => $exp) {
                    $employerName    = $exp->emp_cate ?? $exp->company_name ?? '-';
                    $experienceYears = $exp->total_exp ?? $exp->experience ?? '-';
                    $designation     = $exp->designation ?: '-';

                    if ($isFormS) {
                        $employmentType = $exp->emp_type ?: '-';
                        $fromDate = !empty($exp->from_date) ? format_date($exp->from_date) : '-';
                        $toDate   = !empty($exp->to_date)   ? format_date($exp->to_date)   : '-';
                        $isContractor   = strtolower(trim((string) $employmentType)) === 'contractor';
                        $intimationCell = '';
                        if ($hasContractorRow) {
                            $intimationCell = $isContractor
                                ? '<td>' . e(!empty($exp->intimation_date) ? format_date($exp->intimation_date) : '-') . '</td>'
                                : '<td>-</td>';
                        }
                        $html .= '<tr>
                            <td>' . ($i + 1) . '</td>
                            <td>' . e(ucwords(str_replace('_', ' ', (string) $employmentType))) . '</td>
                            <td class="td-left">' . e($employerName) . '</td>
                            <td>' . e($fromDate) . '</td>
                            <td>' . e($toDate) . '</td>
                            <td>' . e($experienceYears) . '</td>
                            <td class="td-left">' . e($designation) . '</td>'
                            . $intimationCell . '
                        </tr>';
                    } else {
                        $fromDate = !empty($exp->from_date) ? format_date($exp->from_date) : '-';
                        $toDate   = !empty($exp->to_date)   ? format_date($exp->to_date)   : '-';

                        $html .= '<tr>
                            <td>' . ($i + 1) . '</td>
                            <td>' . e($employerName) . '</td>
                            <td>' . e($fromDate) . '</td>
                            <td>' . e($toDate) . '</td>
                            <td>' . e($experienceYears) . '</td>
                            <td>' . e($designation) . '</td>
                        </tr>';
                    }
                }
            }
            $html .= '</table>';
        }

        // ── Compute Yes/No flags ─────────────────────────────────────────────
        $value  = (empty($form->previously_number) || empty($form->previously_date)) ? 'No' : 'Yes';
        $certno = (empty($form->certificate_no)    || empty($form->certificate_date)) ? 'No' : 'Yes';

        $previousRefNo        = trim((string) ($form->previously_number ?? ''));
        $previousIssueDate    = !empty($form->previously_issue_date) ? format_date($form->previously_issue_date) : '-';
        $previousValidityDate = !empty($form->previously_date)       ? format_date($form->previously_date)       : '-';
        $certificateRefNo        = trim((string) ($form->certificate_no ?? ''));
        $certificateIssueDate    = !empty($form->certificate_issue_date) ? format_date($form->certificate_issue_date) : '-';
        $certificateValidityDate = !empty($form->certificate_date)       ? format_date($form->certificate_date)       : '-';

        // ── Helper: render one Q&A row (num | inner: text + answer) ────────
        // ── Q rows — same 28pt num col as items 1-6 ─────────────────────────
        $numStyle  = 'style="width:28pt; font-weight:bold; vertical-align:top; padding:4px 4px 4px 0; color:#1a1a1a; white-space:nowrap;"';
        $textStyle = 'style="width:58%; vertical-align:top; padding:4px 4px 4px 0; color:#1a1a1a;"';
        $colStyle  = 'style="width:3%;  vertical-align:top; padding:4px 2px; text-align:center; color:#1a1a1a;"';
        $ansStyle  = 'style="vertical-align:top; padding:4px 0; color:#1a1a1a;"';

        $qRow = function(string $num, string $text, string $ans) use ($numStyle, $textStyle, $colStyle, $ansStyle) {
            return '
            <tr>
              <td ' . $numStyle  . '>' . $num . '.</td>
              <td ' . $textStyle . '>' . $text . '</td>
              <td ' . $colStyle  . '>:</td>
              <td ' . $ansStyle  . '>' . $ans . '</td>
            </tr>';
        };
        $certSubRow = function(string $refNo, string $issueDate, string $validityDate) use ($numStyle) {
            return '
            <tr>
              <td ' . $numStyle . '></td>
              <td colspan="3" style="padding:2px 0 6px 0;">
                <table class="cert-sub">
                  <tr>
                    <td><strong>Certificate No</strong><br>' . e($refNo) . '</td>
                    <td><strong>Date of Issue</strong><br>' . e($issueDate) . '</td>
                    <td><strong>Validity Date</strong><br>' . e($validityDate) . '</td>
                  </tr>
                </table>
              </td>
            </tr>';
        };

        // ── Q7 – Q10 (Form S) / Q6–Q9 (others) ─────────────────────────────
        $html .= '<table style="width:100%; border-collapse:collapse; margin-top:10px;">';

        if ($form->form_name == 'S') {
            $html .= $qRow('7', 'HAVE YOU MADE ANY PREVIOUS APPLICATION? IF SO, STATE REFERENCE NO AND DATE.', e($value));
            if ($value === 'Yes') {
                $html .= $certSubRow($previousRefNo ?: '-', $previousIssueDate, $previousValidityDate);
            }
            $html .= $qRow('8', 'DO YOU POSSESS WIREMAN COMPETENCY CERTIFICATE / WIREMAN HELPER COMPETENCY CERTIFICATE ISSUED BY THIS BOARD? IF SO FURNISH THE DETAILS AND SURRENDER THE SAME.', e($certno));
            if ($certno === 'Yes') {
                $html .= $certSubRow($certificateRefNo ?: '-', $certificateIssueDate, $certificateValidityDate);
            }
            $html .= $qRow('9',  'AADHAAR NUMBER', $masked);
            $html .= $qRow('10', 'PAN NUMBER',     $maskedPan);
        } else {
            $no = ($form->form_name == 'WH') ? '6' : '7';
            $certLabel = ($form->form_name == 'WH')
                ? 'DO YOU POSSESS WIREMAN HELPER COMPETENCY CERTIFICATE ISSUED BY THIS BOARD? IF SO FURNISH THE DETAILS AND SURRENDER THE SAME.'
                : 'DO YOU POSSESS WIREMAN COMPETENCY CERTIFICATE / WIREMAN HELPER COMPETENCY CERTIFICATE ISSUED BY THIS BOARD? IF SO FURNISH THE DETAILS AND SURRENDER THE SAME.';
            $html .= $qRow($no, $certLabel, e($certno));

            $aadhaarNo = ($form->form_name == 'WH') ? '7' : '8';
            $panNo     = ($form->form_name == 'WH') ? '8' : '9';
            $html .= $qRow($aadhaarNo, 'AADHAAR NUMBER', $masked);
            $html .= $qRow($panNo,     'PAN NUMBER',     $maskedPan);
        }

        $html .= '</table>';

        // ── Payment details ──────────────────────────────────────────────────
        if ($payment) {
            $paymentType   = mb_strtoupper($payment->payment_mode   ?? 'ONLINE', 'UTF-8');
            $transactionNo = mb_strtoupper($payment->transaction_id ?? 'N/A',    'UTF-8');
            $paymentDate   = mb_strtoupper(\Carbon\Carbon::parse($payment->created_at)->format('d-m-Y'), 'UTF-8');
            $amountValue   = '&#8377; ' . ($payment->amount ?? 'N/A');
            $statusValue   = mb_strtoupper($payment->payment_status ?? 'N/A', 'UTF-8');

            $html .= '
            <p style="font-size:11pt; font-weight:bold; margin-top:16px; margin-bottom:4px; text-align:center;">PAYMENT DETAILS</p>
            <table class="pay-tbl">
              <tr>
                <th>PAYMENT TYPE</th>
                <th>TRANSACTION NUMBER</th>
                <th>PAYMENT DATE</th>
                <th>AMOUNT</th>
                <th>PAYMENT STATUS</th>
              </tr>
              <tr>
                <td>' . e($paymentType)   . '</td>
                <td>' . e($transactionNo) . '</td>
                <td>' . e($paymentDate)   . '</td>
                <td>' . $amountValue      . '</td>
                <td>' . e($statusValue)   . '</td>
              </tr>
            </table>';
        }

        // ── Place / Date footer ──────────────────────────────────────────────
        $html .= '
        <table style="width:100%; border-collapse:collapse; margin-top:20px;">
          <tr>
            <td style="text-align:left;"><strong>Place :</strong> Chennai</td>
            <td style="text-align:right;"><strong>Date :</strong> ' . date('d-m-Y') . '</td>
          </tr>
        </table>';

        $mpdf->WriteHTML($html);
        return response($mpdf->Output('Application_Details.pdf', 'I'))->header('Content-Type', 'application/pdf');
    }


    public function generatetcp($newApplicationId)
    {
        // Fetch form details
        $form = Mst_Form_s_w::where('application_id', $newApplicationId)->first();
        $education = Mst_education::where('application_id', $newApplicationId)->get();
        $experience = Mst_experience::where('application_id', $newApplicationId)->get();
        $applicant_photo = TnelbApplicantPhoto::where('application_id', $newApplicationId)->first();
        $payment = DB::table('payments')->where('application_id', $newApplicationId)->first();

        if (!$form) {
            return redirect()->back()->with('error', 'No records found!');
        }

        // Create a new TCPDF instance
        $pdf = new TCPDF();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->SetCreator('TNELB');
        $pdf->SetAuthor('Your App');
        $pdf->SetTitle('TamilNadu Government');
        $pdf->SetMargins(10, 5, 10);
        $pdf->AddPage();

        // Application Title
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 5, 'GOVERNMENT OF TAMILNADU', 0, 1, 'C');

        $pdf->Cell(0, 5, 'ELECTRICAL LICENSING BOARD', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Thiru.Vi.Ka.Indl.Estate, Guindy. Chennai – 600032.', 0, 1, 'C');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 5, 'Form "S"', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Application for Supervisor Competency Certificate', 0, 1, 'C');
        $pdf->Ln(2);

        // Load and display applicant photo (Right side)
        if (!empty($applicant_photo->upload_path)) {
            $photoPath = public_path($applicant_photo->upload_photo);

            if (file_exists($photoPath)) {
                $pdf->Image($photoPath, 150, 35, 40, 40);
            }
        }

        // Set font for content
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Ln(0);

        // Application Details (Left side)

        // $pdf->Cell(50, 10, 'Name of the applicant ', 0, 0);
        // $pdf->Cell(100, 10, ': ' . $form->application_id, 0, 1);
        // Name of the applicant
        $pdf->Cell(50, 9, '1. Name of the applicant', 0, 0);
        $pdf->Cell(5, 9, ':', 0, 0, 'C'); // Centered colon
        $pdf->Cell(85, 9, $form->applicant_name, 0, 1, 'L'); // Left-aligned value

        // Father's Name
        $pdf->Cell(50, 9, '2. Father\'s Name', 0, 0);
        $pdf->Cell(5, 9, ':', 0, 0, 'C');
        $pdf->Cell(85, 9, $form->fathers_name, 0, 1, 'L');

        // Address of the applicant (Use MultiCell to wrap text properly)
        $pdf->Cell(50, 9, '3. Address of the applicant', 0, 0);
        $pdf->Cell(5, 9, ':', 0, 0, 'C');
        $pdf->MultiCell(85, 9, $form->applicants_address, 0, 'L'); // Address wraps properly

        // Date of Birth and Age
        $pdf->Cell(50, 9, '4. Date of Birth and Age', 0, 0);
        $pdf->Cell(5, 9, ':', 0, 0, 'C');
        $pdf->Cell(85, 9, $form->d_o_b . ', ' . $form->age, 0, 1, 'L');

        // $pdf->Cell(50, 9, 'License Applied For', 0, 0);
        // $pdf->Cell(100, 9, ': ' . ($payment->license_name ?? 'N/A'), 0, 1);
        // $pdf->Cell(50, 9, 'Form Name', 0, 0);
        // $pdf->Cell(100, 9, ': ' . ($payment->form_name ?? 'N/A'), 0, 1);
        // $pdf->Ln(1);

        // Educational Details Section
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 10, '5. Details of Technical Qualification passed by the applicant', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Ln(1);
        // Table Header
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(40, 7, 'Education Level', 1, 0, 'C');
        $pdf->Cell(50, 7, 'Institution/School Name', 1, 0, 'C');
        $pdf->Cell(40, 7, 'Year of Passing', 1, 0, 'C');
        $pdf->Cell(50, 7, 'Percentage / Grade', 1, 1, 'C');  // End the row with a line break

        // Reset font for content
        $pdf->SetFont('helvetica', '', 10);

        $eduCount = count($education);
        $expCount = count($experience);

        foreach ($education as $edu) {
            $passingMonth = trim((string) ($edu->month_passing ?? ''));
            $passingYear = trim((string) ($edu->year_of_passing ?? ''));
            $passingLabel = trim($passingMonth . ' ' . $passingYear);
            $pdf->Cell(40, 7, $edu->educational_level, 1, 0, 'C');
            $pdf->Cell(50, 7, $edu->institute_name, 1, 0, 'C');
            $pdf->Cell(40, 7, ($passingLabel !== '' ? $passingLabel : '-'), 1, 0, 'C');
            $pdf->Cell(50, 7, $edu->percentage . '%', 1, 1, 'C');
        }

        // Add some space below the table
        $pdf->Ln(1);


        // Work Experience Section
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 10, '6. Details of Past and Present Experience', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);

        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(60, 7, 'Company Name / Contractor', 1, 0, 'C');
        $pdf->Cell(50, 7, 'Years of Experience', 1, 0, 'C');
        $pdf->Cell(50, 7, 'Designation', 1, 1, 'C'); // End the row with a line break

        // Reset font for content
        $pdf->SetFont('helvetica', '', 10);

        // Loop through work experience data and populate the table
        foreach ($experience as $exp) {
            $pdf->Cell(60, 7, ($exp->emp_cate ?? $exp->company_name ?? ''), 1, 0, 'C');
            $pdf->Cell(50, 7, ($exp->total_exp ?? $exp->experience ?? '') . ' years', 1, 0, 'C');
            $pdf->Cell(50, 7, $exp->designation, 1, 1, 'C');
        }
        $pdf->Ln(2); // Add space before the question
        $pdf->SetFont('helvetica', '', 10);
        // $pdf->writeHTML('<p style="margin-bottom:5px;">7.Have you made any previous application?</p>', true, false, true, false, '');
        // $pdf->writeHTML('<p>If so, state reference No and date:</p>', true, false, true, false, '');
        $pdf->Ln(2);


        $pdf->Cell(100, 5, '7. Have you made any previous application?', 0, 1, 'L');
        $pdf->Cell(135, 5, 'If so, state reference Number and date', 0, 0, 'L');

        if ($form->previously_number != 0 && $form->previously_date != 0) {
            $pdf->Cell(90, 5, ': ' . $form->previously_number . ', ' . $form->previously_date, 0, 1, 'L');
        } else {
            $pdf->Cell(90, 5, ': NO', 0, 1, 'L');
        }
        $pdf->Ln(1);

        $pdf->Cell(100, 5, '8. Do you possess Wireman C.C / Wireman Helper C.C issued by this Board?', 0, 1, 'L');
        $pdf->Cell(135, 5, 'If so, furnish the details and surrender the same.', 0, 0, 'L');

        if ($form->wireman_details) {
            $pdf->Cell(20, 9, '', 0, 0);
            $pdf->Cell(130, 9, ': ' . $form->wireman_details, 0, 1, 'L');
        } else {

            $pdf->Cell(130, 5, ': NO', 0, 1, 'L');
        }
        // Wireman Certificate

        // $pdf->Cell(100, 9, '8. Do you possess Wireman C.C / Wireman Helper C.C issued by this Board?', 0, 1, 'L');

        // $pdf->Cell(135, 9, 'If so, furnish the details and surrender the same.', 0, 1, 'L');

        // if ($form->wireman_details) {
        //     $pdf->Cell(20, 9, '', 0, 0);
        //     $pdf->Cell(130, 9, ': ' . $form->wireman_details, 0, 1, 'L');
        // } else {
        //     $pdf->Cell(20, 9, '', 0, 0);
        //     $pdf->Cell(130, 9, ': NO', 0, 1, 'L');
        // }

        $pdf->Ln(1);

        // Demand Draft Details
        $pdf->Cell(10, 9, '9.', 0, 0, 'L');
        $pdf->Cell(150, 9, 'Demand Draft Details', 0, 1, 'L');

        // Table Header
        $pdf->Cell(40, 10, 'Bank Name', 1, 0, 'C');
        $pdf->Cell(50, 10, 'Mode Of Payment', 1, 0, 'C');
        $pdf->Cell(40, 10, 'Payment Date', 1, 0, 'C');
        $pdf->Cell(50, 10, 'Transaction ID', 1, 1, 'C');

        // Payment details row
        $pdf->Cell(40, 10, 'State Bank of India', 1, 0, 'C');
        $pdf->Cell(50, 10, 'UPI', 1, 0, 'C');
        $pdf->Cell(40, 10, '25-02-2025', 1, 0, 'C');
        $pdf->Cell(50, 10, $payment->transaction_id ?? 'N/A', 1, 1, 'C');

        $pdf->Ln(3);

        $pdf->SetFont('helvetica', '', 10);

        // First line (auto-wrap)
        $pdf->MultiCell(0, 10, 'I hereby declare that all the details mentioned above are correct and true to the best of my knowledge.', 0, 'L');


        $pdf->Ln(1);

        // Second line (auto-wrap)
        $pdf->MultiCell(0, 10, 'I request that I may be granted a Supervisor Competency Certificate.', 0, 'L');

        $pdf->Ln(12);

        $pdf->Cell(20, 10, 'Place:', 0, 0);
        $pdf->Cell(60, 10, ': Chennai', 0, 0, 'L');
        $pdf->SetX(110);
        $pdf->Cell(60, 10, '', 0, 1, 'C');

        $pdf->Cell(20, 10, 'Date:', 0, 0);
        $pdf->Cell(60, 10, ': ' . date('d-m-Y'), 0, 0, 'L');


        // if (!empty($documents->upload_sign)) {
        //     $signPath = public_path($documents->upload_sign);

        //     if (file_exists($signPath)) {
        //         $pdf->Image($signPath, 132, 25, 30, 30);
        //     }
        // }
        $pdf->Cell(80, 10, 'Signature of the Candidate', 0, 1, 'R');

        $pdf->Output('Application_Details.pdf', 'I');
    }


    public function generateTamilPDF($newApplicationId)
    {

        $form = Mst_Form_s_w::where('application_id', $newApplicationId)->first();
        $education = Mst_education::where('application_id', $newApplicationId)->get();
        $experience = Mst_experience::where('application_id', $newApplicationId)->get();
        $applicant_photo = TnelbApplicantPhoto::where('application_id', $newApplicationId)->first();
        $payment = DB::table('payments')->where('application_id', $newApplicationId)->first();

        if (!$form) {
            return redirect()->back()->with('error', 'பதிவுகள் கிடைக்கவில்லை!');
        }
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf([
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'default_font_size' => 11,
            'default_font'      => 'marutham',
            'margin_left'       => 15,
            'margin_right'      => 15,
            'margin_top'        => 12,
            'margin_bottom'     => 12,
            'fontDir'  => array_merge($fontDirs, [public_path('fonts')]),
            'fontdata' => $fontData + [
                'marutham' => ['R' => 'Marutham.ttf'],
            ],
        ]);

        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;
        $mpdf->SetDefaultFont('marutham');
        $mpdf->SetFont('marutham', '', 12);
        $mpdf->SetTitle('தமிழ்நாடு அரசு மின்சார உரிமையாளர்கள் வாரியம் படிவம் ' . $form->license_name);

        $certificateText = match ($form->form_name) {
            'S' => 'மேற்பார்வையாளர் தகுதிச் சான்றிதழுக்கான விண்ணப்பம்',
            'W' => 'மின் கம்பியாளர் தகுதிச் சான்றிதழ் பெறுவதற்கான விண்ணப்பம்',
            'WH' => 'மின் கம்பி உதவியாளர் தகுதிச் சான்றிதழ் பெறுவதற்கான விண்ணப்பம்',
            default => 'Application for Competency Certificate',
        };

        $mpdf->WriteHTML('
        <style>
            body { font-family: marutham, sans-serif; font-size: 11pt; line-height: 1.5; color: #1a1a1a; margin: 0; padding: 0; }
            .eng {
                font-family: Arial, "Helvetica Neue", Helvetica, sans-serif !important;
                font-weight: 600;
                font-size: 10pt !important;
                line-height: 1.5 !important;
            }

            /* ── Page header ── */
            .hdr { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
            .hdr td { border: none; text-align: center; padding: 1px 0; line-height: 1.45; }

            /* ── Data tables ── */
            .tbl-data { width: 100%; border-collapse: collapse; margin-top: 3px; font-size: 9pt; }
            .tbl-data th { border: 1px solid #333; background: #eeeeee; font-weight: bold;
                           text-align: center; padding: 5px 4px; vertical-align: middle; white-space: nowrap; color: #1a1a1a; }
            .tbl-data td { border: 1px solid #333; text-align: center;
                           padding: 4px; vertical-align: middle; color: #1a1a1a; }
            .tbl-data td.td-left { text-align: left; }

            /* ── Certificate sub-detail ── */
            .cert-sub { width: 90%; border-collapse: collapse; margin: 4px 0 6px 0; font-size: 9pt; }
            .cert-sub td { border: 1px solid #aaa; padding: 5px 10px; text-align: center;
                           background: #f9f9f9; color: #1a1a1a; }

            /* ── Payment table ── */
            .pay-tbl { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 9.5pt; }
            .pay-tbl th { border: 1px solid #333; background: #eeeeee; font-weight: bold;
                          text-align: center; padding: 6px 8px; color: #1a1a1a; }
            .pay-tbl td { border: 1px solid #333; text-align: center; padding: 6px 8px; color: #1a1a1a; }
        </style>', HTMLParserMode::HEADER_CSS);

        $html = '
        <table class="hdr">
            <tr><td style="font-size:12pt; font-weight:bold;">தமிழ்நாடு அரசு</td></tr>
            <tr><td style="font-size:11pt; font-weight:bold;">மின்சார உரிமையாளர்கள் வாரியம்</td></tr>
            <tr><td>திரு.வி.கா. தொழிற்சாலை, கிண்டி, சென்னை – 600032.</td></tr>
            <tr><td style="font-size:11pt; font-weight:bold;">படிவம் - "' . $form->form_name . ($form->appl_type == 'R' ? '" - புதுப்பிப்பு' : '"') . '</td></tr>
            <tr><td>' . $certificateText . '</td></tr>
            <tr><td style="font-size:11pt; font-weight:bold;">விண்ணப்ப எண்: <span class="eng">' . $form->application_id . '</span></td></tr>
        </table>';

        // ── Photo HTML ──────────────────────────────────────────────────────
        if ($applicant_photo && file_exists(public_path($applicant_photo->upload_path))) {
            $photoHtml = '<img src="' . public_path($applicant_photo->upload_path)
                       . '" style="width:110px; height:130px; border:1px solid #555;">';
        } else {
            $photoHtml = '<div style="width:110px; height:130px; border:1px solid #999; display:inline-block; '
                       . 'text-align:center; vertical-align:middle; font-size:8pt; color:#777; padding-top:50px;">No Photo</div>';
        }

        // ── Items 1-4 (left) + photo (right) via fixed 2-col table ─────────
        $html .= '
        <table style="width:100%; border-collapse:collapse; margin-top:14px;">
          <tr>
            <td style="vertical-align:top; padding:0;">
              <table style="width:100%; border-collapse:collapse;">
                <tr>
                  <td style="width:28pt; font-weight:normal; vertical-align:top; padding:4px 4px 4px 0; color:#1a1a1a; white-space:nowrap;">1.</td>
                  <td style="width:38%; vertical-align:top; padding:4px 4px 4px 0; color:#1a1a1a;">விண்ணப்பதாரரின் பெயர்</td>
                  <td style="width:4%;  vertical-align:top; padding:4px 2px; text-align:center; color:#1a1a1a;">:</td>
                  <td style="vertical-align:top; padding:4px 0; color:#1a1a1a;"><span class="eng">' . e($form->applicant_name) . '</span></td>
                </tr>
                <tr>
                  <td style="width:28pt; font-weight:normal; vertical-align:top; padding:4px 4px 4px 0; color:#1a1a1a; white-space:nowrap;">2.</td>
                  <td style="vertical-align:top; padding:4px 4px 4px 0; color:#1a1a1a;">தகப்பனார் பெயர்</td>
                  <td style="vertical-align:top; padding:4px 2px; text-align:center; color:#1a1a1a;">:</td>
                  <td style="vertical-align:top; padding:4px 0; color:#1a1a1a;"><span class="eng">' . e($form->fathers_name) . '</span></td>
                </tr>
                <tr>
                  <td style="width:28pt; font-weight:normal; vertical-align:top; padding:4px 4px 4px 0; color:#1a1a1a; white-space:nowrap;">3.</td>
                  <td style="vertical-align:top; padding:4px 4px 4px 0; color:#1a1a1a;">விண்ணப்பதாரர் முகவரி</td>
                  <td style="vertical-align:top; padding:4px 2px; text-align:center; color:#1a1a1a;">:</td>
                  <td style="vertical-align:top; padding:4px 0; color:#1a1a1a;"><span class="eng">' . $this->formatAddressToThreeLines($form->applicants_address) . '</span></td>
                </tr>
                <tr>
                  <td style="width:28pt; font-weight:normal; vertical-align:top; padding:4px 4px 4px 0; color:#1a1a1a; white-space:nowrap;">4.</td>
                  <td style="vertical-align:top; padding:4px 4px 4px 0; color:#1a1a1a;">பிறந்த நாள், மாதம், ஆண்டு மற்றும் வயது</td>
                  <td style="vertical-align:top; padding:4px 2px; text-align:center; color:#1a1a1a;">:</td>
                  <td style="vertical-align:top; padding:4px 0; color:#1a1a1a;"><span class="eng">' . e($form->d_o_b) . ' (' . e($form->age) . ' years)</span></td>
                </tr>
              </table>
            </td>
            <td style="width:125pt; vertical-align:top; text-align:center; padding-left:8px;">' . $photoHtml . '</td>
          </tr>
        </table>';



        // ── 5. Education ─────────────────────────────────────────────────────
        $html .= '
        <table style="width:100%; border-collapse:collapse; margin-top:10px;">
          <tr>
            <td style="width:28pt; font-weight:bold; vertical-align:top; padding:4px 4px 4px 0; color:#1a1a1a; white-space:nowrap;">5.</td>
            <td style="vertical-align:top; padding:4px 0; color:#1a1a1a;">விண்ணப்பதாரியின் தொழில்நுட்ப தகுதி மற்றும் தேர்ச்சி பற்றிய விவரங்கள்</td>
          </tr>
        </table>
        <table class="tbl-data" style="margin-top:4px;">
          <tr>
            <th rowspan="2" style="width:6%;">வரிசை எண்</th>
            <th rowspan="2">கல்வி நிலை</th>
            <th rowspan="2">கல்லூரி / பள்ளி</th>
            <th colspan="2">தேர்ச்சி பெற்ற மாதம் மற்றும் ஆண்டு</th>
            <th rowspan="2">சான்றிதழ் எண்</th>
          </tr>
          <tr>
            <th>மாதம்</th>
            <th>ஆண்டு</th>
          </tr>';

        foreach ($education as $i => $edu) {
            $passingMonth = trim((string) ($edu->month_passing ?? ''));
            $passingYear  = trim((string) ($edu->year_of_passing ?? ''));
            $html .= '<tr>
                <td>' . ($i + 1) . '</td>
                <td><span class="eng">' . e($edu->educational_level) . '</span></td>
                <td class="td-left"><span class="eng">' . e($edu->institute_name) . '</span></td>
                <td><span class="eng">' . ($passingMonth !== '' ? $passingMonth : '-') . '</span></td>
                <td><span class="eng">' . ($passingYear  !== '' ? $passingYear  : '-') . '</span></td>
                <td><span class="eng">' . e($edu->certificate_no) . '</span></td>
            </tr>';
        }
        $html .= '</table>';

        // Section 6 Experience: skip for Form WH only (same as English PDF)
        if ($form->form_name !== 'WH') {
            $isFormS = strtoupper((string) $form->form_name) === 'S';

            // ── 6. Experience ────────────────────────────────────────────────────
            $html .= '
            <table style="width:100%; border-collapse:collapse; margin-top:8px;">
              <tr>
                <td style="width:28pt; font-weight:bold; vertical-align:top; padding:4px 4px 4px 0; color:#1a1a1a; white-space:nowrap;">6.</td>
                <td style="vertical-align:top; padding:4px 0; color:#1a1a1a;">பெற்றுள்ள முந்தைய மற்றும் தற்போதைய அனுபவங்களைப் பற்றிய விவரங்கள்</td>
              </tr>
            </table>
            <table class="tbl-data" style="margin-top:4px;">';

            $hasContractorRowTa = $isFormS && $experience->contains(function ($exp) {
                return strtolower(trim((string) ($exp->emp_type ?? ''))) === 'contractor';
            });

            if ($isFormS) {
                // Match Form-S layout (two-row header with From/To/Total)
                $html .= '
            <tr>
                <th rowspan="2">வரிசை எண்</th>
                <th rowspan="2">வேலைவாய்ப்பு வகை</th>
                <th rowspan="2">நிறுவனம் / அமைப்பு</th>
                <th colspan="3">அனுபவம்</th>
                <th rowspan="2">பதவி</th>'
                . ($hasContractorRowTa ? '<th rowspan="2">அறிவிப்பு தேதி</th>' : '') . '
            </tr>
            <tr>
                <th>தேதி முதல்</th>
                <th>தேதி வரை</th>
                <th>மொத்த ஆண்டுகள்</th>
            </tr>';
            } else {
                $html .= '
            <tr>
                <th rowspan="2" style="width:5%;">வரிசை எண்</th>
                <th rowspan="2">நிறுவனம்</th>
                <th colspan="3">அனுபவம் (ஆண்டுகள்)</th>
                <th rowspan="2">பதவி</th>
            </tr>
            <tr>
                <th>தேதி முதல்</th>
                <th>தேதி வரை</th>
                <th style="width:10%;">மொத்த ஆண்டுகள்</th>
            </tr>';
            }

            $hasExpDataTa = $experience->contains(function ($exp) use ($isFormS) {
                if ($isFormS) {
                    return trim((string) ($exp->emp_type ?? '')) !== ''
                        || trim((string) ($exp->emp_cate ?? $exp->company_name ?? '')) !== ''
                        || trim((string) ($exp->from_date ?? '')) !== ''
                        || trim((string) ($exp->to_date ?? '')) !== ''
                        || trim((string) ($exp->total_exp ?? $exp->experience ?? '')) !== ''
                        || trim((string) ($exp->designation ?? '')) !== '';
                }
                return trim((string) ($exp->emp_cate ?? $exp->company_name ?? '')) !== ''
                    || trim((string) ($exp->total_exp ?? $exp->experience ?? '')) !== ''
                    || trim((string) ($exp->designation ?? '')) !== '';
            });

            if (!$hasExpDataTa) {
                if ($isFormS) {
                    $extraNilCellTa = $hasContractorRowTa ? '<td>Nil</td>' : '';
                    $html .= '<tr><td>1</td><td>Nil</td><td>Nil</td><td>Nil</td><td>Nil</td><td>Nil</td><td>Nil</td>' . $extraNilCellTa . '</tr>';
                } else {
                    $html .= '<tr><td>1</td><td>Nil</td><td>Nil</td><td>Nil</td><td>Nil</td><td>Nil</td></tr>';
                }
            } else {
                foreach ($experience as $i => $exp) {
                    $employerName = $exp->emp_cate ?? $exp->company_name ?? 'Nil';
                    $experienceYears = $exp->total_exp ?? $exp->experience ?? 'Nil';
                    $designation = $exp->designation ?: 'Nil';

                    if ($isFormS) {
                        $employmentType = $exp->emp_type ?: 'Nil';
                        $fromDate = !empty($exp->from_date) ? format_date($exp->from_date) : 'Nil';
                        $toDate = !empty($exp->to_date) ? format_date($exp->to_date) : 'Nil';

                        $isContractor = strtolower(trim((string) $employmentType)) === 'contractor';
                        $intimationCellTa = '';
                        if ($hasContractorRowTa) {
                            if ($isContractor) {
                                $intimationDate = !empty($exp->intimation_date) ? format_date($exp->intimation_date) : 'Nil';
                                $intimationCellTa = '<td><span class="eng">' . e($intimationDate) . '</span></td>';
                            } else {
                                $intimationCellTa = '<td>-</td>';
                            }
                        }

                        $html .= '<tr>
                            <td>' . ($i + 1) . '</td>
                            <td><span class="eng">' . e(ucwords(str_replace('_', ' ', (string) $employmentType))) . '</span></td>
                            <td><span class="eng">' . e($employerName) . '</span></td>
                            <td><span class="eng">' . e($fromDate) . '</span></td>
                            <td><span class="eng">' . e($toDate) . '</span></td>
                            <td><span class="eng">' . e($experienceYears) . '</span></td>
                            <td><span class="eng">' . e($designation) . '</span></td>'
                            . $intimationCellTa . '
                        </tr>';
                    } else {
                        $fromDate = !empty($exp->from_date) ? format_date($exp->from_date) : 'Nil';
                        $toDate = !empty($exp->to_date) ? format_date($exp->to_date) : 'Nil';

                        $html .= '<tr>
                            <td>' . ($i + 1) . '</td>
                            <td><span class="eng">' . e($employerName) . '</span></td>
                            <td><span class="eng">' . e($fromDate) . '</span></td>
                            <td><span class="eng">' . e($toDate) . '</span></td>
                            <td><span class="eng">' . e($experienceYears) . '</span></td>
                            <td><span class="eng">' . e($designation) . '</span></td>
                        </tr>';
                    }
                }
            }

            $html .= '</table>';
        }

        // ── Compute ஆம்/இல்லை flags ──────────────────────────────────────────
        $value  = (empty($form->previously_number) || empty($form->previously_date)) ? 'இல்லை' : 'ஆம்';
        $certno = (empty($form->certificate_no)    || empty($form->certificate_date)) ? 'இல்லை' : 'ஆம்';

        $previousRefNoTa        = trim((string) ($form->previously_number ?? ''));
        $previousIssueDateTa    = !empty($form->previously_issue_date) ? format_date($form->previously_issue_date) : '-';
        $previousValidityDateTa = !empty($form->previously_date)       ? format_date($form->previously_date)       : '-';
        $certificateRefNoTa        = trim((string) ($form->certificate_no ?? ''));
        $certificateIssueDateTa    = !empty($form->certificate_issue_date) ? format_date($form->certificate_issue_date) : '-';
        $certificateValidityDateTa = !empty($form->certificate_date)       ? format_date($form->certificate_date)       : '-';

        $decryptedaadhar = $this->safeDecryptString($form->aadhaar);
        $decryptedaadhar = $decryptedaadhar ? preg_replace('/\s+/', '', $decryptedaadhar) : '';
        $masked  = strlen($decryptedaadhar) === 12 ? str_repeat('X', 8) . substr($decryptedaadhar, -4) : 'Invalid Aadhaar';
        $aadhaar = $masked ?: 'இல்லை';

        $decryptedPanTa = $this->safeDecryptString($form->pancard);
        $decryptedPanTa = $decryptedPanTa ? strtoupper(preg_replace('/[^A-Z0-9]/i', '', $decryptedPanTa)) : '';
        $maskedPanTa = strlen($decryptedPanTa) === 10 ? str_repeat('X', 6) . substr($decryptedPanTa, -4) : '';

        // ── Q rows — same 28pt num col alignment ────────────────────────────
        $numStyleTa  = 'style="width:28pt; font-weight:normal; vertical-align:top; padding:4px 4px 4px 0; color:#1a1a1a; white-space:nowrap;"';
        $textStyleTa = 'style="width:58%; vertical-align:top; padding:4px 4px 4px 0; color:#1a1a1a;"';
        $colStyleTa  = 'style="width:3%;  vertical-align:top; padding:4px 2px; text-align:center; color:#1a1a1a;"';
        $ansStyleTa  = 'style="vertical-align:top; padding:4px 0; color:#1a1a1a;"';

        $qRow = function(string $num, string $text, string $ans) use ($numStyleTa, $textStyleTa, $colStyleTa, $ansStyleTa) {
            return '
            <tr>
              <td ' . $numStyleTa  . '>' . $num . '.</td>
              <td ' . $textStyleTa . '>' . $text . '</td>
              <td ' . $colStyleTa  . '>:</td>
              <td ' . $ansStyleTa  . '>' . $ans . '</td>
            </tr>';
        };
        $certSubRowTa = function(string $refNo, string $issueDate, string $validityDate) use ($numStyleTa) {
            return '
            <tr>
              <td ' . $numStyleTa . '></td>
              <td colspan="3" style="padding:2px 0 6px 0;">
                <table class="cert-sub">
                  <tr>
                    <td><strong>Certificate No</strong><br><span class="eng">' . e($refNo) . '</span></td>
                    <td><strong>Date of Issue</strong><br><span class="eng">' . e($issueDate) . '</span></td>
                    <td><strong>Validity Date</strong><br><span class="eng">' . e($validityDate) . '</span></td>
                  </tr>
                </table>
              </td>
            </tr>';
        };

        $html .= '<table style="width:100%; border-collapse:collapse; margin-top:10px;">';

        if ($form->form_name == 'S') {
            $html .= $qRow('7', 'இதற்கு முன்பு மின் உதவியாளர் தகுதிச்சான்றிதழுக்கு விண்ணப்பித்துள்ளீர்களா? ஆம் என்றால் அதன் எண் மற்றும் தேதியை குறிப்பிடவும்.', e($value));
            if ($value === 'ஆம்') {
                $html .= $certSubRowTa($previousRefNoTa ?: '-', $previousIssueDateTa, $previousValidityDateTa);
            }
            $html .= $qRow('8', 'இந்த வாரியம் வழங்கிய மின்கம்பியாளர் தகுதி சான்றிதழ் / மேற்பார்வையாளர் தகுதி சான்றிதழ் உங்களிடம் உள்ளதா? இருந்தால், அதன் விவரங்களை வழங்கி, அதனை ஒப்படைக்கவும்.', e($certno));
            if ($certno === 'ஆம்') {
                $html .= $certSubRowTa($certificateRefNoTa ?: '-', $certificateIssueDateTa, $certificateValidityDateTa);
            }
            $html .= $qRow('9',  'ஆதார் எண்', e($aadhaar));
            $html .= $qRow('10', 'நிரந்தர கணக்கு எண்', e($maskedPanTa));
        } else {
            $no = ($form->form_name == 'WH') ? '6' : '7';
            if ($form->form_name == 'WH') {
                $html .= $qRow($no, 'இதற்கு முன்னாள் விண்ணப்பம் செய்து மின் கம்பி உதவியாளர் தகுதி சான்றிதழ் பெறப்பட்டுள்ளதா? ஆம் என்றால் அதன் எண் மற்றும் செல்லத்தக்க காலம் குறிப்பிடுக.', e($certno));
            } else {
                $html .= $qRow($no, 'இதற்கு முன்னாள் விண்ணப்பம் செய்து மின்கம்பியாளர் தகுதி சான்றிதழ் / மின் கம்பி உதவியாளர் தகுதி சான்றிதழ் பெறப்பட்டுள்ளதா? ஆம் என்றால் அதன் எண் மற்றும் செல்லத்தக்க காலம் குறிப்பிடுக', e($certno));
            }
            $aadhaarNo = ($form->form_name == 'WH') ? '7' : '8';
            $panNo     = (string) (((int) $aadhaarNo) + 1);
            $html .= $qRow($aadhaarNo, 'ஆதார் எண்', e($aadhaar));
            $html .= $qRow($panNo,     'நிரந்தர கணக்கு எண்', e($maskedPanTa));
        }

        $html .= '</table>';

        if ($payment) {
            $paymentType   = mb_strtoupper($payment->payment_mode ?? 'ONLINE', 'UTF-8');
            $transactionNo = mb_strtoupper($payment->transaction_id ?? 'N/A', 'UTF-8');
            $paymentDate   = \Carbon\Carbon::parse($payment->created_at)->format('d-m-Y');
            $paymentDate   = mb_strtoupper($paymentDate, 'UTF-8');
            $amountValue   = '₹ ' . ($payment->amount ?? 'N/A');
            $amountValue   = mb_strtoupper($amountValue, 'UTF-8');
            $statusValue   = mb_strtoupper($payment->payment_status ?? 'N/A', 'UTF-8');

            $html .= '
            <br><br>
            <table class="header-table" style="border:none; margin-bottom:2px; width:100%;">
                <tr><td style="font-family: Arial, sans-serif; font-size:11pt; font-weight:bold; text-align:center;">PAYMENT DETAILS</td></tr>
            </table>
            <table class="payment-table" border="1" cellspacing="0" cellpadding="0" style="width:100%; border:1px solid #000; border-collapse:collapse; table-layout:fixed; font-family: Arial, sans-serif; font-size:10pt;">
                <colgroup>
                    <col style="width:14%;" />
                    <col style="width:28%;" />
                    <col style="width:18%;" />
                    <col style="width:18%;" />
                    <col style="width:22%;" />
                </colgroup>
                <thead>
                    <tr>
                        <th style="border:1px solid #000; padding:6px 4px; text-align:center; background:#efefef; white-space:nowrap;">PAYMENT TYPE</th>
                        <th style="border:1px solid #000; padding:6px 4px; text-align:center; background:#efefef; white-space:nowrap;">TRANSACTION NUMBER</th>
                        <th style="border:1px solid #000; padding:6px 4px; text-align:center; background:#efefef; white-space:nowrap;">PAYMENT DATE</th>
                        <th style="border:1px solid #000; padding:6px 4px; text-align:center; background:#efefef; white-space:nowrap;">AMOUNT</th>
                        <th style="border:1px solid #000; padding:6px 4px; text-align:center; background:#efefef; white-space:nowrap;">PAYMENT STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border:1px solid #000; padding:6px 4px; text-align:center;">' . e($paymentType) . '</td>
                        <td style="border:1px solid #000; padding:6px 4px; text-align:center;">' . e($transactionNo) . '</td>
                        <td style="border:1px solid #000; padding:6px 4px; text-align:center;">' . e($paymentDate) . '</td>
                        <td style="border:1px solid #000; padding:6px 4px; text-align:center;">' . e($amountValue) . '</td>
                        <td style="border:1px solid #000; padding:6px 4px; text-align:center;">' . e($statusValue) . '</td>
                    </tr>
                </tbody>
            </table>';
        }
        

        // Place and Date at very bottom
        $html .= '<br><br>
        <table width="100%" style="border-collapse:collapse; margin-top:8px;">
            <tr>
                <td style="text-align:left; border:none;"><strong>இடம் :</strong> சென்னை</td>
                <td style="text-align:right; border:none;"><strong>தேதி :</strong> ' . date('d-m-Y') . '</td>
            </tr>
        </table>';

        $html = mb_convert_encoding($html, 'UTF-8', 'auto');
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('Application_Tamil.pdf', 'I'))
            ->header('Content-Type', 'application/pdf');
    }







    public function downloadPaymentReceipt($newApplicationId)
    {
      // dd($newApplicationId);
      // exit;
        $form= DB::table('tnelb_ea_applications')->where('application_id', $newApplicationId)->first()
                // ?? DB::table('tnelb_esa_applications')->where('application_id', $newApplicationId)->first()
                // ?? DB::table('tnelb_esb_applications')->where('application_id', $newApplicationId)->first()
                // ?? DB::table('tnelb_eb_applications')->where('application_id', $newApplicationId)->first()
                ?? Mst_Form_s_w::where('application_id', $newApplicationId)->first()
                ??TnelbFormP::where('application_id', $newApplicationId)->first();

        $license_name= DB::table('mst_licences')->where('form_code', $form->form_name)->first();

        $education = Mst_education::where('application_id', $newApplicationId)->get();
        $experience = Mst_experience::where('application_id', $newApplicationId)->get();
        // $documents = Mst_documents::where('application_id', $newApplicationId)->first();
        $payment = DB::table('payments')->where('application_id', $newApplicationId)->first();


        if (!$payment) {
            // dd('111');
            // exit;
            return redirect()->back()->with('error', 'Payment not found!');
        }

        $mpdf = new Mpdf(['default_font_size' => 10]);
        $mpdf->SetFont('arial', '', 10);

        $mpdf->SetTitle('TNELB Payment Receipt ' . $newApplicationId);

           $applType = strtoupper(trim($form->appl_type));
        $typeOfForm = ($applType === 'N') ? 'New Application' : 'Renewal Application';
        // $mpdf->SetTitle('TNELB Application License '. $form->license_name .' Form ' . $form->form_name);
       $html = '
            <style>
                .no_space {
                    margin: 3px;
                    line-height: 1.3;
                }
                .table-border td, 
                .table-border th {
                    
                    padding: 6px;
                    font-size: 14px;
                    text-align:left;
                }
            </style>

            <div style="text-align:center;">
                <h3 class="no_space" style="font-size:18px;">GOVERNMENT OF TAMIL NADU</h3>
                <h4 class="no_space" style="font-size:16px;">THE ELECTRICAL LICENSING BOARD</h4>
                <p class="no_space" style="font-size:14px;">Thiru.Vi.Ka. Industrial Estate, Guindy, Chennai – 600 032.</p>
                <h4 class="no_space" style="font-weight:500;">
                    FORM <span style="font-weight:bold;">"'. $form->form_name .'"</span>
                </h4>
                <p class="no_space" style="font-size:16px; font-weight:500;"> Application For "'. $license_name->licence_name .'"</p>
            </div>

            <hr style="margin:10px 0; border:0; border-top:1px solid #000;">
            <p class="section-title" style="font-size:15px; font-weight:bold; margin-top:5px; text-align:center;">Payment Details</p>
            <table class="table-border" style="border-collapse: collapse; width:100%; margin-top:10px;">

                <tr>
                    <th>Application ID</th>
                    <td>: '. $newApplicationId .'</td>
                </tr>

                <tr>
                    <th>Applicant Name </th>
                    <td>: '. $form->applicant_name .'</td>
                </tr>

                  <tr>
                    <th>Type of Form</th>
                    <td>: '. $typeOfForm .'</td>
                </tr>

                <tr>
                    <th>Bank Name</th>
                    <td>: State Bank of India</td>
                </tr>
                <tr>
                    <th>Mode of Payment</th>
                    <td>: UPI</td>
                </tr>
                <tr>
                    <th>Payment Date</th>
                  <td> : ' . \Carbon\Carbon::parse($payment->created_at)->format('d-m-Y') . '</td>

                </tr>
                <tr>
                    <th>Transaction ID</th>
                    <td> : ' . ($payment->transaction_id ?? "N/A") . '</td>
                </tr>
                <tr>
                    <th>Total Amount</th>
                    <td>: ₹ ' . ($payment->amount ?? "N/A") . '</td>
                </tr>
            </table>';

        $mpdf->WriteHTML($html);
        return response($mpdf->Output('', 'S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="payment_receipt.pdf"');
    }

//     public function downloadPaymentReceipt($newApplicationId)
//     {
//       // dd($newApplicationId);
//       // exit;
//         $form= DB::table('tnelb_ea_applications')->where('application_id', $newApplicationId)->first()
//                 ?? DB::table('tnelb_esa_applications')->where('application_id', $newApplicationId)->first()
//                 ?? DB::table('tnelb_esb_applications')->where('application_id', $newApplicationId)->first()
//                 ?? DB::table('tnelb_eb_applications')->where('application_id', $newApplicationId)->first()
//                 ?? Mst_Form_s_w::where('application_id', $newApplicationId)->first();

//         $license_name= DB::table('mst_licences')->where('form_code', $form->form_name)->first();
//         // $form = Mst_Form_s_w::where('application_id', $newApplicationId)->first();
// //  dd($license_name);
// //       exit;

//         $education = Mst_education::where('application_id', $newApplicationId)->get();
//         $experience = Mst_experience::where('application_id', $newApplicationId)->get();
//         $documents = Mst_documents::where('application_id', $newApplicationId)->first();
//         $payment = DB::table('payments')->where('application_id', $newApplicationId)->first();


//         if (!$payment) {
//             // dd('111');
//             // exit;
//             return redirect()->back()->with('error', 'Payment not found!');
//         }

//         $mpdf = new Mpdf(['default_font_size' => 10]);
//         $mpdf->SetFont('arial', '', 10);

//         $mpdf->SetTitle('TNELB Payment Receipt ' . $newApplicationId);

//            $applType = strtoupper(trim($form->appl_type));
// $typeOfForm = ($applType === 'N') ? 'New Application' : 'Renewal Application';
//         // $mpdf->SetTitle('TNELB Application License '. $form->license_name .' Form ' . $form->form_name);
//        $html = '
//             <style>
//                 .no_space {
//                     margin: 3px;
//                     line-height: 1.3;
//                 }
//                 .table-border td, 
//                 .table-border th {
                    
//                     padding: 6px;
//                     font-size: 14px;
//                     text-align:left;
//                 }
//             </style>

//             <div style="text-align:center;">
//                 <h3 class="no_space" style="font-size:18px;">GOVERNMENT OF TAMIL NADU</h3>
//                 <h4 class="no_space" style="font-size:16px;">THE ELECTRICAL LICENSING BOARD</h4>
//                 <p class="no_space" style="font-size:14px;">Thiru.Vi.Ka. Industrial Estate, Guindy, Chennai – 600 032.</p>
//                 <h4 class="no_space" style="font-weight:500;">
//                     FORM <span style="font-weight:bold;">"'. $form->form_name .'"</span>
//                 </h4>
//                 <p class="no_space" style="font-size:16px; font-weight:500;"> Application For "'. $license_name->licence_name .'"</p>
//             </div>

//             <hr style="margin:10px 0; border:0; border-top:1px solid #000;">
//             <p class="section-title" style="font-size:15px; font-weight:bold; margin-top:5px; text-align:center;">Payment Details</p>
//             <table class="table-border" style="border-collapse: collapse; width:100%; margin-top:10px;">

//                 <tr>
//                     <th>Application ID</th>
//                     <td>: '. $newApplicationId .'</td>
//                 </tr>

//                 <tr>
//                     <th>Applicant Name </th>
//                     <td>: '. $form->applicant_name .'</td>
//                 </tr>

//                   <tr>
//                     <th>Type of Form</th>
//                     <td>: '. $typeOfForm .'</td>
//                 </tr>

//                 <tr>
//                     <th>Bank Name</th>
//                     <td>: State Bank of India</td>
//                 </tr>
//                 <tr>
//                     <th>Mode of Payment</th>
//                     <td>: UPI</td>
//                 </tr>
//                 <tr>
//                     <th>Payment Date</th>
//                   <td> : ' . \Carbon\Carbon::parse($payment->created_at)->format('d-m-Y') . '</td>

//                 </tr>
//                 <tr>
//                     <th>Transaction ID</th>
//                     <td> : ' . ($payment->transaction_id ?? "N/A") . '</td>
//                 </tr>
//                 <tr>
//                     <th>Total Amount</th>
//                     <td>: ₹ ' . ($payment->amount ?? "N/A") . '</td>
//                 </tr>
//             </table>';

//         $mpdf->WriteHTML($html);
//         return response($mpdf->Output('', 'S'), 200)
//             ->header('Content-Type', 'application/pdf')
//             ->header('Content-Disposition', 'inline; filename="payment_receipt.pdf"');
//     }
    public function generateLicensePDF($newApplicationId)
    {
        $form = Mst_Form_s_w::where('application_id', $newApplicationId)->first();
        // var_dump($form->appl_type);die;


        // var_dump($form);die;
        $education = Mst_education::where('application_id', $newApplicationId)->get();
        $experience = Mst_experience::where('application_id', $newApplicationId)->get();
        $applicant_photo = TnelbApplicantPhoto::where('application_id', $newApplicationId)->first();

        if ($form->appl_type == 'R') {
            $license_details = DB::table('tnelb_renewal_license')->where('application_id', $newApplicationId)->first();
        }else{
            $license_details = DB::table('tnelb_license')->where('application_id', $newApplicationId)->first();
        }



        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font_size' => 10,
            'default_font' => 'helvetica',
            'margin_bottom' => 30
        ]);
    
        $mpdf->WriteHTML('
        <style>
            body { font-family: helvetica, sans-serif; font-size: 10pt; line-height: 1.4; }
            h3, h4, p { margin: 4px 0; }
            table { border-collapse: collapse; width: 100%; margin-top: 6px; }
            td, th { padding: 4px; vertical-align: top; }
            .label { width: 35%; text-align: left; font-weight: bold; }
            .value { width: 40%; text-align: left; }
            .tbl-bordered td, .tbl-bordered th { border: 1px solid #000; text-align: center; }
            .tbl-no-border td { border: none; padding-bottom: 12px; }
            .photo-cell { text-align:center; }
        </style>', HTMLParserMode::HEADER_CSS);
    
        $certificateText = match ($form->form_name) {
            'S' => 'Application for Competency Certificate for Supervisor',
            'W' => 'Application for Competency Certificate for Wireman',
            'WH' => 'Application for Competency Certificate for Wireman Helper',
            default => 'Application for Competency Certificate',
        };
        

                  
        $html  = '<div class="photo-cell">
        <img src="' . public_path($applicant_photo->upload_path) . '" width="120" height="150" style="border:1px solid #000;">
      </div>';

        $html .= '<h3 style="text-align:center;">Supervisor Competency Certificate</h3>';
        $html .= '<h4 style="text-align:center;">License No: <strong>' . $license_details->license_number . '</strong></h4>';

        $html .= '<table class="tbl-no-border" style="margin: 0 auto; width: 70%;">';
        $html .= '  <tr><td class="label">Date of Issue :</td><td class="value">'. format_date($license_details->issued_at) .'</td></tr>';
        $html .= '  <tr><td class="label">Name :</td><td class="value">'. $form->applicant_name .'</td></tr>';
        $html .= '  <tr><td class="label">Father\'s Name :</td><td class="value">'. $form->fathers_name .'</td></tr>';
        $html .= '  <tr><td class="label">Address :</td><td class="value">'. $form->applicants_address .'</td></tr>';
        $html .= '  <tr><td class="label">Date of Birth :</td><td class="value">'. $form->d_o_b .'</td></tr>';
        $html .= '  <tr><td class="label">Qualification :</td><td class="value"></td></tr>';
        $html .= '</table>';


        $html .= '<h4 style="text-align:center;">Renewal:</h4>';

        $html .= '
        <table style="width:100%; border:0;">
            <tr>
                <td class="label" style="text-align:left;">Form:'. format_date($license_details->issued_at) . '</td>
                <td class="label" style="text-align:right;">To:' . format_date($license_details->expires_at) .'</td>
            </tr>
        </table>
        <div style="margin-top: 50px;"><span>Employment Details: </span></div>';
        

        $mpdf->WriteHTML($html);

        $mpdf->SetHTMLFooter('
            <table width="100%" style="font-size: 12px;">
                <tr>
                    <td style="text-align:left;">Secretary, Electrical Licensing Board</td>
                    <td style="text-align:right;">President, Electrical Licensing Board</td>
                </tr>
            </table>
        ');
        return response($mpdf->Output('', 'S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="license.pdf"');

    }
}
