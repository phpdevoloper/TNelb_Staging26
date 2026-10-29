<?php

namespace App\Http\Controllers;

use App\Models\Tnelb_Temp_Tbl;
use Illuminate\Http\Request;
use Psy\Util\Str;


use Illuminate\Support\Facades\Storage;

use App\Models\TnelbTempUploadedDocument;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
// use Intervention\Image\File;
use Symfony\Component\HttpFoundation\Session\Session;
// use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\File;


class DocumentUploadController extends Controller
{

    public function uploadownershipdeed_ch(Request $request)
    {

        // -------------------------
        // DYNAMIC FILE FIELD
        // -------------------------
        $fileField = $request->document_category;



        // For array input like qual_proof[]
        if (str_ends_with($fileField, '[]')) {
            $fileField = rtrim($fileField, '[]');
        }
        //  dd($fileField);
        //         exit;
        // -------------------------
        // VALIDATION
        // -------------------------
        $request->validate([
            // $fileField          => 'required|file|mimes:pdf|max:250',
            'module'            => 'required',
            'document_category' => 'required',
            'document_sub_category' => 'required',
            'license_name'      => 'required',
            'form_name'         => 'required',
            'ownership_type'    => 'required',
        ]);

        try {
            $file = $request->file($fileField);

            // -------------------------
            // SESSION APPLICATION ID
            // -------------------------
            if (!session()->has('application_id')) {
                $applicationId = 'APP_' . $request->license_name . date('dmY');
                session(['application_id' => $applicationId]);
            } else {
                $applicationId = session('application_id');
            }

            $loginId = auth()->user()->login_id;

            // -------------------------
            // PATH HANDLING
            // -------------------------
            $dbFilePath = DocPathController::getPath($request);
            $folderPath = public_path($dbFilePath);

            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, 0755, true);
            }

            // -------------------------
            // FILE NAME
            // -------------------------
            $date = Carbon::now()->format('Y_m_d');
            $random = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

            $fileName = $date . '_' . $loginId . '_' . $random . '_' .
                strtoupper($request->document_category) . '.pdf';

            // dd( $folderPath);
            // exit;

            $file->move($folderPath, $fileName);

            // -------------------------
            // INSERT TEMP RECORD
            // -------------------------
            DB::table('tnelb_temp_uploaded_documents')->insert([
                'login_id'              => $loginId,
                'application_id'        => $applicationId,
                'form_name'             => $request->form_name,
                'license_name'          => $request->license_name,
                'module'                => $request->module,
                'ownership_type'        => $request->ownership_type,
                'document_category'     => $request->document_category,
                'document_sub_category' => $request->document_sub_category,
                'file_name'             => $fileName,
                'file_path'             => $dbFilePath,
                'uploaded_at'           => DB::raw('NOW()'),
                'is_final'              => '0',
                'created_at'            => DB::raw('NOW()'),
                'updated_at'            => DB::raw('NOW()'),
            ]);

            return response()->json([
                'status'    => 'success',
                'file_url'  => asset($dbFilePath . $fileName),
                'file_name' => $fileName,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Upload failed. Please try again.'
            ], 500);
        }
    }



    public function uploadownershipdeed_json(Request $request)
    {
        $jsonHandle = null;

        try {
            // 1. Check uploaded files.
            $fileInputs = $request->files->all();
            if (count($fileInputs) === 0) {
                return response()->json([
                    'status' => 'error',
                    'msg' => 'No file received',
                ], 422);
            }

            $fileField = array_key_first($fileInputs);
            $fileValue = $fileInputs[$fileField];

            // 2. Validate request and PDFs (maximum 250 KB per PDF).
            $rules = [
                'module' => 'required',
                'document_category' => 'required',
                'license_name' => 'required',
                'form_name' => 'required',
            ];

            if (is_array($fileValue)) {
                $rules[$fileField] = 'required|array';
                $rules[$fileField . '.*'] = 'required|file|mimes:pdf|max:250';
            } else {
                $rules[$fileField] = 'required|file|mimes:pdf|max:250';
            }

            $request->validate($rules);
            $files = is_array($fileValue) ? $fileValue : [$fileValue];

            // 3. Resolve application and user.
            if (!session()->has('application_id')) {
                $applicationId = 'APP_' . $request->license_name . now()->format('dmY');
                session(['application_id' => $applicationId]);
            } else {
                $applicationId = session('application_id');
            }

            $loginId = auth()->user()->login_id;

            // 4. Keep the PDF files in the existing upload folder.
            $pathInfo = DocPathController::getPath($request);
            $dbFilePath = $pathInfo->filepath_pro;
            $moduleCode = strtoupper($pathInfo->module_code);
            $folderPath = public_path($dbFilePath);


            // dd($folderPath); exit;

            if (!\File::isDirectory($folderPath)) {
                \File::makeDirectory($folderPath, 0755, true, true);
            }

            // 5. JSON file replaces BOTH tnelb_temp_uploaded_documents and
            //    cl_temp_mapping database writes.
            $jsonDirectory = storage_path('app/tnelb_json');
            if (!\File::exists($jsonDirectory)) {
                \File::makeDirectory($jsonDirectory, 0755, true, true);
            }

            $jsonPath = $jsonDirectory . DIRECTORY_SEPARATOR . 'upload_store.json';
            $jsonHandle = fopen($jsonPath, 'c+');
            if ($jsonHandle === false) {
                throw new \RuntimeException('Unable to open JSON upload store.');
            }

            // Lock the JSON store while reading and updating it, preventing simultaneous
            // uploads from overwriting one another.
            if (!flock($jsonHandle, LOCK_EX)) {
                throw new \RuntimeException('Unable to lock JSON upload store.');
            }

            rewind($jsonHandle);
            $jsonContents = stream_get_contents($jsonHandle);
            $store = json_decode($jsonContents ?: '', true);

            if (!is_array($store)) {
                $store = ['documents' => [], 'mappings' => []];
            }
            $store['documents'] = isset($store['documents']) && is_array($store['documents'])
                ? $store['documents'] : [];
            $store['mappings'] = isset($store['mappings']) && is_array($store['mappings'])
                ? $store['mappings'] : [];

            $uploadedFiles = [];
            $counter = 1;

            foreach ($files as $file) {
                $originalName = $file->getClientOriginalName();
                $subCategory = (string) $request->input('document_sub_category', '');
                $rowIndex = is_numeric($request->input('row_index'))
                    ? (int) $request->input('row_index') : null;
                $equipCode = is_numeric($request->input('equip_code'))
                    ? $request->input('equip_code') : null;
                $qcCode = is_numeric($request->input('qc_code'))
                    ? $request->input('qc_code') : null;

                // Match the same document identity rules used by the old DB query.
                $existingIndex = null;
                foreach ($store['documents'] as $index => $document) {
                    if (
                        (string) ($document['login_id'] ?? '') !== (string) $loginId
                        || (string) ($document['application_id'] ?? '') !== (string) $applicationId
                        || (string) ($document['module'] ?? '') !== (string) $request->module
                        || (string) ($document['form_name'] ?? '') !== (string) $request->form_name
                        || (string) ($document['document_category'] ?? '') !== (string) $request->document_category
                    ) {
                        continue;
                    }

                    $matches = true;
                    switch ($subCategory) {
                        case 'OED':
                        case 'AP':
                            $matches = ($document['document_sub_category'] ?? null) === $subCategory
                                && (string) ($document['ownership_type'] ?? '') === (string) $request->input('ownership_type', '')
                                && ($document['row_index'] ?? null) === $rowIndex;
                            break;
                        case 'ED':
                            $matches = ($document['document_sub_category'] ?? null) === 'ED'
                                && (string) ($document['equip_code'] ?? '') === (string) ($equipCode ?? '');
                            break;
                        case 'QD':
                            $matches = ($document['document_sub_category'] ?? null) === 'QD'
                                && (string) ($document['qc_code'] ?? '') === (string) ($qcCode ?? '');
                            break;
                        case 'OHD':
                            $matches = (string) ($document['ownership_type'] ?? '') === (string) $request->input('ownership_type', '');
                            break;
                        case 'QAD':
                        case 'QCD':
                        case 'SS':
                            $matches = ($document['document_sub_category'] ?? null) === $subCategory
                                && ($document['row_index'] ?? null) === $rowIndex;
                            break;
                        default:
                            $matches = ($document['document_sub_category'] ?? null) === $subCategory;
                            break;
                    }

                    if ($matches) {
                        $existingIndex = $index;
                        break;
                    }
                }

                $now = now()->toDateTimeString();

                if ($existingIndex !== null) {
                    // UPDATE: preserve the stored filename, as the previous code did.
                    $existing = $store['documents'][$existingIndex];
                    $fileName = $existing['file_name'];
                    $newStatus = ((string) ($existing['is_final'] ?? '0') === '1') ? '2' : '0';

                    $oldPath = public_path(($existing['file_path'] ?? '') . $fileName);
                    if (\File::exists($oldPath)) {
                        \File::delete($oldPath);
                    }

                    $file->move($folderPath, $fileName);

                    $store['documents'][$existingIndex] = array_merge($existing, [
                        'ownership_type' => $request->input('ownership_type'),
                        'document_sub_category' => $subCategory,
                        'original_pdfname' => $originalName,
                        'file_path' => $dbFilePath,
                        'is_final' => $newStatus,
                        'uploaded_at' => $now,
                        'updated_at' => $now,
                    ]);
                } else {
                    // INSERT: retain the original filename conventions for all categories.
                    $date = now()->format('Ymd');
                    $time = now()->timezone('Asia/Kolkata')->format('Hi');
                    $formCode = $request->input('form_code');
                    $ownershipType = (string) $request->input('ownership_type', '');

                    if (in_array($subCategory, ['OED', 'AP'], true)) {
                        $fileName = $date . '_' . $time . '_' . $loginId . '_L' . $formCode . '_'
                            . strtoupper($ownershipType) . $moduleCode . $rowIndex . '.pdf';
                    } elseif (in_array($subCategory, ['QAD', 'QCD', 'SS'], true)) {
                        $fileName = $date . '_' . $time . '_' . $loginId . '_L' . $formCode . '_'
                            . $moduleCode . $rowIndex . '.pdf';
                    } elseif ($subCategory === 'OHD') {
                        $fileName = $date . '_' . $time . '_' . $loginId . '_L' . $formCode . '_'
                            . $ownershipType . '_' . $moduleCode . '.pdf';
                    } elseif ($subCategory === 'ED') {
                        $fileName = $date . '_' . $time . '_' . $loginId . '_L' . $formCode . '_'
                            . $ownershipType . '_' . $moduleCode . $equipCode . '.pdf';
                    } elseif ($subCategory === 'QD') {
                        $fileName = $date . '_' . $time . '_' . $loginId . '_L' . $formCode
                            . $moduleCode . $qcCode . '.pdf';
                    } else {
                        $fileName = $date . '_' . $time . '_' . $loginId . '_L' . $formCode . '_'
                            . $moduleCode . '.pdf';
                    }

                    // Avoid accidental overwrites if two new uploads generate the same name.
                    if (\File::exists($folderPath . DIRECTORY_SEPARATOR . $fileName)) {
                        $name = pathinfo($fileName, PATHINFO_FILENAME);
                        $fileName = $name . '_' . \Illuminate\Support\Str::uuid() . '.pdf';
                    }

                    $file->move($folderPath, $fileName);

                    $nextId = 1;
                    foreach ($store['documents'] as $document) {
                        $nextId = max($nextId, ((int) ($document['id'] ?? 0)) + 1);
                    }

                    $store['documents'][] = [
                        'id' => $nextId,
                        'login_id' => $loginId,
                        'application_id' => $applicationId,
                        'form_name' => $request->form_name,
                        'license_name' => $request->license_name,
                        'module' => $request->module,
                        'ownership_type' => $request->input('ownership_type'),
                        'document_category' => $request->document_category,
                        'document_sub_category' => $subCategory,
                        'original_pdfname' => $originalName,
                        'file_name' => $fileName,
                        'file_path' => $dbFilePath,
                        'ownership_count' => $counter,
                        'appl_type' => $request->input('appl_type'),
                        'row_index' => $rowIndex,
                        'equip_code' => $equipCode,
                        'qc_code' => $qcCode,
                        'uploaded_at' => $now,
                        'is_final' => '0',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    // JSON equivalent of cl_temp_mapping. Avoid duplicate mapping entries.
                    $mappingExists = false;
                    foreach ($store['mappings'] as $mapping) {
                        if (
                            (string) ($mapping['login_id'] ?? '') === (string) $loginId
                            && (string) ($mapping['temp_app_id'] ?? '') === (string) $applicationId
                            && (string) ($mapping['form_name'] ?? '') === (string) $request->form_name
                            && (string) ($mapping['license_name'] ?? '') === (string) $request->license_name
                        ) {
                            $mappingExists = true;
                            break;
                        }
                    }

                    if (!$mappingExists) {
                        $store['mappings'][] = [
                            'login_id' => $loginId,
                            'temp_app_id' => $applicationId,
                            'application_id' => '',
                            'form_name' => $request->form_name,
                            'license_name' => $request->license_name,
                            'date' => now()->toDateString(),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                $counter++;
                $uploadedFiles[] = [
                    'file_name' => $fileName,
                    'file_url' => asset($dbFilePath . $fileName),
                ];
            }

            // Save JSON safely while still holding the exclusive lock.
            $encoded = json_encode($store, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($encoded === false) {
                throw new \RuntimeException('Unable to encode upload metadata as JSON.');
            }

            rewind($jsonHandle);
            if (!ftruncate($jsonHandle, 0) || fwrite($jsonHandle, $encoded) === false) {
                throw new \RuntimeException('Unable to write upload metadata JSON.');
            }
            fflush($jsonHandle);
            flock($jsonHandle, LOCK_UN);
            fclose($jsonHandle);
            $jsonHandle = null;

            return response()->json([
                'status' => 'success',
                'files' => $uploadedFiles,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        } finally {
            if (is_resource($jsonHandle)) {
                flock($jsonHandle, LOCK_UN);
                fclose($jsonHandle);
            }
        }
    }

    public function uploadownershipdeed(Request $request)
    {
        try {

        // ======================================================
        // 1. VALIDATE FILE EXISTENCE
        // ======================================================
// dd($request->all()); exit;
        $fileInputs = $request->files->all();

            $fileInputs = $request->files->all();

            if (empty($fileInputs)) {
                return response()->json([
                    'status' => 'error',
                    'msg'    => 'No file received',
                ], 422);
            }

            $fileField = array_key_first($fileInputs);
            $fileValue = $fileInputs[$fileField];

            // ======================================================
            // 2. VALIDATION
            // ======================================================

            $rules = [
                'module'            => 'required',
                'document_category' => 'required',
                'license_name'      => 'required',
                'form_name'         => 'required',
            ];

            if (is_array($fileValue)) {
                $rules[$fileField] = 'required|array';
                $rules[$fileField . '.*'] = 'required|file|mimes:pdf|max:250';
            } else {
                $rules[$fileField] = 'required|file|mimes:pdf|max:250';
            }

            $request->validate($rules);

            $files = is_array($fileValue)
                ? $fileValue
                : [$fileValue];

            // ======================================================
            // 3. RECORD ID
            // Empty record_id = temporary upload
            // Existing record_id = update actual tables
            // ======================================================

            $recordId = trim((string) $request->input('record_id', ''));

            $recordId = $recordId !== '' && $recordId !== 'null'
                ? $recordId
                : null;

            // ======================================================
            // 4. APPLICATION ID
            // Empty record_id = use session ID
            // Present record_id = use existing application ID
            // ======================================================

            if ($recordId !== null) {

                $applicationId = $recordId;
            } else {

                if (!session()->has('application_id')) {
                    $applicationId = session()->getId();

                    session(['application_id' => $applicationId]);
                } else {
                    $applicationId = session('application_id');
                }
            }

            // ======================================================
            // 5. LOGIN ID
            // ======================================================

            $loginId = auth()->user()->login_id;

            // ======================================================
            // 6. FILE PATH
            // ======================================================

            $pathData = DocPathController::getPath($request);

            $dbFilePath = trim($pathData->filepath_pro, '/\\');

            $folderPath = public_path($dbFilePath);

            if (!\File::exists($folderPath)) {
                \File::makeDirectory($folderPath, 0755, true);
            }

            $documentCategory = trim(
                (string) $request->input('document_category')
            );

            $subCategory = trim(
                (string) $request->input('document_sub_category', '')
            );

            $uploadedFiles = [];
            $counter = 1;

            // ======================================================
            // 7. PROCESS EACH FILE
            // ======================================================

            foreach ($files as $file) {

                $originalName = $file->getClientOriginalName();

                $equipCode = is_numeric($request->equip_code)
                    ? $request->equip_code
                    : null;

                $qcCode = is_numeric($request->qc_code)
                    ? $request->qc_code
                    : null;

                $rowIndex = is_numeric($request->row_index)
                    ? (int) $request->row_index
                    : null;

                // ==================================================
                // 8. FIND EXISTING TEMP DOCUMENT
                // Only when record_id is empty
                // ==================================================

                $existing = null;

                if ($recordId === null) {

                    $existingQuery = DB::table('tnelb_temp_uploaded_documents')
                        ->where('login_id', $loginId)
                        ->where('application_id', $applicationId)
                        ->where('module', $request->module)
                        ->where('form_name', $request->form_name)
                        ->where('document_category', $documentCategory);

                    if ($subCategory !== '') {
                        $existingQuery->where(
                            'document_sub_category',
                            $subCategory
                        );
                    }

                    if (in_array($subCategory, ['OED', 'AP'])) {
                        $existingQuery
                            ->where('ownership_type', $request->ownership_type)
                            ->where('row_index', $rowIndex);
                    }

                    if (in_array($subCategory, ['QAD', 'QCD', 'SS'])) {
                        $existingQuery->where('row_index', $rowIndex);
                    }

                    if ($subCategory === 'ED') {
                        $existingQuery->where('equip_code', $equipCode);
                    }

                    if ($subCategory === 'QD') {
                        $existingQuery->where('qc_code', $qcCode);
                    }

                    if ($subCategory === 'OHD') {
                        $existingQuery->where(
                            'ownership_type',
                            $request->ownership_type
                        );
                    }

                    $existing = $existingQuery
                        ->orderByDesc('id')
                        ->first();
                }

                // ==================================================
                // 9. FIND CURRENT MAIN-TABLE FILE WHEN record_id EXISTS
                // Reuse its filename so the replacement keeps the same name.
                // ==================================================

                $oldStoredPath = null;

                if ($recordId !== null) {
                    switch ($documentCategory) {
                        case 'ownership_doc':
                            $oldStoredPath = DB::table('ccl_forma_meta')
                                ->where('application_id', $applicationId)
                                ->value('ownership_doc');
                            break;

                        case 'bank_doc':
                            $oldStoredPath = DB::table('tnelb_banksolvency_a')
                                ->where('application_id', $applicationId)
                                ->value('bank_doc');
                            break;

                        case 'Address_proof':
                            $oldStoredPath = DB::table('tnelb_addressproof_cl')
                                ->where('application_id', $applicationId)
                                ->value('file_doc');
                            break;

                        case 'other_doc':
                            $oldStoredPath = DB::table('tnelb_attachments_cl')
                                ->where('application_id', $applicationId)
                                ->where('type', $request->ownership_type)
                                ->value('file_doc');
                            break;

                        case 'instrument_test_report':
                            if (!$equipCode) {
                                throw new \RuntimeException('Equipment ID is required for the test report.');
                            }
                            $oldStoredPath = DB::table('tnelb_equimentsuser_cl')
                                ->where('application_id', $applicationId)
                                ->where('equipment_id', $equipCode)
                                ->value('testreport_file');
                            break;

                        case 'instrument_purchase_report':
                            if (!$equipCode) {
                                throw new \RuntimeException('Equipment ID is required for the purchase report.');
                            }
                            $oldStoredPath = DB::table('tnelb_equimentsuser_cl')
                                ->where('application_id', $applicationId)
                                ->where('equipment_id', $equipCode)
                                ->value('purchasereport_file');
                            break;

                        // QC / QSC staff appointment and consent documents
                        case 'app_doc':
                        case 'cons_doc':
                            if ($rowIndex === null) {
                                throw new \RuntimeException(
                                    'Row index is required for QC/QSC staff documents.'
                                );
                            }

                            $staffQuery = DB::table('cl_staff_tbl')
                                ->where('application_id', $applicationId)
                                ->where('row_index', $rowIndex)
                                ->whereIn('staff_category', ['QC', 'QSC']);

                            $oldStoredPath = $staffQuery->value($documentCategory);
                            break;
                    }
                }

                // ==================================================
                // 10. GENERATE FILE NAME
                // ==================================================

                $date = now()->format('Ymd');
                $time = now()->timezone('Asia/Kolkata')->format('Hi');

                $formCode = $request->form_code;
                $moduleCode = strtoupper($pathData->module_code);

                if ($existing) {

                    // Keep the same filename when replacing a temp file.
                    $fileName = $existing->file_name;
                } else {

                    switch ($subCategory) {

                        case 'OED':
                        case 'AP':

                            $fileName = $date . '_' . $time . '_' .
                                $loginId . '_L' . $formCode . '_' .
                                strtoupper($request->ownership_type) .
                                $moduleCode . $rowIndex . '.pdf';

                            break;

                        case 'QAD':
                        case 'QCD':
                        case 'SS':

                            $fileName = $date . '_' . $time . '_' .
                                $loginId . '_L' . $formCode . '_' .
                                $moduleCode . $rowIndex . '.pdf';

                            break;

                        case 'OHD':

                            $fileName = $date . '_' . $time . '_' .
                                $loginId . '_L' . $formCode . '_' .
                                $request->ownership_type . '_' .
                                $moduleCode . '.pdf';

                            break;

                        case 'ED':

                            $fileName = $date . '_' . $time . '_' .
                                $loginId . '_L' . $formCode . '_' .
                                $request->ownership_type . '_' .
                                $moduleCode . $equipCode . '.pdf';

                            break;

                        case 'QD':

                            $fileName = $date . '_' . $time . '_' .
                                $loginId . '_L' . $formCode .
                                $moduleCode . $qcCode . '.pdf';

                            break;

                        default:

                            $fileName = $date . '_' . $time . '_' .
                                $loginId . '_L' . $formCode . '_' .
                                $moduleCode . '.pdf';

                            break;
                    }
                }

                // If updating an existing application's document, retain the
                // currently stored filename when one already exists. The new
                // upload will replace that filename in $folderPath.
                if ($recordId !== null && !empty($oldStoredPath)) {
                    $fileName = basename(str_replace('\\', '/', $oldStoredPath));
                }

                // ==================================================
                // 11. REPLACE EXISTING FILE OR SAVE NEW FILE
                // ==================================================

                $destination = $folderPath . DIRECTORY_SEPARATOR . $fileName;

                if (\File::exists($destination)) {
                    \File::delete($destination);
                }

                $file->move($folderPath, $fileName);

                $finalPath = $dbFilePath . '/' . $fileName;

                // ==================================================
                // 11. EXISTING APPLICATION: ACTUAL TABLES
                // record_id present = do not insert into temp table
                // ==================================================

                if ($recordId !== null) {

                    // dd($documentCategory); exit;

                    switch ($documentCategory) {

                        // ------------------------------------------
                        // OWNERSHIP DOCUMENT
                        // ------------------------------------------
                        case 'ownership_doc':

                            DB::table('ccl_forma_meta')->updateOrInsert(
                                ['application_id' => $applicationId],
                                [
                                    'ownership_doc' => $finalPath,
                                    'updated_at'    => now(),
                                ]
                            );

                            break;

                        // ------------------------------------------
                        // BANK SOLVENCY DOCUMENT
                        // ------------------------------------------
                        case 'bank_doc':

                            DB::table('tnelb_banksolvency_a')->updateOrInsert(
                                ['application_id' => $applicationId],
                                [
                                    'login_id'     => $loginId,
                                    'form_name'    => $request->form_name,
                                    'license_name' => $request->license_name,
                                    'bank_doc'     => $finalPath,
                                    'updated_at'   => now(),
                                    'status'       => '1',
                                ]
                            );

                            break;

                        // ------------------------------------------
                        // ADDRESS PROOF
                        // ------------------------------------------
                        case 'Address_proof':

                            DB::table('tnelb_addressproof_cl')->updateOrInsert(
                                ['application_id' => $applicationId],
                                [
                                    'login_id'     => $loginId,
                                    'form_name'    => $request->form_name,
                                    'license_name' => $request->license_name,
                                    'file_doc'     => $finalPath,
                                    'updated_at'   => now(),
                                ]
                            );

                            break;

                        // ------------------------------------------
                        // OTHER DOCUMENTS
                        // ------------------------------------------
                        case 'other_doc':

                            DB::table('tnelb_attachments_cl')->updateOrInsert(
                                [
                                    'application_id' => $applicationId,
                                    'type'            => $request->ownership_type,
                                ],
                                [
                                    'login_id'     => $loginId,
                                    'form_name'    => $request->form_name,
                                    'license_name' => $request->license_name,
                                    'file_doc'     => $finalPath,
                                    'updated_at'   => now(),
                                    'created_at'   => now(),
                                ]
                            );

                            break;

                        // ------------------------------------------
                        // EQUIPMENT TEST REPORT
                        // ------------------------------------------
                        case 'instrument_test_report':

                            if (!$equipCode) {
                                throw new \RuntimeException(
                                    'Equipment ID is required for the test report.'
                                );
                            }

                            DB::table('tnelb_equimentsuser_cl')->updateOrInsert(
                                [
                                    'application_id' => $applicationId,
                                    'equipment_id'   => $equipCode,
                                ],
                                [
                                    'testreport_file' => $finalPath,
                                    'updated_at'      => now(),
                                ]
                            );

                            break;

                        // ------------------------------------------
                        // EQUIPMENT PURCHASE REPORT
                        // ------------------------------------------
                        case 'instrument_purchase_report':

                            if (!$equipCode) {
                                throw new \RuntimeException(
                                    'Equipment ID is required for the purchase report.'
                                );
                            }

                            DB::table('tnelb_equimentsuser_cl')->updateOrInsert(
                                [
                                    'application_id' => $applicationId,
                                    'equipment_id'   => $equipCode,
                                ],
                                [
                                    'purchasereport_file' => $finalPath,
                                    'updated_at'          => now(),
                                ]
                            );

                            break;

                        // ------------------------------------------
                        // QC / QSC STAFF DOCUMENTS
                        // ------------------------------------------
                        case 'app_doc':
                        case 'cons_doc':

                            if ($rowIndex === null) {
                                throw new \RuntimeException(
                                    'Row index is required for QC/QSC staff documents.'
                                );
                            }

                            // The request must identify whether this is QC or QSC.
                            $staffCategory = 'QC'; // Default to QC if not provided

                            if (!in_array($staffCategory, ['QC', 'QSC'], true)) {
                                throw new \RuntimeException(
                                    'A valid staff_category (QC or QSC) is required.'
                                );
                            }

                            // Find the staff row using its actual identity.
                            $staff = DB::table('cl_staff_tbl')
                                ->where('application_id', $applicationId)
                                ->where('row_index', $rowIndex)
                                ->where('staff_category', 'QC')
                                ->first();

                            if ($staff) {

                                // Existing record: update only the uploaded document field.
                                DB::table('cl_staff_tbl')
                                    ->where('id', $staff->id)
                                    ->update([
                                        'staff_category'      => $staffCategory,
                                        $documentCategory => $finalPath,
                                        'updated_at'       => now(),
                                    ]);
                            } else {

                                // Missing record: insert a new staff row.
                                DB::table('cl_staff_tbl')->insert([
                                    'application_id' => $applicationId,
                                    'row_index'      => $rowIndex,
                                    'staff_category' => 'QC',
                                    'staff_flag' => '1',
                                    'login_id'     => $loginId,
                                    $documentCategory => $finalPath,
                                    'created_at'     => now(),
                                    'updated_at'     => now(),
                                ]);
                            }

                            break;

                                                // ------------------------------------------
                            // SPECIMEN SIGN
                            // ------------------------------------------
                            case 'specimen_sign':

                                if ($rowIndex === null) {
                                    throw new \RuntimeException(
                                        'Row index is required for specimen sign.'
                                    );
                                }

                                // Find the existing record using application ID + row index.
                                $signRecord = DB::table('cl_forma_signs')
                                    ->where('application_id', $applicationId)
                                    ->where('row_index', $rowIndex)
                                    ->first();

                                if ($signRecord) {

                                    // Existing record: update only the specimen sign file path.
                                    DB::table('cl_forma_signs')
                                        ->where('application_id', $applicationId)
                                        ->where('row_index', $rowIndex)
                                        ->update([
                                            'specimen_sign' => $finalPath,
                                            'updated_at'    => now(),
                                        ]);

                                } else {

                                    // Missing record: insert a new row.
                                    DB::table('cl_forma_signs')->insert([
                                        'login_id' => $loginId,
                                        'application_id' => $applicationId,
                                        'form_name' => $request->input('form_name'),
                                        'cert_name' => $request->input('cert_name'),
                                        'form_code' => $request->input('form_code'),
                                        'name_of_authorised_to_sign' => $request->input(
                                            'name_of_authorised_to_sign'
                                        ),
                                        'age_of_authorised_to_sign' => $request->input(
                                            'age_of_authorised_to_sign'
                                        ),
                                        'qualification_of_authorised_to_sign' => $request->input(
                                            'qualification_of_authorised_to_sign'
                                        ),
                                        'designation_of_authorised_to_sign' => $request->input(
                                            'designation_of_authorised_to_sign'
                                        ),
                                        'specimen_sign' => $finalPath,
                                        'row_index' => $rowIndex,
                                        'flag' => 1,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                                }

                                break;

                        // ------------------------------------------
                        // UNKNOWN DOCUMENT CATEGORY
                        // ------------------------------------------
                        default:

                            throw new \RuntimeException(
                                'Main-table mapping is not configured for document category: ' .
                                    $documentCategory .
                                    ', subcategory: ' . $subCategory
                            );
                    }

                    // The database now points to the replacement. If the old
                    // file was stored in a different folder, remove that stale
                    // file after the new upload and database update succeeded.
                    if (!empty($oldStoredPath)) {
                        $normalizedOldPath = str_replace('\\', '/', $oldStoredPath);
                        $oldAbsolutePath = str_starts_with($normalizedOldPath, '/')
                            ? $normalizedOldPath
                            : public_path(ltrim($normalizedOldPath, '/'));

                        if (is_file($oldAbsolutePath) && realpath($oldAbsolutePath) !== realpath($destination)) {
                            \File::delete($oldAbsolutePath);
                        }
                    }
                } else {

                    // ==============================================
                    // 12. NEW APPLICATION: TEMPORARY TABLE
                    // Empty record_id = session-based application ID
                    // is_final = 1 for insert and replacement
                    // ==============================================

                    if ($existing) {

                        // Replace existing temporary document.
                        // Keep its ID and filename unchanged.

                        DB::table('tnelb_temp_uploaded_documents')
                            ->where('id', $existing->id)
                            ->update([
                                'ownership_type'        => $request->ownership_type,
                                'document_sub_category' => $subCategory,
                                'original_pdfname'      => $originalName,
                                'file_path'             => $dbFilePath,
                                'is_final'              => '1',
                                'uploaded_at'           => now(),
                                'updated_at'            => now(),
                            ]);
                    } else {

                        // Insert new temporary document with is_final = 1.

                        DB::table('tnelb_temp_uploaded_documents')->insert([
                            'login_id'              => $loginId,
                            'application_id'        => $applicationId,
                            'record_id_app'         => null,
                            'form_name'             => $request->form_name,
                            'license_name'          => $request->license_name,
                            'module'                => $request->module,
                            'ownership_type'        => $request->ownership_type,
                            'document_category'     => $documentCategory,
                            'document_sub_category' => $subCategory,
                            'original_pdfname'      => $originalName,
                            'file_name'             => $fileName,
                            'file_path'             => $dbFilePath,
                            'ownership_count'       => $counter,
                            'appl_type'             => $request->appl_type,
                            'row_index'             => $rowIndex,
                            'equip_code'            => $equipCode,
                            'qc_code'               => $qcCode,
                            'uploaded_at'           => now(),
                            'is_final'              => '1',
                            'created_at'            => now(),
                            'updated_at'            => now(),
                        ]);

                        // Preserve the existing temporary mapping logic.

                        // DB::table('cl_temp_mapping')->insert([
                        //     'login_id'       => $loginId,
                        //     'temp_app_id'    => $applicationId,
                        //     'application_id' => '',
                        //     'form_name'      => $request->form_name,
                        //     'license_name'   => $request->license_name,
                        //     'date'           => now()->toDateString(),
                        //     'created_at'     => now(),
                        //     'updated_at'     => now(),
                        // ]);
                    }
                }

                // ==================================================
                // 13. BUILD RESPONSE
                // ==================================================

                $uploadedFiles[] = [
                    'file_name' => $fileName,
                    'file_url'  => asset($finalPath),
                ];

                $counter++;
            }

            // ======================================================
            // 14. SUCCESS RESPONSE
            // ======================================================

            return response()->json([
                'status' => 'success',
                'files'  => $uploadedFiles,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'status' => 'validation_error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ], 500);
        }
    }



    public function uploadownershipdeed_16102026(Request $request)
    {
        try {





            # 1. CHECK FILE EXISTENCE
            if (count($request->files->all()) === 0) {
                return response()->json([
                    'status' => 'error',
                    'msg' => 'No file received'
                ], 422);
            }

            $fileInputs = $request->files->all();
            $fileField = array_key_first($fileInputs);
            $fileValue = $fileInputs[$fileField];

            # 2. VALIDATION
            $rules = [
                'module' => 'required',
                'document_category' => 'required',
                'license_name' => 'required',
                'form_name' => 'required',
            ];

            if (is_array($fileValue)) {
                $rules[$fileField] = 'required|array';
                $rules[$fileField . '.*'] = 'required|file|mimes:pdf|max:250';
            } else {
                $rules[$fileField] = 'required|file|mimes:pdf|max:250';
            }

            $request->validate($rules);





            # 3. NORMALIZE FILES
            $files = is_array($fileValue) ? $fileValue : [$fileValue];

            # 4. SESSION APPLICATION ID
            if (!session()->has('application_id')) {
                $applicationId = 'APP_' . $request->license_name . date('dmY');
                session(['application_id' => $applicationId]);
            } else {
                $applicationId = session('application_id');
            }





            $loginId = auth()->user()->login_id;

            # 5. FILE PATH
            $dbFilePath_all = DocPathController::getPath($request);

            $dbFilePath = $dbFilePath_all->filepath_pro;

            $dbFilePath_modulecode = $dbFilePath_all->module_code;

            $folderPath = public_path($dbFilePath);

            if (!\File::exists($folderPath)) {
                \File::makeDirectory($folderPath, 0755, true);
            }



            $uploadedFiles = [];

            # 6. LOOP FILES
            $counter = 1;
            foreach ($files as $file) {

                $originalName = $file->getClientOriginalName();


                # CHECK EXISTING RECORD

                $record_id_app = $request->record_id;
                $existingQuery = DB::table('tnelb_temp_uploaded_documents')
                    ->where('login_id', $loginId)
                    ->where('application_id', $applicationId)
                    ->where('module', $request->module)
                    // ->where('record_id_app', $record_id_app)
                    ->where('form_name', $request->form_name)
                    ->where('document_category', $request->document_category);

                if ($request->document_sub_category === 'OED') {
                    $existingQuery->where('document_sub_category', 'OED')
                        ->where('ownership_type', $request->ownership_type)
                        ->where('row_index', $request->row_index);
                }

                if ($request->document_sub_category === 'AP') {
                    $existingQuery->where('document_sub_category', 'AP')
                        ->where('ownership_type', $request->ownership_type)
                        ->where('row_index', $request->row_index);
                }

                if ($request->document_sub_category === 'ED') {

                    $existingQuery->where('document_sub_category', 'ED')
                        ->where('equip_code', $request->equip_code);
                }

                if ($request->document_sub_category === 'QD') {

                    $existingQuery->where('document_sub_category', 'QD')
                        ->where('qc_code', $request->qc_code);
                }


                if ($request->document_sub_category === 'OHD') {
                    $existingQuery->where('ownership_type', $request->ownership_type);
                }

                if ($request->document_sub_category === 'QAD') {

                    $existingQuery->where('document_sub_category', 'QAD')
                        ->where('row_index', $request->row_index);
                }

                if ($request->document_sub_category === 'QCD') {

                    $existingQuery->where('document_sub_category', 'QCD')
                        ->where('row_index', $request->row_index);
                }

                if ($request->document_sub_category === 'SS') {

                    $existingQuery->where('document_sub_category', 'SS')
                        ->where('row_index', $request->row_index);
                }

                $existing = $existingQuery->first();



                if ($existing) {

                    # UPDATE FLOW
                    $fileName = $existing->file_name;

                    $newStatus = ($existing->is_final == '1') ? '2' : '0';

                    # DELETE OLD FILE
                    $oldFile = public_path($existing->file_path . $fileName);
                    if (\File::exists($oldFile)) {
                        \File::delete($oldFile);
                    }

                    # MOVE NEW FILE (SAME NAME)
                    $file->move($folderPath, $fileName);

                    DB::table('tnelb_temp_uploaded_documents')
                        ->where('id', $existing->id)
                        ->update([
                            'ownership_type' => $request->ownership_type,
                            'document_sub_category' => $request->document_sub_category,
                            'original_pdfname' => $originalName,
                            'file_path' => $dbFilePath,
                            'is_final' => $newStatus,
                            'uploaded_at' => now(),
                            'updated_at' => now(),
                        ]);
                } else {

                    # INSERT FLOW

                    $date = now()->format('Ymd');
                    $time = now()->timezone('Asia/Kolkata')->format('Hi');

                    $form_code = $request->form_code;

                    $equip_code = $request->equip_code;
                    $qc_code = $request->qc_code;

                    $moduleCode = strtoupper($dbFilePath_modulecode);


                    $random = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

                    if ($request->document_sub_category === 'OED') {

                        $fileName = $date . '_' . $time . '_' . $loginId . '_' .
                            'L' . $form_code . '_' .
                            strtoupper($request->ownership_type) .
                            $moduleCode . $request->row_index . '.pdf';
                    } elseif ($request->document_sub_category === 'AP') {

                        $fileName = $date . '_' . $time . '_' . $loginId . '_' .
                            'L' . $form_code . '_' .
                            strtoupper($request->ownership_type) .
                            $moduleCode . $request->row_index . '.pdf';
                    } elseif ($request->document_sub_category === 'QAD') {

                        $fileName = $date . '_' . $time . '_' . $loginId . '_' .
                            'L' . $form_code . '_' .
                            $moduleCode . $request->row_index . '.pdf';
                    } elseif ($request->document_sub_category === 'QCD') {
                        $fileName = $date . '_' . $time . '_' . $loginId . '_' .
                            'L' . $form_code . '_' .
                            $moduleCode . $request->row_index . '.pdf';
                    } elseif ($request->document_sub_category === 'SS') {
                        $fileName = $date . '_' . $time . '_' . $loginId . '_' .
                            'L' . $form_code . '_' .
                            $moduleCode . $request->row_index . '.pdf';
                    } elseif ($request->document_sub_category === 'OHD') {


                        $fileName = $date . '_' . $time . '_' . $loginId . '_' .
                            'L' . $form_code . '_' . $request->ownership_type . '_' .
                            $moduleCode . '.pdf';
                    } elseif ($request->document_sub_category === 'ED') {

                        $fileName = $date . '_' . $time . '_' . $loginId . '_' .
                            'L' . $form_code . '_' . $request->ownership_type . '_' .
                            $moduleCode . $equip_code . '.pdf';
                    } elseif ($request->document_sub_category === 'QD') {

                        $fileName = $date . '_' . $time . '_' . $loginId . '_' .
                            'L' . $form_code . $moduleCode . $qc_code . '.pdf';
                    } else {


                        $fileName = $date . '_' . $time . '_' . $loginId . '_' .
                            'L' . $form_code . '_' .
                            $moduleCode . '.pdf';
                    }

                    $equip_code = is_numeric($request->equip_code) ? $request->equip_code : null;

                    $qc_code = is_numeric($request->qc_code) ? $request->qc_code : null;

                    $file->move($folderPath, $fileName);




                    DB::table('tnelb_temp_uploaded_documents')->insert([
                        'login_id' => $loginId,
                        'application_id' => $applicationId,
                        'form_name' => $request->form_name,
                        'license_name' => $request->license_name,
                        'module' => $request->module,
                        'ownership_type' => $request->ownership_type,
                        'document_category' => $request->document_category,
                        'document_sub_category' => $request->document_sub_category,
                        'original_pdfname' => $originalName,
                        'file_name' => $fileName,
                        'file_path' => $dbFilePath,
                        'ownership_count' => $counter,

                        'appl_type' => $request->appl_type,

                        'row_index' => is_numeric($request->row_index) ? (int) $request->row_index : null,

                        'equip_code' => $equip_code,
                        'qc_code' => $qc_code,
                        'uploaded_at' => now(),
                        'is_final' => '0',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);


                    DB::table('cl_temp_mapping')->insert([
                        'login_id' => $loginId,
                        'temp_app_id' => $applicationId,

                        'application_id' => '',
                        'form_name' => $request->form_name,
                        'license_name' => $request->license_name,

                        'date' => now()->toDateString(),

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $counter++;

                $uploadedFiles[] = [
                    'file_name' => $fileName,
                    'file_url' => asset($dbFilePath . $fileName),
                ];
            }


            # 7. RESPONSE
            return response()->json([
                'status' => 'success',
                'files' => $uploadedFiles
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'status' => 'validation_error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    public function uploadownershipdeed_bk(Request $request)
    {
        //   dd($request->all());
        // exit;

        $form_name = $request->form_name;
        $license_name = $request->license_name;
        $request->validate([
            'file' => 'required|mimes:pdf|max:250'
        ]);




        try {
            $file = $request->file('partnership_deed');

            if (!session()->has('application_id')) {
                $applicationId = 'APP_' . $license_name  . date('dmY');
                session(['application_id' => $applicationId]);
            } else {
                $applicationId = session('application_id');
            }


            $loginId          = auth()->user()->login_id;
            // $applicationId    = session('application_id');

            $documentCategory = 'PARTNERSHIP_DEED';

            $date = Carbon::now()->format('d_m_Y');
            $year = Carbon::now()->format('Y');

            $fileName = $loginId . '_' . $date . '_' . $documentCategory . '.pdf';

            // dd($fileName);exit;
            // $file_path = 'upload_documents/EA/New_applications/ownership_doc/';

            // $folderPath = public_path('upload_documents/EA/New_applications/ownership_doc/');

            $file_path = DocPathController::getPath($request);
            $folderPath = public_path($file_path);


            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0755, true);
            }

            $file->move($folderPath, $fileName);
            DB::table('tnelb_temp_uploaded_documents')->insert([
                'login_id'               => $loginId,
                'application_id'         => $applicationId,
                'module'                 => $request->module,
                'ownership_type'         => 'pt',
                'document_category'      => $request->document_category,
                'document_sub_category'  => 'DEED_doc',
                'file_name'              => $fileName,
                'file_path'              => $file_path,

                // DB time (not PHP time)
                'uploaded_at'            => DB::raw('NOW()'),
                'is_final'               => '0',
                'created_at'             => DB::raw('NOW()'),
                'updated_at'             => DB::raw('NOW()'),
            ]);


            // $doc = Tnelb_Temp_Tbl::create([
            //     'login_id'               => auth()->id(),
            //     'application_id'         => $request->application_id ?? null,
            //     'module'                 => 'ownership_doc',
            //     'ownership_type'         => 'pt',
            //     'document_category'      => 'Ownership',
            //     'document_sub_category'  => 'Partnership Deed',
            //     'file_name'              => $fileName,
            //     'file_path'              => $filePath,
            //     'uploaded_at'            => now(),
            //     'is_final'               => 'N',
            //     'moved_as'               => null,
            // ]);

            return response()->json([
                'status' => 'success',
                'file_url' => asset($file_path . $fileName),
                'file_name' => $fileName,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Upload failed. Please try again.',
            ], 500);
        }
    }
}
