<?php

namespace App\Http\Controllers;

use App\Models\CC_Forms_Meta;
use App\Models\CC_Payments;
use App\Models\EA_Application_model;
use App\Models\PaymentTransactionModel;
use App\Models\TnelbFormP;
use App\Services\ApplicationOwnerResolver;
use App\Services\Competency\CompetencyMetaService;
use App\Services\Competency\FormPSchema;
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


        if ($denied = $this->denyUnlessOwner($validated['application_id'], $validated['login_id'])) {
            return $denied;
        }

        $form = CC_Forms_Meta::findByApplicationId((string) $validated['application_id']);

        if (!$form) {
            return response()->json([
                'status' => 404,
                'message' => 'Form details not found!',
            ]);
        }

        // Form P is marked paid only by a verified PayU payment (PayUPaymentSettlementService::settleSuccess).
        if (FormPSchema::isFormP((string) ($form->form_name ?? ''))) {
            return response()->json([
                'status' => 422,
                'message' => 'Form P fees are paid through the online payment gateway only.',
            ], 422);
        }

        $applType = strtoupper(trim((string) ($form->appl_type ?? '')));
        $noPaymentType = in_array($applType, ['D', 'A'], true);
        $storedPayment = strtoupper(trim((string) ($form->payment_status ?? '')));

        if ($storedPayment === 'B') {
            if (strtoupper(trim((string) ($form->app_status ?? ''))) === 'D') {
                $form->update(['app_status' => 'P']);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Application fee-exempted for this application',
                'data' => null,
            ]);
        }

        if (in_array($applType, ['N', 'R'], true)
            && (float) $validated['amount'] <= 0
            && ! $noPaymentType) {
            return response()->json([
                'status' => 422,
                'message' => 'Payment is required for new and renewal applications.',
            ], 422);
        }

        $amountPaid = (int) round((float) $validated['amount']);
        $appType = strtoupper(trim((string) ($form->appl_type ?? 'N')));
        if ($appType === '') {
            $appType = 'N';
        }

        $payment = CC_Payments::updateOrCreate(
            [
                'login_id'        => $validated['login_id'],
                'application_id'  => $validated['application_id'],
            ],
            [
                'transaction_id'    => $validated['transaction_id'],
                'payment_status'    => 'success',
                'amount_paid'       => $amountPaid,
                'application_fee'   => $amountPaid,
                'app_type'          => $appType,
                'form_name'         => $form->form_name,
                'cert_name'      => $form->certificate_name,
                'payment_mode'      => $validated['payment_mode'],
                'late_fee'         => (int) ($validated['lateFee'] ?? 0),
                'late_months'       => (int) ($validated['lateMonths'] ?? 0),
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

        if ($denied = $this->denyUnlessOwner($validated['application_id'], $validated['login_id'])) {
            return $denied;
        }

        $form = CC_Forms_Meta::findByApplicationId((string) $validated['application_id'])
            ?: TnelbFormP::where('application_id', $validated['application_id'])->first();

        if (!$form) {
            return response()->json([
                'status' => 404,
                'message' => 'Form details not found!',
            ]);
        }

        // Never mark an application paid from a client-supplied reference: the transaction must be a PayU
        // attempt for this application that was already verified as SUCCESS.
        $verifiedTxn = PaymentTransactionModel::query()
            ->where('application_id', (string) $validated['application_id'])
            ->where('txnid', (string) $validated['transaction_id'])
            ->where('status', 'SUCCESS')
            ->first();
        if (! $verifiedTxn) {
            return response()->json([
                'status' => 422,
                'message' => 'No verified online payment was found for this application.',
            ], 422);
        }


        $applType = strtoupper(trim((string) ($form->appl_type ?? $request->input('form_type') ?? '')));
        if ($applType === 'FRESH' || $applType === 'NEW') {
            $applType = 'N';
        } elseif ($applType === 'RENEWAL') {
            $applType = 'R';
        }
        if ($applType === '') {
            $applType = 'N';
        }

        // Amounts come from the verified gateway attempt, not from the browser.
        $amountPaid = (int) round((float) $verifiedTxn->amount);
        $lateFee = (int) ($verifiedTxn->late_fee ?? 0);

        $payment = CC_Payments::updateOrCreate(
            [
                'login_id'        => $validated['login_id'],
                'application_id'  => $validated['application_id'],
            ],
            [
                'transaction_id'    => $validated['transaction_id'],
                'payment_status'    => 'success',
                'amount_paid'       => $amountPaid,
                'application_fee'   => max(0, $amountPaid - $lateFee),
                'app_type'          => $applType,
                'form_name'         => $form->form_name ?: 'P',
                'cert_name'         => $form->license_name ?? $form->certificate_name ?? 'P',
                'payment_mode'      => $validated['payment_mode'],
                'late_fee'          => $lateFee,
                'late_months'       => (int) ($verifiedTxn->late_months ?? 0),
                'transaction_date'  => $validated['transactionDate'],
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
            $appStatus = strtoupper(trim((string) ($form->app_status ?? '')));
            if ($appStatus === '' || $appStatus === 'D') {
                $formUpdate['app_status'] = 'P';
            }
            $form->update($formUpdate);
            if (! $form instanceof \App\Models\TnelbFormP) {
                TnelbFormP::where('application_id', $validated['application_id'])->update($formUpdate);
            }
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Payment created successfully!',
            'data'    => $payment
        ]);
    }

    /**
     * Only the logged-in applicant who owns the application may record a payment for it, and the
     * posted login_id must be that applicant.
     */
    private function denyUnlessOwner(mixed $applicationId, mixed $postedLoginId): ?\Illuminate\Http\JsonResponse
    {
        $loginId = trim((string) (auth()->user()->login_id ?? ''));
        $applicationId = trim((string) $applicationId);

        if ($loginId === ''
            || $applicationId === ''
            || ! hash_equals($loginId, trim((string) $postedLoginId))
            || ! app(ApplicationOwnerResolver::class)->isOwnedBy($applicationId, $loginId)) {
            return response()->json([
                'status' => 403,
                'message' => 'Access denied.',
            ], 403);
        }

        return null;
    }
}
