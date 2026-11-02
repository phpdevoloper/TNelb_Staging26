<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class StaffController extends Controller
{
    public function checkStaffExists(Request $request)
    {
        // dd('111');
        // exit;
        $cc_number = $request->cc_number;
        // $cc_validity = $request->cc_validity;

        // dd($cc_number);
        // dd($cc_validity);
        // exit;

        $exists = DB::table('tnelb_applicant_cl_staffdetails as s')
            ->join('cl_forma_lic as l', 's.application_id', '=', 'l.application_id')
            ->leftJoin('cl_forma_lic as rl', 's.application_id', '=', 'rl.application_id')
            ->where('s.cc_number', $cc_number)
            // ->where('s.cc_validity', $cc_validity)
            // ->where(function($query) {
            //     $query->whereNotNull('l.expires_at')
            //           ->orWhereNotNull('rl.expires_at');
            // })
            ->exists();

        return response()->json(['exists' => $exists]);
    }


    public function saveQCStaff(Request $request)
    {

        // dd($request->all()); exit;
        $validator = Validator::make($request->all(), [
            // 'application_id' => 'required|integer',
            'staff_name'     => 'required|string|max:255',
            'staff_cc_no'    => 'required|string|max:100',
            'first_issue'    => 'required|string',
            'validity_from'  => 'required|string',
            'validity_to'    => 'required|string',
            'app_doc'        => 'required|string',
            'cons_doc'       => 'required|string',
        ]);



        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()->toArray()
            ], 422);
        }

        DB::beginTransaction();

        try {

            $applicationId = $request->input('application_id');

            if (empty($applicationId)) {
                $applicationId = session()->getId();
            }

            // $applicationId = $request->application_id;
            $ccNo = trim($request->staff_cc_no);

            // Find existing QC staff using CC number
            $existingStaff = DB::table('cl_staff_tbl')
                ->where('application_id', $applicationId)
                ->where('staff_category', 'QC')
                ->where('staff_cc_no', $ccNo)
                ->where('staff_flag', 1)
                ->lockForUpdate()
                ->first();

            // Convert frontend date d-m-Y to database Y-m-d
            $firstIssue = $this->formatDate($request->first_issue);
            $validityFrom = $this->formatDate($request->validity_from);
            $validityTo = $this->formatDate($request->validity_to);

            $data = [
                'staff_name'              => $request->staff_name,
                'staff_cc_no'             => $ccNo,

                // Actual database column names
                'staff_cc_first_issue'    => $firstIssue,
                'staff_cc_validity_from'  => $validityFrom,
                'staff_cc_validity_to'    => $validityTo,

                'app_doc'                 => $request->app_doc,
                'cons_doc'                => $request->cons_doc,

                'form_name'               => $request->form_name,
                'license_name'            => $request->license_name,
                'appl_type'               => $request->appl_type,

                'staff_category'          => 'QC',
                'updated_at'              => now(),
            ];

            if ($existingStaff) {

                // UPDATE existing CC number
                DB::table('cl_staff_tbl')
                    ->where('id', $existingStaff->id)
                    ->update($data);

                $staffId = $existingStaff->id;
                $rowIndex = $existingStaff->row_index;
                $operation = 'updated';
            } else {

                // INSERT new QC staff
                $rowIndex = (DB::table('cl_staff_tbl')
                    ->where('application_id', $applicationId)
                    ->where('staff_category', 'QC')
                    ->max('row_index') ?? 0) + 1;

                $data['login_id'] = auth()->id();
                $data['application_id'] = $applicationId;
                $data['staff_category'] = 'QC';
                $data['row_index'] = $rowIndex;
                $data['staff_status'] = 'N';
                $data['staff_flag'] = 1;
                $data['created_at'] = now();

                // Add any required login/user ID field here,
                // using your existing server-side session logic.

                $staffId = DB::table('cl_staff_tbl')
                    ->insertGetId($data);

                $operation = 'inserted';
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'QC staff ' . $operation . ' successfully.',

                'staff_id' => $staffId,
                'row_index' => $rowIndex,
                'operation' => $operation,

                'staff_name' => $request->staff_name,
                'staff_cc_no' => $ccNo,

                'first_issue' => $firstIssue,
                'validity_from' => $validityFrom,
                'validity_to' => $validityTo,

                'app_doc' => $request->app_doc,
                'cons_doc' => $request->cons_doc,

                'fee' => $request->fee ?? 0
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            \Log::error('QC Staff Save Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | REMOVE QC STAFF
    |--------------------------------------------------------------------------
    */
    public function removeQCStaff(Request $request)
    {

        // dd($request->all()); exit;
        $request->validate([
            'staff_id' => 'required',
            'application_id' => 'required',
            'cc_no' => 'required',
        ]);

        try {

            $staff = DB::table('cl_staff_tbl')
                ->where('id', $request->staff_id)
                ->where('application_id', $request->application_id)
                ->where('staff_cc_no', $request->cc_no)
                ->where('staff_category', 'QC')
                ->where('staff_flag', '1')
                ->first();

            if (!$staff) {
                return response()->json([
                    'status' => false,
                    'message' => 'QC staff record not found.'
                ]);
            }

            DB::table('cl_staff_tbl')
                ->where('id', $request->staff_id)
                ->where('application_id', $request->application_id)
                ->where('staff_cc_no', $request->cc_no)
                ->update([
                    'staff_flag' => '0',
                    'updated_at' => now()
                ]);

            return response()->json([
                'status' => true,
                'message' => 'QC staff removed successfully.'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Unable to remove QC staff.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /*
    |--------------------------------------------------------------------------
    | DATE FORMAT
    |--------------------------------------------------------------------------
    */

    private function formatDate($date)
    {
        if (empty($date)) {
            return null;
        }

        return \Carbon\Carbon::parse($date)->format('Y-m-d');
    }


public function updateQCStaff(Request $request)
{
    $request->validate([
        'id' => 'required|integer',
        'application_id' => 'required',
        'row_index' => 'required|integer',
        'staff_cc_no' => 'required|string|max:20',
        'staff_name' => 'required|string',
        'staff_cc_first_issue' => 'required|date',
        'staff_cc_validity_from' => 'required|date',
        'staff_cc_validity_to' => 'required|date',
    ]);

    DB::beginTransaction();

    try {

        // ======================================================
        // 1. GET EXISTING ACTIVE QC STAFF RECORD
        // ======================================================

        $oldStaff = DB::table('cl_staff_tbl')
            ->where('id', $request->id)
            ->where('application_id', $request->application_id)
            ->where('row_index', $request->row_index)
            ->where('staff_flag', 1)
            ->lockForUpdate()
            ->first();

        if (!$oldStaff) {
            throw new \Exception("Active QC staff record not found.");
        }

        // ======================================================
        // 2. COMPARE CC NUMBER
        // ======================================================

        $oldCCNo = trim($oldStaff->staff_cc_no);
        $newCCNo = trim($request->staff_cc_no);

        $ccChanged = ($oldCCNo !== $newCCNo);

        // ======================================================
        // 3. PREPARE UPDATED STAFF DETAILS
        // ======================================================

        $staffData = [
            'staff_category' => $oldStaff->staff_category,
            'staff_name' => $request->staff_name,
            'staff_cc_no' => $newCCNo,
            'staff_cc_first_issue' => $request->staff_cc_first_issue,
            'staff_cc_validity_from' => $request->staff_cc_validity_from,
            'staff_cc_validity_to' => $request->staff_cc_validity_to,

            // Preserve latest document paths from database.
            'app_doc' => $oldStaff->app_doc,
            'cons_doc' => $oldStaff->cons_doc,

            'updated_at' => now(),
        ];

        // ======================================================
        // 4. SAME CC NUMBER - UPDATE EXISTING RECORD
        // ======================================================

        if (!$ccChanged) {

            DB::table('cl_staff_tbl')
                ->where('id', $oldStaff->id)
                ->update($staffData);

            $newId = $oldStaff->id;
            $oldRecordId = null;

        }

        // ======================================================
        // 5. CHANGED CC NUMBER
        // OLD RECORD INACTIVE + NEW RECORD ACTIVE
        // ======================================================

        else {

            // Deactivate old record.
            DB::table('cl_staff_tbl')
                ->where('id', $oldStaff->id)
                ->update([
                    'staff_flag' => 0,
                    'updated_at' => now(),
                ]);

            // Copy existing record.
            $newStaffData = (array) $oldStaff;

            unset($newStaffData['id']);

            // Update edited details and insert new active record.
            $newStaffData = array_merge(
                $newStaffData,
                $staffData,
                [
                    'staff_flag' => 1,
                    'row_index' => $oldStaff->row_index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $newId = DB::table('cl_staff_tbl')
                ->insertGetId($newStaffData);

            $oldRecordId = $oldStaff->id;
        }

        // ======================================================
        // 6. GET NEW ACTIVE RECORD
        // ======================================================

        $savedStaff = DB::table('cl_staff_tbl')
            ->where('id', $newId)
            ->where('staff_flag', 1)
            ->first();

        if (!$savedStaff) {
            throw new \Exception(
                "Unable to retrieve the updated QC staff record."
            );
        }

        DB::commit();

        // ======================================================
        // 7. SUCCESS RESPONSE
        // ======================================================

        return response()->json([

            'status' => 'success',

            'message' => $ccChanged
                ? 'Old QC staff record deactivated and new QC staff record inserted successfully.'
                : 'QC staff updated successfully.',

            'cc_changed' => $ccChanged ? 1 : 0,

            'old_staff_id' => $oldRecordId,

            'staff' => [

                'id' => $savedStaff->id,

                'row_index' => $savedStaff->row_index,

                'staff_category' => $savedStaff->staff_category,

                'staff_name' => $savedStaff->staff_name,

                'staff_cc_no' => $savedStaff->staff_cc_no,

                'staff_cc_first_issue' => $savedStaff->staff_cc_first_issue
                    ? \Carbon\Carbon::parse($savedStaff->staff_cc_first_issue)->format('Y-m-d')
                    : '',

                'staff_cc_validity_from' => $savedStaff->staff_cc_validity_from
                    ? \Carbon\Carbon::parse($savedStaff->staff_cc_validity_from)->format('Y-m-d')
                    : '',

                'staff_cc_validity_to' => $savedStaff->staff_cc_validity_to
                    ? \Carbon\Carbon::parse($savedStaff->staff_cc_validity_to)->format('Y-m-d')
                    : '',

                'app_doc' => $savedStaff->app_doc,

                'cons_doc' => $savedStaff->cons_doc,

                'app_doc_url' => !empty($savedStaff->app_doc)
                    ? asset($savedStaff->app_doc)
                    : '',

                'cons_doc_url' => !empty($savedStaff->cons_doc)
                    ? asset($savedStaff->cons_doc)
                    : '',

                'staff_flag' => $savedStaff->staff_flag,
            ],
        ]);

    } catch (\Throwable $e) {

        DB::rollBack();

        \Log::error('QC STAFF UPDATE ERROR', [
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ]);

        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
}


    // edit qc ---------------------------------------------
    public function editQCStaffController($id)
    {
        try {

            $staff = DB::table('cl_staff_tbl')
                ->where('id', $id)
                ->where('staff_category', 'QC')
                ->where('staff_flag', 1)
                ->first();

            if (!$staff) {
                return response()->json([
                    'status' => false,
                    'message' => 'QC staff record not found.'
                ], 404);
            }

            return response()->json([
                'status' => true,

                'data' => [
                    'id' => $staff->id,
                    'row_index' => $staff->row_index,

                    'staff_name' => $staff->staff_name,
                    'staff_cc_no' => $staff->staff_cc_no,

                    'first_issue' => $staff->staff_cc_first_issue
                        ? \Carbon\Carbon::parse($staff->staff_cc_first_issue)->format('Y-m-d')
                        : '',

                    'validity_from' => $staff->staff_cc_validity_from
                        ? \Carbon\Carbon::parse($staff->staff_cc_validity_from)->format('Y-m-d')
                        : '',

                    'validity_to' => $staff->staff_cc_validity_to
                        ? \Carbon\Carbon::parse($staff->staff_cc_validity_to)->format('Y-m-d')
                        : '',

                    'app_doc' => $staff->app_doc,
                    'cons_doc' => $staff->cons_doc,

                    'application_id' => $staff->application_id,
                    'form_name' => $staff->form_name,
                    'license_name' => $staff->license_name,
                    'appl_type' => $staff->appl_type
                ]
            ]);
        } catch (\Throwable $e) {

            \Log::error('Edit QC Staff Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Unable to fetch QC staff details.'
            ], 500);
        }
    }
}
