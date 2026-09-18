<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/** Backward-compatible alias: Form P digitisation lives on FormPController. */
class FormpDigitizationController extends FormPController
{
    public function index(Request $request)
    {
        return $this->digitize($request);
    }
}
