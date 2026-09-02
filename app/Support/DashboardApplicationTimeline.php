<?php

namespace App\Support;

use App\Services\Competency\CompetencyCertificateService;
use App\Services\Competency\CompetencyMetaService;
use App\Services\Competency\CompetencyWorkflowService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Testing helper: rebuilds an application’s process history from creation onward.
 */
class DashboardApplicationTimeline
{
    public function __construct(
        private CompetencyMetaService $metaService,
        private CompetencyWorkflowService $workflowService,
        private CompetencyCertificateService $certificateService,
    ) {
    }

    /**
     * @return array{
     *     application_id: string,
     *     form_name: string,
     *     appl_type: string,
     *     appl_type_label: string,
     *     applicant_name: string,
     *     current_status: string,
     *     current_status_label: string,
     *     payment_status: string,
     *     payment_status_label: string,
     *     created_at: ?string,
     *     root_id: string,
     *     lineage: list<array<string, mixed>>,
     *     tree: array<string, mixed>,
     *     chapters: list<array<string, mixed>>,
     *     events: list<array<string, mixed>>
     * }
     */
    public function build(string $applicationId, object $application): array
    {
        $applicationId = trim($applicationId);
        $formName = strtoupper(trim((string) ($application->form_name ?? '')));
        $applType = strtoupper(trim((string) ($application->appl_type ?? '')));
        $currentStatus = strtoupper(trim((string) ($application->app_status ?? $application->status ?? '')));
        $paymentStatus = strtolower(trim((string) ($application->payment_status ?? '')));

        $family = $this->buildFamilyTree($applicationId);
        $chapters = [];
        $allEvents = collect();

        foreach ($family['order'] as $familyId) {
            $row = $family['nodes'][$familyId] ?? null;
            if (! $row) {
                continue;
            }

            $chapterEvents = $this->eventsForApplication($familyId, $row);
            $chapters[] = [
                'application_id' => $familyId,
                'is_current' => $familyId === $applicationId,
                'parent_id' => trim((string) ($row->old_application ?? '')) ?: null,
                'form_name' => strtoupper(trim((string) ($row->form_name ?? ''))) ?: '—',
                'appl_type' => strtoupper(trim((string) ($row->appl_type ?? ''))),
                'appl_type_label' => $this->applTypeLabel(strtoupper(trim((string) ($row->appl_type ?? '')))),
                'status' => strtoupper(trim((string) ($row->app_status ?? $row->status ?? ''))),
                'status_label' => $this->statusLabel(strtoupper(trim((string) ($row->app_status ?? $row->status ?? '')))),
                'created_at' => $this->formatAt($this->toCarbon($row->created_at ?? null)),
                'events' => $chapterEvents,
            ];
            $allEvents = $allEvents->merge($chapterEvents);
        }

        return [
            'application_id' => $applicationId,
            'form_name' => $formName !== '' ? $formName : '—',
            'appl_type' => $applType,
            'appl_type_label' => $this->applTypeLabel($applType),
            'applicant_name' => trim((string) ($application->applicant_name ?? '')),
            'current_status' => $currentStatus,
            'current_status_label' => $this->statusLabel($currentStatus),
            'payment_status' => $paymentStatus,
            'payment_status_label' => $this->paymentLabel($paymentStatus),
            'created_at' => $this->formatAt($this->toCarbon($application->created_at ?? null)),
            'root_id' => $family['root'],
            'lineage' => $family['lineage'],
            'tree' => $family['tree'],
            'chapters' => $chapters,
            'events' => $allEvents->values()->all(),
        ];
    }

    /**
     * Walk up to the original New/Digitisation, then down every renewal/alteration child.
     *
     * @return array{
     *     root: string,
     *     order: list<string>,
     *     nodes: array<string, object>,
     *     tree: array<string, mixed>,
     *     lineage: list<array<string, mixed>>
     * }
     */
    private function buildFamilyTree(string $applicationId): array
    {
        $rootId = $this->findRootApplicationId($applicationId);
        $nodes = [];
        $childrenMap = [];
        $order = [];
        $queue = [$rootId];
        $seen = [$rootId => true];

        while ($queue !== []) {
            $id = array_shift($queue);
            $row = $this->findApplicationRow($id);
            if (! $row) {
                continue;
            }
            $nodes[$id] = $row;
            $childIds = $this->childrenOf($id);
            $childrenMap[$id] = $childIds;
            foreach ($childIds as $childId) {
                if (isset($seen[$childId])) {
                    continue;
                }
                $seen[$childId] = true;
                $queue[] = $childId;
            }
        }

        if (! isset($nodes[$applicationId])) {
            $current = $this->findApplicationRow($applicationId);
            if ($current) {
                $nodes[$applicationId] = $current;
            }
        }

        $tree = $this->treeNode($rootId, $applicationId, $nodes, $childrenMap, $order);
        if ($tree === null && isset($nodes[$applicationId])) {
            $tree = $this->treeNode($applicationId, $applicationId, $nodes, $childrenMap, $order);
            $rootId = $applicationId;
        }

        $lineage = [];
        $walk = $applicationId;
        $guard = [];
        while ($walk !== '' && ! isset($guard[$walk])) {
            $guard[$walk] = true;
            $row = $nodes[$walk] ?? $this->findApplicationRow($walk);
            if (! $row) {
                break;
            }
            array_unshift($lineage, $this->familySummary($walk, $row, $walk === $applicationId));
            $parentId = trim((string) ($row->old_application ?? ''));
            if ($parentId === '') {
                break;
            }
            $walk = $parentId;
        }

        return [
            'root' => $rootId,
            'order' => $order !== [] ? $order : array_keys($nodes),
            'nodes' => $nodes,
            'tree' => $tree ?? $this->familySummary($applicationId, $nodes[$applicationId] ?? (object) ['application_id' => $applicationId], true),
            'lineage' => $lineage,
        ];
    }

    /**
     * @param  array<string, object>  $nodes
     * @param  array<string, list<string>>  $childrenMap
     * @param  list<string>  $order
     * @return array<string, mixed>|null
     */
    private function treeNode(
        string $id,
        string $currentId,
        array $nodes,
        array $childrenMap,
        array &$order
    ): ?array {
        $row = $nodes[$id] ?? null;
        if (! $row) {
            return null;
        }

        $order[] = $id;
        $children = [];
        foreach ($childrenMap[$id] ?? [] as $childId) {
            if (in_array($childId, $order, true)) {
                continue;
            }
            $child = $this->treeNode($childId, $currentId, $nodes, $childrenMap, $order);
            if ($child !== null) {
                $children[] = $child;
            }
        }

        $node = $this->familySummary($id, $row, $id === $currentId);
        $node['children'] = $children;

        return $node;
    }

    /**
     * @return array<string, mixed>
     */
    private function familySummary(string $id, object $row, bool $isCurrent): array
    {
        $type = strtoupper(trim((string) ($row->appl_type ?? '')));
        $status = strtoupper(trim((string) ($row->app_status ?? $row->status ?? '')));

        return [
            'application_id' => $id,
            'is_current' => $isCurrent,
            'parent_id' => trim((string) ($row->old_application ?? '')) ?: null,
            'form_name' => strtoupper(trim((string) ($row->form_name ?? ''))) ?: '—',
            'appl_type' => $type,
            'appl_type_label' => $this->applTypeLabel($type),
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'created_at' => $this->formatAt($this->toCarbon($row->created_at ?? null)),
            'children' => [],
        ];
    }

    private function findRootApplicationId(string $applicationId): string
    {
        $currentId = $applicationId;
        $seen = [];

        while ($currentId !== '' && ! isset($seen[$currentId])) {
            $seen[$currentId] = true;
            $row = $this->findApplicationRow($currentId);
            $parentId = trim((string) ($row->old_application ?? ''));
            if ($parentId === '' || $this->findApplicationRow($parentId) === null) {
                return $currentId;
            }
            $currentId = $parentId;
        }

        return $applicationId;
    }

    /**
     * @return list<string>
     */
    private function childrenOf(string $parentId): array
    {
        $parentId = trim($parentId);
        if ($parentId === '') {
            return [];
        }

        $children = [];
        $seen = [];

        foreach ($this->applicationLookupTables() as $table) {
            if (! Schema::hasColumn($table, 'old_application') || ! Schema::hasColumn($table, 'application_id')) {
                continue;
            }

            $query = DB::table($table)->where('old_application', $parentId);
            if (Schema::hasColumn($table, 'created_at')) {
                $query->orderBy('created_at')->orderBy('application_id');
            } else {
                $query->orderBy('application_id');
            }
            $rows = $query->get(['application_id']);

            foreach ($rows as $row) {
                $childId = trim((string) ($row->application_id ?? ''));
                if ($childId === '' || isset($seen[$childId])) {
                    continue;
                }
                $seen[$childId] = true;
                $children[] = $childId;
            }
        }

        return $children;
    }

    private function findApplicationRow(string $applicationId): ?object
    {
        $applicationId = trim($applicationId);
        if ($applicationId === '') {
            return null;
        }

        foreach ($this->applicationLookupTables() as $table) {
            $row = DB::table($table)->where('application_id', $applicationId)->first();
            if ($row) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function applicationLookupTables(): array
    {
        $tables = [];
        foreach ($this->metaService->allMetaTables() as $table) {
            if (Schema::hasTable($table)) {
                $tables[] = $table;
            }
        }
        foreach (['tnelb_form_p', 'tnelb_application_tbl'] as $table) {
            if (Schema::hasTable($table) && ! in_array($table, $tables, true)) {
                $tables[] = $table;
            }
        }

        return $tables;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function eventsForApplication(string $applicationId, object $application): array
    {
        $formName = strtoupper(trim((string) ($application->form_name ?? '')));

        return collect()
            ->merge($this->createdEvents($application))
            ->merge($this->paymentEvents($applicationId))
            ->merge($this->payuTransactionEvents($applicationId))
            ->merge($this->workflowEvents($applicationId, $formName))
            ->merge($this->certificateEvents($applicationId, $formName))
            ->filter()
            ->sortBy(function (array $event) {
                $ts = $event['at'] instanceof Carbon ? $event['at']->timestamp : 0;

                return sprintf('%015d-%s', $ts, $event['sort'] ?? 'm');
            })
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function createdEvents(object $application): Collection
    {
        $events = collect();
        $createdAt = $this->toCarbon($application->created_at ?? null);
        $submittedAt = $this->toCarbon($application->submitted_date ?? null);
        $applType = strtoupper(trim((string) ($application->appl_type ?? '')));
        $appId = trim((string) ($application->application_id ?? ''));
        $createdTitle = match ($applType) {
            'N' => 'New application created',
            'R' => 'Renewal application created',
            'D' => 'Digitisation application created',
            'A' => 'Alteration application created',
            default => 'Application created in this system',
        };

        if ($createdAt) {
            $events->push($this->event(
                $createdAt,
                $createdTitle,
                'First record saved for this application.',
                'muted',
                'CREATED',
                array_values(array_filter([
                    $appId !== '' ? 'Application ID: '.$appId : null,
                    'Application type: '.$this->applTypeLabel($applType),
                    ! empty($application->old_application) ? 'Parent application: '.$application->old_application : null,
                    'Source: application master (created_at)',
                ])),
                'a'
            ));
        }

        if ($submittedAt && (! $createdAt || $submittedAt->diffInSeconds($createdAt) > 5)) {
            $events->push($this->event(
                $submittedAt,
                'Application submitted',
                'Submitted date recorded on the application master.',
                'info',
                'P',
                array_values(array_filter([
                    $appId !== '' ? 'Application ID: '.$appId : null,
                    'Source: application master (submitted_date)',
                ])),
                'b'
            ));
        }

        return $events;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function paymentEvents(string $applicationId): Collection
    {
        if (! Schema::hasTable('cc_payments')) {
            return collect();
        }

        return DB::table('cc_payments')
            ->where('application_id', $applicationId)
            ->orderBy('p_id')
            ->get()
            ->map(function ($row) {
                $status = strtolower(trim((string) ($row->payment_status ?? '')));
                $paid = in_array($status, ['y', 'payment', 'paid', 'success'], true);
                $amount = $row->amount_paid ?? $row->application_fee ?? null;
                $detail = $paid ? 'Payment recorded as successful.' : 'Payment record saved.';
                $lines = array_values(array_filter([
                    $amount !== null && $amount !== '' ? 'Amount: ₹'.$amount : null,
                    ! empty($row->transaction_id) ? 'Transaction ID: '.$row->transaction_id : null,
                    ! empty($row->payment_mode) ? 'Mode: '.$row->payment_mode : null,
                    'Status: '.($row->payment_status ?: '—'),
                    'Source: cc_payments',
                ]));

                return $this->event(
                    $this->toCarbon($row->transaction_date ?? $row->created_at ?? null),
                    $paid ? 'Payment successful' : 'Payment recorded',
                    $detail,
                    $paid ? 'success' : 'warning',
                    strtoupper($status !== '' ? $status : 'PAY'),
                    $lines,
                    'c'
                );
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function payuTransactionEvents(string $applicationId): Collection
    {
        if (! Schema::hasTable('payment_transactions')) {
            return collect();
        }

        return DB::table('payment_transactions')
            ->where('application_id', $applicationId)
            ->orderBy('id')
            ->get()
            ->map(function ($row) {
                $status = strtolower(trim((string) ($row->status ?? '')));
                $tone = match (true) {
                    in_array($status, ['success', 'captured', 'paid'], true) => 'success',
                    in_array($status, ['failure', 'failed', 'cancel', 'cancelled'], true) => 'danger',
                    default => 'warning',
                };
                $lines = array_values(array_filter([
                    ! empty($row->txnid) ? 'Txn ID: '.$row->txnid : null,
                    $row->amount !== null && $row->amount !== '' ? 'Amount: ₹'.$row->amount : null,
                    ! empty($row->gateway) ? 'Gateway: '.$row->gateway : null,
                    ! empty($row->payment_method) ? 'Method: '.$row->payment_method : null,
                    ! empty($row->error_message) ? 'Error: '.$row->error_message : null,
                    'Gateway status: '.($row->status ?: '—'),
                    'Source: payment_transactions',
                ]));

                return $this->event(
                    $this->toCarbon($row->created_at ?? null),
                    'PayU / gateway attempt',
                    'Gateway transaction for this application.',
                    $tone,
                    strtoupper($status !== '' ? $status : 'TXN'),
                    $lines,
                    'd'
                );
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function workflowEvents(string $applicationId, string $formName): Collection
    {
        [$workflowTable, $metaTable] = $this->resolveWorkflowTables($applicationId, $formName);
        if ($workflowTable === null || ! Schema::hasTable($workflowTable)) {
            return collect();
        }

        $joinMeta = $metaTable !== null && Schema::hasTable($metaTable);

        $rows = $this->workflowService->queryWithReturnApplicantLog(
            $applicationId,
            $workflowTable,
            $metaTable ?? 'cc_form_s_meta',
            [],
            $joinMeta,
            true
        )->sortBy(function ($row) {
            $at = $this->toCarbon($row->created_at ?? null);

            return sprintf('%015d-%010d', $at?->timestamp ?? 0, (int) ($row->id ?? $row->w_id ?? 0));
        })->values();

        return $rows->map(function ($row) {
            $status = strtoupper(trim((string) ($row->appl_status ?? '')));
            $processedBy = trim((string) ($row->processed_by ?? ''));
            $roleLabel = $this->roleLabel($processedBy);
            $forwardedRole = trim((string) ($row->role_name ?? $row->name ?? ''));
            $isApplicantResubmission = $status === 'RE' && strtoupper($processedBy) === 'AP';
            $isExternalReturn = $status === 'QU' && in_array(strtoupper($processedBy), ['SE', 'PR'], true);

            $title = match (true) {
                $isApplicantResubmission => 'Resubmitted by Applicant',
                $isExternalReturn => 'Returned to Applicant by '.$roleLabel,
                $status === 'RE', $status === 'PRE', $status === 'RET' => 'Returned by '.$roleLabel,
                $status === 'A' => 'Approved by '.$roleLabel,
                $status === 'RJ' => 'Rejected by '.$roleLabel,
                $status === 'F', $status === 'RF' => 'Forwarded by '.$roleLabel,
                $status === 'P' => 'Submitted / processed by '.$roleLabel,
                default => 'Processed by '.$roleLabel,
            };

            $tone = match ($status) {
                'A' => 'success',
                'RJ' => 'danger',
                'QU', 'PRE', 'RET' => 'warning',
                'RE', 'RF' => 'info',
                default => 'info',
            };

            $lines = [];
            if ($forwardedRole !== '') {
                $lines[] = 'Forwarded to: '.$forwardedRole;
            }
            if (! empty($row->remarks)) {
                $lines[] = 'Remarks: '.$row->remarks;
            }
            if (! empty($row->return_remarks) && (string) $row->return_remarks !== (string) ($row->remarks ?? '')) {
                $lines[] = 'Return remarks: '.$row->return_remarks;
            }
            if (! empty($row->reject_reason)) {
                $lines[] = 'Reject reason: '.$row->reject_reason;
            }

            $queries = $this->stringList($row->return_log_internal_queries ?? $row->return_queries ?? $row->queries ?? []);
            if ($queries !== []) {
                $lines[] = 'Queries: '.implode('; ', $queries);
            }
            if (! empty($row->query_status)) {
                $lines[] = 'Query status: '.$row->query_status;
            }
            $lines[] = 'Workflow status: '.$status.' ('.$this->statusLabel($status).')';
            $lines[] = 'Processed by: '.$roleLabel.($processedBy !== '' ? ' ['.$processedBy.']' : '');
            $lines[] = 'Source: workflow';

            return $this->event(
                $this->toCarbon($row->created_at ?? null),
                $title,
                $this->statusLabel($status),
                $tone,
                $status !== '' ? $status : 'WF',
                $lines,
                'e'
            );
        });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function certificateEvents(string $applicationId, string $formName): Collection
    {
        $events = collect();
        $cert = $this->certificateService->findByApplicationId($applicationId, $formName ?: null);
        if ($cert) {
            $issuedAt = $this->toCarbon($cert->dateof_issue ?? $cert->created_at ?? null);
            $events->push($this->event(
                $issuedAt,
                'Certificate issued',
                'Certificate generated for this application.',
                'success',
                'CERT',
                array_values(array_filter([
                    ! empty($cert->certificate_no) ? 'Certificate no: '.$cert->certificate_no : null,
                    ! empty($cert->valid_from) ? 'Valid from: '.$this->formatDateOnly($this->toCarbon($cert->valid_from)) : null,
                    ! empty($cert->valid_to) ? 'Valid to: '.$this->formatDateOnly($this->toCarbon($cert->valid_to)) : null,
                    'Source: competency certificate table',
                ])),
                'f'
            ));

            return $events;
        }

        if (Schema::hasTable('tnelb_license')) {
            $license = DB::table('tnelb_license')->where('application_id', $applicationId)->first();
            if ($license) {
                $events->push($this->event(
                    $this->toCarbon($license->issued_at ?? $license->created_at ?? null),
                    'Certificate issued',
                    'License row created for this application.',
                    'success',
                    'CERT',
                    array_values(array_filter([
                        ! empty($license->license_number) ? 'License no: '.$license->license_number : null,
                        ! empty($license->expires_at) ? 'Valid to: '.$this->formatDateOnly($this->toCarbon($license->expires_at)) : null,
                        'Source: tnelb_license',
                    ])),
                    'f'
                ));
            }
        }

        return $events;
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function resolveWorkflowTables(string $applicationId, string $formName): array
    {
        $metaTable = $this->metaService->metaTableForApplicationId($applicationId);
        if ($metaTable === null && Schema::hasTable('tnelb_form_p')
            && DB::table('tnelb_form_p')->where('application_id', $applicationId)->exists()) {
            $metaTable = 'tnelb_form_p';
        }
        if ($metaTable === null && Schema::hasTable('tnelb_application_tbl')
            && DB::table('tnelb_application_tbl')->where('application_id', $applicationId)->exists()) {
            $metaTable = 'tnelb_application_tbl';
        }

        if ($formName !== '' && $this->workflowService->supportsForm($formName)) {
            $ccTable = $this->workflowService->tableForForm($formName);
            if (Schema::hasTable($ccTable)
                && DB::table($ccTable)->where('application_id', $applicationId)->exists()) {
                return [$ccTable, $metaTable];
            }
        }

        if (Schema::hasTable('tnelb_workflow')
            && DB::table('tnelb_workflow')->where('application_id', $applicationId)->exists()) {
            return ['tnelb_workflow', $metaTable];
        }

        if ($formName !== '' && $this->workflowService->supportsForm($formName)) {
            return [$this->workflowService->tableForForm($formName), $metaTable];
        }

        return [Schema::hasTable('tnelb_workflow') ? 'tnelb_workflow' : null, $metaTable];
    }

    /**
     * @param  list<string>  $lines
     * @return array<string, mixed>
     */
    private function event(
        ?Carbon $at,
        string $title,
        string $detail,
        string $tone,
        string $badge,
        array $lines,
        string $sort
    ): array {
        return [
            'at' => $at,
            'at_label' => $this->formatAt($at),
            'title' => $title,
            'detail' => $detail,
            'tone' => $tone,
            'badge' => $badge,
            'lines' => $lines,
            'sort' => $sort,
        ];
    }

    private function toCarbon(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }
        if ($value instanceof Carbon) {
            return $value;
        }
        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function formatAt(?Carbon $at): ?string
    {
        return $at?->format('d-m-Y h:i A');
    }

    private function formatDateOnly(?Carbon $at): ?string
    {
        return $at?->format('d-m-Y');
    }

    private function applTypeLabel(string $code): string
    {
        return match ($code) {
            'N' => 'New',
            'R' => 'Renewal',
            'D' => 'Digitisation',
            'A' => 'Alteration',
            default => $code !== '' ? $code : '—',
        };
    }

    private function statusLabel(string $code): string
    {
        return match ($code) {
            'D' => 'Draft',
            'P' => 'Submitted',
            'F' => 'In progress (forwarded)',
            'RF' => 'Re-forwarded',
            'QU' => 'Returned to applicant',
            'RE' => 'Resubmitted',
            'PRE' => 'Internally returned',
            'RET' => 'Returned',
            'A' => 'Approved / completed',
            'RJ' => 'Rejected',
            default => $code !== '' ? $code : 'Unknown',
        };
    }

    private function paymentLabel(string $status): string
    {
        return match ($status) {
            'n', 'draft' => 'Draft / unpaid',
            'y', 'payment', 'paid', 'success' => 'Paid',
            default => $status !== '' ? $status : '—',
        };
    }

    private function roleLabel(string $processedBy): string
    {
        return match (strtoupper(trim($processedBy))) {
            'SE' => 'Secretary',
            'PR' => 'President',
            'S', 'S2' => 'Supervisor',
            'A', 'AC' => 'Assistant Secretary',
            'AP' => 'Applicant',
            'ASSISTANT SECRETARY' => 'Assistant Secretary',
            default => $processedBy !== '' ? $processedBy : 'System',
        };
    }

    /**
     * @param  mixed  $value
     * @return list<string>
     */
    private function stringList(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [$value];
        }
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(
            $value,
            static fn ($item) => is_string($item) && trim($item) !== ''
        ));
    }
}
