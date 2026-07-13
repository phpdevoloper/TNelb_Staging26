<?php

namespace App\Http\Controllers;

use App\Models\CC_Forms_Meta;
use App\Models\CC_Payments;
use App\Models\EA_Application_model;
use App\Models\TnelbFormP;
use App\Services\Competency\CompetencyMetaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends BaseController
{

    protected $dbNow;
    public function __construct()
    {
        parent::__construct();
        $this->dbNow  = DB::selectOne("SELECT date_trunc('second', NOW()::timestamp) AS db_now")->db_now;
    }

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


        if ($request->application_id) {
            $form = CC_Forms_Meta::findByApplicationId($validated['application_id']);
        }
        

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

        $payment = CC_Payments::updateOrCreate(
            [
                'login_id'        => $validated['login_id'],
                'application_id'  => $validated['application_id'],
            ],
            [
                'transaction_id'    => $validated['transaction_id'],
                'payment_status'    => 'success',
                'amount_paid'            => $validated['amount'],
                'form_name'         => $form->form_name,
                'cert_name'      => $form->certificate_name,
                'payment_mode'      => $validated['payment_mode'],
                'late_fee'         => $validated['lateFee'] ?? 0,
                'late_months'       => $validated['lateMonths'] ?? 0,
                'transaction_date'  => $validated['transactionDate'],
            ]
        );


        if ($payment) {
            $metaService = app(CompetencyMetaService::class);
            if ($metaService->supportsForm((string) ($form->form_name ?? ''))) {
                $form->update(['payment_status' => 'Y']);
            } else {
                EA_Application_model::where('application_id', $validated['application_id'])
                ->update([
                    'payment_status' => 'Y',
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


        $payment = CC_Payments::updateOrCreate(
            [
                'login_id'        => $validated['login_id'],
                'application_id'  => $validated['application_id'],
            ],
            [
                'transaction_id'    => $validated['transaction_id'],
                'payment_status'    => 'success',
                'amount_paid'            => $validated['amount'],
                'form_name'         => $form->form_name,
                'cert_name'      => $form->license_name,
                'payment_mode'      => $validated['payment_mode'],
                'late_fee'         => $validated['lateFee'] ?? 0,
                'late_months'       => $validated['lateMonths'] ?? 0,
                'transaction_date'  => $validated['transactionDate'] 
            ]
        );

        if ($payment) {
            $formUpdate = [
                'payment_status' => 'Y',
                'updated_at'     => $this->dbNow,
            ];
            if (empty($form->submitted_date)) {
                $formUpdate['submitted_date'] = $this->dbNow;
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
