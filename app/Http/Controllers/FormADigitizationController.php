<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Admin\Mst_equipment_tbl;
use App\Models\MstLicence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormADigitizationController extends BaseController
{
    public function index(){
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
}
