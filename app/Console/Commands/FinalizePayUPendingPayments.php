<?php

namespace App\Console\Commands;

use App\Services\PayUPaymentSettlementService;
use Illuminate\Console\Command;

class FinalizePayUPendingPayments extends Command
{
    protected $signature = 'payu:finalize-pending {txnid? : Optional specific txnid}';

    protected $description = 'Finalize PayU PENDING_VERIFICATION rows that already have status=success in gateway_response';

    public function handle(PayUPaymentSettlementService $settlement): int
    {
        $txnid = $this->argument('txnid');
        $result = $settlement->finalizePendingSuccesses($txnid);

        $this->info("Finalized: {$result['finalized']}");
        $this->info("Skipped: {$result['skipped']}");

        foreach ($result['errors'] as $error) {
            $this->error($error);
        }

        return empty($result['errors']) ? self::SUCCESS : self::FAILURE;
    }
}
