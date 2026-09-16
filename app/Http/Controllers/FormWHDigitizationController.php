<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/** Backward-compatible alias: Form WH digitisation lives on FormWHController. */
class FormWHDigitizationController extends FormWHController
{
    public function index(Request $request)
    {
        return $this->digitize($request);
    }
}
