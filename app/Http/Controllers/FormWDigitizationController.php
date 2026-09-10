<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/** Backward-compatible alias: Form W digitisation lives on FormWController. */
class FormWDigitizationController extends FormWController
{
    public function index(Request $request)
    {
        return $this->digitize($request);
    }
}
