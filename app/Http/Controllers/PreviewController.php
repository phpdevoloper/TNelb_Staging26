<?php

namespace App\Http\Controllers;

use App\Models\Admin\Mst_equipment_tbl;
use App\Models\Tnelb_banksolvency_a;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        $authorisedSigns = Schema::hasTable('cl_forma_signs')
            ? DB::table('cl_forma_signs')->where('application_id', $application_id)->orderBy('row_index')->orderBy('id')->get()
            : collect();

        $payment = Schema::hasTable('payments')
            ? DB::table('payments')->where('application_id', $application_id)->orderByDesc('id')->first()
            : null;

        $clDigitization = Schema::hasTable('mapping_digi_cls')
            ? DB::table('mapping_digi_cls')->where('application_id', $application_id)->first()
            : null;

        $equipmentMasters = collect();
        if ($equipmentlist->isNotEmpty()) {
            $equipIds = $equipmentlist->pluck('equipment_id')->filter()->unique()->values();
            if ($equipIds->isNotEmpty() && class_exists(Mst_equipment_tbl::class)) {
                $equipmentMasters = Mst_equipment_tbl::whereIn('id', $equipIds)->get()->keyBy('id');
            }
        }

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
        
        $applicant_data->appl_type_code = strtoupper(trim((string) ($applicant_data->appl_type ?? '')));
        $applicant_data->appl_type_label = match ($applicant_data->appl_type_code) {
            'N' => 'New',
            'R' => 'Renewal',
            'D' => 'Digitisation',
            'A' => 'Alteration',
            default => ($applicant_data->appl_type_code !== '' ? $applicant_data->appl_type_code : '—'),
        };

        $applicantUser = DB::table('tnelb_registers')
            ->where('login_id', $applicant_data->login_id)
            ->first();

        foreach ($cl_ownership_table as $owner) {
            $owner->age_proof_url = $this->previewFileUrl($owner->age_proof ?? null);
            $owner->education_url = $this->previewFileUrl($owner->educational_proof ?? $owner->educ_qual_proof ?? null);
        }

        foreach ($staffdetails as $staff) {
            $staff->appointment_url = $this->previewFileUrl($staff->app_doc ?? $staff->appointment_letter ?? null);
            $staff->consent_url = $this->previewFileUrl($staff->cons_doc ?? $staff->consent_letter ?? null);
        }

        foreach ($authorisedSigns as $sign) {
            $sign->specimen_url = $this->previewFileUrl($sign->specimen_sign ?? null);
        }

        if ($banksolvency) {
            $banksolvency->bank_doc_url = $this->previewFileUrl($banksolvency->bank_doc ?? null);
        }

        if ($addressproof) {
            $addressproof->file_doc_url = $this->previewFileUrl($addressproof->file_doc ?? null);
        }

        foreach ($equipmentlist as $equipment) {
            $equipment->testreport_url = $this->previewFileUrl($equipment->testreport_file ?? null);
            $equipment->purchasereport_url = $this->previewFileUrl($equipment->purchasereport_file ?? null);
        }

        foreach ($attachments_cl as $attachment) {
            $attachment->file_doc_url = $this->previewFileUrl($attachment->file_doc ?? null);
        }

        $applicant_data->ownership_doc_url = $this->previewFileUrl($applicant_data->ownership_doc ?? null);

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
            'license_name',
            'license_name_data',
            'cl_ownership_table',
            'staffdetails',
            'otherstaffdetails',
            'qcQscStaffs_details',
            'maxQcStaff',
            'maxQcCertNo',
            'maxQcValidityDate',
            'equipmentlist',
            'equipmentMasters',
            'attachments_cl',
            'addressproof',
            'banksolvency',
            'authorisedSigns',
            'payment',
            'clDigitization',
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
