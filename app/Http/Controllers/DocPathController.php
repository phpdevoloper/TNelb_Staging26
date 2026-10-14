<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocPathController extends Controller
{
    public static function getPath(Request $request)
    {
// dd($request->module, trim($request->appl_type));



// exit;
        $filepath = DB::table('mst_filepath_cl_tbl')
            ->where('appl_type', trim($request->appl_type))
            ->where('form_module', $request->module)
            ->where('status', '1')

            ->first();

        if (!$filepath) {
            $filepath = '0';
        }
// dd($filepath);exit;
        return $filepath;
    }
}
