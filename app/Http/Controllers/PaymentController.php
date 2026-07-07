<?php

namespace App\Http\Controllers;

use App\Models\EA_Application_model;
use App\Models\Mst_documents;
use App\Models\Mst_education;
use App\Models\Mst_experience;
use App\Models\Mst_Form_s_w;
use App\Models\Payment; // ✅ Add this line
use App\Models\TnelbFormP;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

use Carbon\Carbon;


class PaymentController extends Controller
{

    public function updatePayment(Request $request)
    {
        
        $validated = $request->validate([
            'login_id'        => 'required',
            'application_id'  => 'required',
            'transaction_id'  => 'required',
            'amount'          => 'required|numeric|min:0',
            'payment_mode'    => 'required',
            'lateFee'         => 'nullable|int',
            'lateMonths'      => 'nullable|int',
            'transactionDate'  => 'required|date',
            'board_member_fee_exempt' => 'nullable|in:0,1',
        ]);

        //      dd($request->all());
        // exit;
        if ($request->application_id) {
            $form = Mst_Form_s_w::where('application_id', $validated['application_id'])->first();
        }
        


        // $transaction_date = \Carbon\Carbon::createFromFormat('d-m-Y', $request->transactionDate)
        //                ->format('Y-m-d');

        if (!$form) {
            return response()->json([
                'status' => 404,
                'message' => 'Form details not found!',
            ]);
        }

        $applType = strtoupper(trim((string) ($form->appl_type ?? '')));
        $noPaymentType = in_array($applType, ['D', 'A'], true);
        $boardMemberExempt = $request->boolean('board_member_fee_exempt')
            && strtoupper((string) $form->form_name) === 'S'
            && in_array($applType, ['N', 'R'], true);

        if (in_array($applType, ['N', 'R'], true)
            && (float) $validated['amount'] <= 0
            && ! $noPaymentType
            && ! $boardMemberExempt) {
            return response()->json([
                'status' => 422,
                'message' => 'Payment is required for new and renewal applications.',
            ], 422);
        }

        $payment = Payment::updateOrCreate(
            [
                'login_id'        => $validated['login_id'],
                'application_id'  => $validated['application_id'],
            ],
            [
                'transaction_id'    => $validated['transaction_id'],
                'payment_status'    => 'success',
                'amount'            => $validated['amount'],
                'form_name'         => $form->form_name,
                'license_name'      => $form->license_name,
                'payment_mode'      => $validated['payment_mode'],
                'late_fees'         => $validated['lateFee'] ?? 0,
                'late_months'       => $validated['lateMonths'] ?? 0,
                'transaction_date'  => $validated['transactionDate']
            ]
        );

        // var_dump($request->form_name);die;

        if ($payment) {
            if (in_array($request->form_name, ['S', 'W', 'WH'])) {

                Mst_Form_s_w::where('application_id', $validated['application_id'])
                ->update([
                    'payment_status' => 'payment', // e.g., 'payment', 'draft', etc.
                ]);
            }else {
                EA_Application_model::where('application_id', $validated['application_id'])
                ->update([
                    'payment_status' => 'payment', // e.g., 'payment', 'draft', etc.
                ]);
            }
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Payment created successfully!',
            'data'    => $payment
        ]);
    }

    public function updatePaymentFormP(Request $request)
    {



        // $transactionDate = Carbon::createFromFormat('d-m-Y', $request->transactionDate)
        // ->format('Y-m-d');
        

        $validated = $request->validate([
            'login_id'        => 'required',
            'application_id'  => 'required',
            'transaction_id'  => 'required',
            'amount'          => 'required|numeric|min:0',
            'payment_mode'    => 'required',
            'lateFee'         => 'nullable|int',
            'lateMonths'      => 'nullable|int',
            'transactionDate'  => 'required|date',
        ]);

        
        if ($request->application_id) {
            $form = TnelbFormP::where('application_id', $validated['application_id'])->first();
        }      

        if (!$form) {
            return response()->json([
                'status' => 404,
                'message' => 'Form details not found!',
            ]);
        }


        $payment = Payment::updateOrCreate(
            [
                'login_id'        => $validated['login_id'],
                'application_id'  => $validated['application_id'],
            ],
            [
                'transaction_id'    => $validated['transaction_id'],
                'payment_status'    => 'success',
                'amount'            => $validated['amount'],
                'form_name'         => $form->form_name,
                'license_name'      => $form->license_name,
                'payment_mode'      => $validated['payment_mode'],
                'late_fees'         => $validated['lateFee'] ?? 0,
                'late_months'       => $validated['lateMonths'] ?? 0,
                'transaction_date'  => $validated['transactionDate'] 
            ]
        );

        if ($payment) {
            $formUpdate = [
                'payment_status' => 'payment',
                'updated_at'     => now(),
            ];
            if (empty($form->submitted_date)) {
                $formUpdate['submitted_date'] = now();
            }
            TnelbFormP::where('application_id', $validated['application_id'])->update($formUpdate);
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Payment created successfully!',
            'data'    => $payment
        ]);
    }
}
