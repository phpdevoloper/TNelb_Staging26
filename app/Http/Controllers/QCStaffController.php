<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use Illuminate\Support\Facades\DB;
use App\Models\Register;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Colors\Rgb\Channels\Red;

class QCStaffController extends Controller
{
    public function verifylicenseformAqc(Request $request)
    {
        $input = $request->all();

        // Convert dd-mm-yyyy to Y-m-d BEFORE validation
        if (!empty($input['date'])) {
            try {
                $input['date'] = Carbon::createFromFormat('d-m-Y', $input['date'])->format('Y-m-d');
            } catch (\Exception $e) {
            }
        }

        // Use manual validator instead of $request->validate()
        $validator = Validator::make($input, [
            'license_number' => 'required|string|max:50',
            'date' => 'required|date',
        ], [
            'date.before' => 'Enter the valid date'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Use sanitized $input
        $licenseNumber = $input['license_number'];
        $date = $input['date'];



      
        $query1 = DB::table('scert')->selectRaw("CAST(certno AS VARCHAR) AS license_number, vdate AS expires_at");
        $query2 = DB::table('tnelb_license')->selectRaw("CAST(license_number AS VARCHAR) AS license_number, expires_at");

        $exists = DB::query()
            ->fromSub(
                $query1
                    ->unionAll($query2),
                'all_licenses'
            )
            ->where('license_number', (string) $licenseNumber)
            ->whereDate('expires_at', $date)
            ->exists();

        return response()->json([
            'exists' => $exists
        ]);
    }
}
