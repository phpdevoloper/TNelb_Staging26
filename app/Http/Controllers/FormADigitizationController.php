<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Admin\Mst_equipment_tbl;
use App\Models\MstLicence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FormADigitizationController extends BaseController
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('logout');
        }

        $cert_licence_code = 'EA';

        $equiplist = Mst_equipment_tbl::where('equip_licence_name', 8)
            ->where('status', 1)
            ->orderBy('id')
            ->get();


        $form_code = MstLicence::where('cert_licence_code', $cert_licence_code)
            ->where('status', 1)
            ->orderBy('id')
            ->first();



        return view('user_login.digitization.EA.apply-form-a', compact('equiplist', 'form_code'));
    }

    public function storedigitization_cl(Request $request)
    {
        // -----------------------------------------
        // VALIDATION
        // -----------------------------------------

        $validator = Validator::make($request->all(), [

            'clnumber' => [
                'required',
                'max:15'
            ],

            'fissue' => [
                'required',
                'date'
            ],

            'from_date' => [
                'required',
                'date'
            ],

            'to_date' => [
                'required',
                'date'
            ],

            'cl_doc' => [
                'required',
                'file',
                'mimes:pdf',
                'max:250'
            ],

        ], [

            'clnumber.required' =>
            'Licence Number is required.',

            'clnumber.max' =>
            'Licence Number must not exceed 15 characters.',

            'fissue.required' =>
            'Date of First Issue is required.',

            'fissue.date' =>
            'Invalid Date of First Issue.',

            'from_date.required' =>
            'Validity From Date is required.',

            'from_date.date' =>
            'Invalid Validity From Date.',

            'to_date.required' =>
            'Validity To Date is required.',

            'to_date.date' =>
            'Invalid Validity To Date.',

            'cl_doc.required' =>
            'Please upload PDF document.',

            'cl_doc.mimes' =>
            'Only PDF files are allowed.',

            'cl_doc.max' =>
            'File size should not exceed 250 KB.',

        ]);


        // -----------------------------------------
        // VALIDATION FAILED
        // -----------------------------------------

        if ($validator->fails()) {

            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }


        // -----------------------------------------
        // DATE CHECK
        // -----------------------------------------

        $fromDate = Carbon::parse($request->from_date);
        $toDate   = Carbon::parse($request->to_date);


        // To Date cannot be before From Date

        if ($toDate->lt($fromDate)) {

            return response()->json([
                'errors' => [
                    'to_date' => [
                        'Validity To Date must be greater than or equal to Validity From Date.'
                    ]
                ]
            ], 422);
        }


        // -----------------------------------------
        // TODAY + 1 YEAR VALIDITY CHECK
        // -----------------------------------------

        $allowedDate = $toDate->copy()->addYear();

        if (Carbon::today()->gt($allowedDate)) {

            return response()->json([
                'errors' => [
                    'to_date' => [
                        'Apply New Application Validity Period including Renewal exceeds limits'
                    ]
                ]
            ], 422);
        }


        // -----------------------------------------
        // LOGIN DETAILS
        // -----------------------------------------

        $login_id = Auth::user()->login_id;
        $user_id  = Auth::user()->id;

        $now = now();


        // -----------------------------------------
        // INITIAL TEMP APP ID
        // -----------------------------------------

        $initial_temp_app_id = 'TEMP' . date('Ymd') . '0000';


        // -----------------------------------------
        // INSERT INITIAL RECORD
        // -----------------------------------------

        try {

            $id = DB::table('mapping_digi_cls')->insertGetId([

                'login_id'     => $login_id,

                'temp_app_id'  => $initial_temp_app_id,

                'form_name'    => $request->form_name,

                'licence_name' => $request->licence_name,

                'clnumber'     => $request->clnumber,

                'fissue'       => $request->fissue,

                'from_date'    => $request->from_date,

                'to_date'      => $request->to_date,

                'cl_doc'       => 'pending',


                'created_at'   => $now,

                'updated_at'   => $now,

            ]);


            // -----------------------------------------
            // GENERATE FINAL TEMP APP ID
            // -----------------------------------------

            $temp_app_id = 'TEMP' . date('Ymd') .
                str_pad($id, 4, '0', STR_PAD_LEFT);


            // -----------------------------------------
            // FILE UPLOAD
            // -----------------------------------------

            $cl_doc = null;

            if ($request->hasFile('cl_doc')) {

                $file = $request->file('cl_doc');

                $extension = $file->getClientOriginalExtension();

                $fileName = $temp_app_id . '_' .
                    time() . '_CL.' . $extension;


                // Create directory if not exists
                $uploadPath = public_path('uploads/digitization/cl/');

                if (!file_exists($uploadPath)) {

                    mkdir($uploadPath, 0777, true);
                }


                // Move file
                $file->move(
                    $uploadPath,
                    $fileName
                );


                // Database path
                $cl_doc = 'uploads/digitization/cl/' . $fileName;
            }


            // -----------------------------------------
            // UPDATE TEMP APP ID + DOCUMENT
            // -----------------------------------------

            DB::table('mapping_digi_cls')
                ->where('id', $id)
                ->update([

                    'temp_app_id' => $temp_app_id,

                    'cl_doc'      => $cl_doc,

                    'updated_at'  => now(),

                ]);


            // -----------------------------------------
            // SUCCESS RESPONSE
            // -----------------------------------------

            return response()->json([

                'status'     => 200,

                'success'    => true,

                'message'    => 'Data stored successfully.',

                'temp_app_id' => $temp_app_id,

            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status'  => 500,
                'success' => false,
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ], 500);
        }
    }
}
