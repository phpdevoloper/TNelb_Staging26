<?php

namespace App\Http\Controllers;

use App\Models\Admin\Mst_equipment_tbl;
use App\Models\Tnelb_banksolvency_a;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class PreviewController extends BaseController
{
    public function index(string $application_id)
    {

        
        
        $applicant_data = DB::table('ccl_forma_meta')
        ->where('ccl_forma_meta.application_id', $application_id)
        ->select('*')
        ->first();
    
        if (!$applicant_data) {
            return abort(404, 'Applicant not found');
        }
      

        $formname = $applicant_data->form_name;

        $license_name = DB::table('mst_licences')->where('form_code', $formname)->first();

        $license_name_data = DB::table('mst_licences')->where('cert_licence_code', $applicant_data->license_name)->first();

        $cl_ownership_table = DB::table('cl_ownership_table')
            ->where('application_id', $application_id)
            ->orderBy('id', 'Desc')
            ->where('proprietor_flag', '1')
            ->get();

        $staffdetails = DB::table('cl_staff_tbl')
            ->where('application_id', $application_id)
            ->where('staff_flag', '1')
            ->orderBy('id')
            ->get();

        $otherstaffdetails = DB::table('cl_staff_tbl')
            ->where('application_id', $application_id)
            ->whereNotIn('staff_category', ['QC', 'QSC'])
            ->where('staff_flag', '1')
            ->orderBy('id')
            ->get();

        $qcQscStaffs_details = $staffdetails
            ->filter(function ($staff) {
                return in_array(
                    strtoupper(trim($staff->staff_category)),
                    ['QC', 'QSC']
                );
            });


        $documents = DB::table('tnelb_applicant_doc_A')
            ->where('application_id', $application_id)
            ->select('*')

            ->first();



        $banksolvency = Tnelb_banksolvency_a::where('application_id', $application_id)
            ->where('status', '1')
            ->first();

        $equiplist = Mst_equipment_tbl::where('equip_licence_name', 8)
            ->where('status', 1)
            ->orderBy('id')
            ->get();

        $equipmentlist = DB::table('tnelb_equimentsuser_cl')
            // ->where('login_id', Auth::user()->login_id)
            ->where('application_id', $application_id)
            ->get();

        $attachments_cl = DB::table('tnelb_attachments_cl')

            ->where('application_id', $application_id)
            ->get();

        $addressproof = DB::table('tnelb_addressproof_cl')

            ->where('application_id', $application_id)
            ->first();



        $Qcstaffs = DB::table('tnelb_ea_qc_models')->where('application_id', $application_id)->orderBy('id', 'ASC')->get();


        $maxQcStaff = $qcQscStaffs_details
            ->filter(function ($staff) {
                return !empty($staff->staff_cc_validity_to);
            })
            ->sortByDesc(function ($staff) {
                return \Carbon\Carbon::parse($staff->staff_cc_validity_to);
            })
            ->first();

        $maxQcCertNo = $maxQcStaff?->staff_cc_no;
        $maxQcValidityDate = $maxQcStaff?->staff_cc_validity_to;
        
        $applicant_data->appl_type = match(trim($applicant_data->appl_type)) {
            'N' => 'New',
            'R' => 'Renewal',
            'D' => 'Digitisation',
            'A' => 'Alteration',
            default => $applicant_data->appl_type,
        };

        $applicantUser = DB::table('tnelb_registers')
            ->where('login_id', $applicant_data->login_id)
            ->first();

        foreach ($cl_ownership_table as $owner) {
            $owner->age_proof_url = $this->previewFileUrl($owner->age_proof ?? null);
            $owner->education_url = $this->previewFileUrl($owner->educational_proof ?? null);
        }

        $documentCards = $this->previewDocumentCards(
            $applicant_data,
            $documents,
            $cl_ownership_table,
            $staffdetails,
            $addressproof,
            $banksolvency,
            $attachments_cl,
            $equipmentlist
        );

        $printRoute = match (strtoupper(trim((string) ($applicant_data->form_name ?? '')))) {
            'A' => 'generatea.pdf',
            'B' => 'generateb.pdf',
            'SB' => 'generatesb.pdf',
            'SA' => 'generatesa.pdf',
            default => null,
        };
        $printUrl = $printRoute
            ? route($printRoute, ['login_id' => $applicant_data->application_id])
            : null;

        return view('user_login.contractor.application_preview', compact(
            'applicant_data',
            'license_name_data',
            'cl_ownership_table',
            'staffdetails',
            'otherstaffdetails',
            'qcQscStaffs_details',
            'maxQcStaff',
            'maxQcCertNo',
            'maxQcValidityDate',
            'equipmentlist',
            'attachments_cl',
            'addressproof',
            'Qcstaffs',
            'applicantUser',
            'documentCards',
            'printUrl'
        ));
    }

    private function previewDocumentCards($applicant, $documents, $owners, $staffdetails, $addressproof, $banksolvency, $attachments, $equipmentlist): array
    {
        $push = function (array &$bucket, ?string $label, ?string $path) {
            $url = $this->previewFileUrl($path);
            if ($url === null || $label === null || $label === '') {
                return;
            }
            foreach ($bucket as $existing) {
                if ($existing['url'] === $url) {
                    return;
                }
            }
            $bucket[] = ['label' => $label, 'url' => $url];
        };

        $aadhaar = [];
        $address = [];
        $education = [];
        $experience = [];
        $other = [];

        $push($aadhaar, 'Aadhaar Card', $documents?->aadhaar_doc ?? null);
        $push($aadhaar, 'Aadhaar Card', $applicant->aadhaar_doc ?? null);

        $push($address, 'Address Proof', $addressproof?->file_doc ?? null);

        foreach ($owners as $owner) {
            $who = trim((string) ($owner->proprietor_name ?? 'Owner'));
            $push($education, 'Education Certificate — ' . $who, $owner->educational_proof ?? null);
        }

        foreach ($staffdetails as $staff) {
            $who = trim((string) ($staff->staff_name ?? 'Staff'));
            foreach (['appointment_letter', 'consent_letter', 'app_doc', 'cons_doc'] as $column) {
                $value = $staff->{$column} ?? null;
                if (!is_string($value) || !preg_match('/\.pdf|\/|\\\\/i', $value)) {
                    continue;
                }
                $push($experience, 'Experience Certificate — ' . $who, $value);
            }
        }

        $push($other, 'Ownership Document', $applicant->ownership_doc ?? null);
        $push($other, 'Bank Solvency', $banksolvency?->bank_doc ?? null);
        $push($other, 'PAN Card', $documents?->pancard_doc ?? $applicant->pan_doc ?? null);
        $push($other, 'GST Document', $documents?->gst_doc ?? $applicant->gst_doc ?? null);

        $attachmentLabels = [
            'separatesheet' => 'Separate Sheet',
            'ccholders' => 'CC Holders',
            'criminaloffence' => 'Criminal Offence',
            'consentletter' => 'Consent Letter',
            'specimensignature' => 'Specimen Signature',
        ];
        foreach ($attachments as $attachment) {
            $type = strtolower(trim((string) ($attachment->type ?? '')));
            $label = $attachmentLabels[$type] ?? 'Other Document';
            $push($other, $label, $attachment->file_doc ?? null);
        }

        foreach ($equipmentlist as $equipment) {
            $serial = trim((string) ($equipment->serial_no ?? ''));
            $suffix = $serial !== '' ? ' (' . $serial . ')' : '';
            $push($other, 'Test Report' . $suffix, $equipment->testreport_file ?? null);
            $push($other, 'Purchase Report' . $suffix, $equipment->purchasereport_file ?? null);
        }

        return [
            ['key' => 'aadhaar', 'title' => 'Aadhaar Card', 'files' => $aadhaar],
            ['key' => 'address', 'title' => 'Address Proof', 'files' => $address],
            ['key' => 'education', 'title' => 'Education Certificate', 'files' => $education],
            ['key' => 'experience', 'title' => 'Experience Certificate', 'files' => $experience],
            ['key' => 'other', 'title' => 'Other Documents', 'files' => $other],
        ];
    }

    private function previewFileUrl(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }

        if (!str_contains($path, '/') && !str_contains($path, '\\') && !preg_match('/\.pdf$/i', $path)) {
            try {
                $path = Crypt::decryptString($path);
            } catch (\Throwable $e) {
                return null;
            }
        }

        $path = str_replace('\\', '/', trim($path));
        $path = preg_replace('#(?<!:)/{2,}#', '/', $path) ?? $path;

        if (preg_match('#^age_proof(?!/)(.+)$#', $path, $matches)) {
            $path = 'age_proof/' . ltrim($matches[1], '/');
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }
}
