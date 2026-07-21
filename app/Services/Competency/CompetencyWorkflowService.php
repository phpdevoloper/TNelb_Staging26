<?php

namespace App\Services\Competency;

use App\Models\Admin\SupervisorModel;
use App\Models\Competency\CC_CompetencyWorkflow;
use App\Models\Competency\CC_Workflow_FormP;
use App\Models\Competency\CC_Workflow_FormS;
use App\Models\Competency\CC_Workflow_FormW;
use App\Models\Competency\CC_Workflow_FormWH;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class CompetencyWorkflowService
{
    /** @var array<string, class-string<CC_CompetencyWorkflow>> */
    private const FORM_MODEL_MAP = [
        'S' => CC_Workflow_FormS::class,
        'W' => CC_Workflow_FormW::class,
        'WH' => CC_Workflow_FormWH::class,
        'P' => CC_Workflow_FormP::class,
    ];

    /** @var array<string, string> */
    private const FORM_TABLE_MAP = [
        'S' => 'cc_workflow_forms',
        'W' => 'cc_workflow_formw',
        'WH' => 'cc_workflow_formwh',
        'P' => 'cc_workflow_formp',
    ];

    public function supportsForm(?string $formName): bool
    {
        $code = strtoupper(trim((string) $formName));

        return $code !== '' && isset(self::FORM_MODEL_MAP[$code]);
    }

    /** @return class-string<CC_CompetencyWorkflow> */
    public function modelClassForForm(string $formName): string
    {
        $code = strtoupper(trim($formName));
        if (! isset(self::FORM_MODEL_MAP[$code])) {
            throw new InvalidArgumentException("Unsupported competency workflow form: {$formName}");
        }

        return self::FORM_MODEL_MAP[$code];
    }

    public function tableForForm(string $formName): string
    {
        $code = strtoupper(trim($formName));
        if (! isset(self::FORM_TABLE_MAP[$code])) {
            throw new InvalidArgumentException("Unsupported competency workflow form: {$formName}");
        }

        return self::FORM_TABLE_MAP[$code];
    }

    public function isCcWorkflowTable(string $table): bool
    {
        return in_array($table, self::FORM_TABLE_MAP, true);
    }

    /** @return Builder<CC_CompetencyWorkflow> */
    public function queryForForm(string $formName): Builder
    {
        $modelClass = $this->modelClassForForm($formName);

        return $modelClass::query();
    }

    public function historyForApplication(string $formName, string $applicationId): Collection
    {
        return $this->queryForForm($formName)
            ->where('application_id', $applicationId)
            ->orderByDesc('w_id')
            ->get();
    }

    public function latestForApplication(string $formName, string $applicationId): ?CC_CompetencyWorkflow
    {
        return $this->queryForForm($formName)
            ->where('application_id', $applicationId)
            ->orderByDesc('w_id')
            ->first();
    }

    public function createEntry(string $formName, array $attributes): CC_CompetencyWorkflow
    {
        $modelClass = $this->modelClassForForm($formName);

        /** @var CC_CompetencyWorkflow $row */
        $row = $modelClass::create($attributes);

        return $row;
    }

    /**
     * Insert workflow row into cc_workflow_* or legacy tnelb_workflow.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function record(string $workflowTable, array $attributes): void
    {
        if ($workflowTable === 'tnelb_workflow') {
            SupervisorModel::create($attributes);

            return;
        }

        if (! $this->isCcWorkflowTable($workflowTable)) {
            throw new InvalidArgumentException("Unknown workflow table: {$workflowTable}");
        }

        $formName = array_search($workflowTable, self::FORM_TABLE_MAP, true);
        if ($formName === false) {
            throw new InvalidArgumentException("Unknown workflow table: {$workflowTable}");
        }

        $this->createEntry($formName, $this->normalizeCcWorkflowAttributes($attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function normalizeCcWorkflowAttributes(array $attributes): array
    {
        $normalized = $attributes;

        if (isset($normalized['forwarded_to']) && ! is_numeric($normalized['forwarded_to'])) {
            $normalized['forwarded_to'] = is_numeric($normalized['role_id'] ?? null)
                ? (int) $normalized['role_id']
                : null;
        }

        if (isset($normalized['processed_by']) && ! is_numeric($normalized['processed_by'])) {
            $normalized['raised_by'] = $normalized['raised_by'] ?? (string) $normalized['processed_by'];
            $normalized['processed_by'] = is_numeric($normalized['login_id'] ?? null)
                ? (int) $normalized['login_id']
                : null;
        }

        if (isset($normalized['chklist_status']) && is_array($normalized['chklist_status'])) {
            $normalized['chklist_status'] = json_encode($normalized['chklist_status']);
        }

        if (isset($normalized['queries']) && is_array($normalized['queries'])) {
            $normalized['queries'] = $normalized['queries'];
        }

        unset($normalized['id']);

        return $normalized;
    }

    /**
     * @param  list<string|\Illuminate\Database\Query\Expression>  $extraSelect
     */
    public function queryWithReturnApplicantLog(
        string $applicationId,
        string $workflowTable = 'tnelb_workflow',
        string $metaTable = 'tnelb_application_tbl',
        array $extraSelect = [],
        bool $joinMeta = true,
        bool $orderByIdDesc = true
    ): Collection {
        $pkCol = $workflowTable === 'tnelb_workflow' ? 'id' : 'w_id';
        $rolesTable = Schema::hasTable('mst_roles') ? 'mst_roles' : 'mst__roles';

        $q = DB::table("{$workflowTable} as wf");

        if ($joinMeta) {
            $q->leftJoin("{$metaTable} as app_tbl", 'wf.application_id', '=', 'app_tbl.application_id');
        }

        if (Schema::hasTable($rolesTable)) {
            $q->leftJoin("{$rolesTable} as mr", 'wf.forwarded_to', '=', 'mr.r_id');
        }

        $q->where('wf.application_id', $applicationId);

        $select = array_merge([
            'wf.*',
            DB::raw("wf.{$pkCol} as id"),
        ], $extraSelect);

        if (Schema::hasTable($rolesTable)) {
            $select[] = DB::raw('mr.role_name as role_name');
        }

        if (Schema::hasTable('tnelb_return_to_applicant_log')) {
            $logTable = 'tnelb_return_to_applicant_log';
            $colQ = Schema::hasColumn($logTable, 'query_types')
                ? 'r.query_types as return_query_types'
                : 'NULL::json';
            $colR = Schema::hasColumn($logTable, 'remarks')
                ? 'r.remarks as return_remarks'
                : 'NULL::text';

            if ($workflowTable !== 'tnelb_workflow') {
                $subFromWhere = "FROM {$logTable} r
                WHERE r.application_id = wf.application_id
                  AND TRIM(wf.appl_status) = 'QU'
                  AND (
                    r.returned_by_role = TRIM(COALESCE(wf.raised_by::text, ''))
                    OR r.returned_by_staff_id = wf.processed_by
                    OR r.returned_by_staff_id::text = TRIM(COALESCE(wf.login_id::text, ''))
                  )
                ORDER BY abs(extract(epoch from (wf.created_at - r.created_at)))
                LIMIT 1";
            } else {
                $subFromWhere = "FROM {$logTable} r
                WHERE r.application_id = wf.application_id
                  AND TRIM(wf.appl_status) = 'QU'
                  AND TRIM(wf.processed_by::text) IN ('SE', 'PR')
                  AND r.returned_by_role = TRIM(wf.processed_by::text)
                ORDER BY abs(extract(epoch from (wf.created_at - r.created_at)))
                LIMIT 1";
            }

            $select[] = DB::raw("(SELECT {$colQ} {$subFromWhere}) AS return_query_types_raw");
            $select[] = DB::raw("(SELECT {$colR} {$subFromWhere}) AS return_remarks_raw");
        }

        $q->select($select);

        if ($orderByIdDesc) {
            $q->orderByDesc("wf.{$pkCol}");
        }

        return $this->hydrateWorkflowReturnLogFields(
            $this->hydrateProcessedByForDisplay($q->get(), $workflowTable)
        );
    }

    /**
     * @param  \Illuminate\Support\Collection<int, object>  $workflows
     * @return \Illuminate\Support\Collection<int, object>
     */
    public function hydrateProcessedByForDisplay(Collection $workflows, string $workflowTable): Collection
    {
        if ($workflowTable === 'tnelb_workflow') {
            return $workflows;
        }

        foreach ($workflows as $row) {
            if (isset($row->raised_by) && trim((string) $row->raised_by) !== '' && ! ctype_digit(trim((string) $row->raised_by))) {
                $row->processed_by = $row->raised_by;

                continue;
            }

            if (isset($row->processed_by) && is_numeric($row->processed_by)) {
                $staff = DB::table('mst_login_users')->where('s_id', (int) $row->processed_by)->first();
                if ($staff) {
                    $row->processed_by = match ($staff->user_name ?? $staff->name ?? '') {
                        'president' => 'PR',
                        'secretary' => 'SE',
                        'supervisor', 'supervisor2' => 'S',
                        'assistantsecretary' => 'A',
                        default => strtoupper(substr((string) ($staff->user_name ?? 'A'), 0, 2)),
                    };
                }
            }
        }

        return $workflows;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, object>  $workflows
     * @return \Illuminate\Support\Collection<int, object>
     */
    public function hydrateWorkflowReturnLogFields(Collection $workflows): Collection
    {
        foreach ($workflows as $row) {
            $row->return_log_internal_queries = [];
            $row->return_log_internal_remarks = null;

            if (property_exists($row, 'return_query_types_raw')) {
                $raw = $row->return_query_types_raw;
                unset($row->return_query_types_raw);
                if ($raw !== null && $raw !== '') {
                    $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
                    if (is_array($decoded)) {
                        $row->return_queries = $decoded;
                        $row->return_log_internal_queries = $decoded;
                    }
                }
            }

            if (property_exists($row, 'return_remarks_raw')) {
                $fromLog = $row->return_remarks_raw;
                if ($fromLog !== null && $fromLog !== '') {
                    $row->return_log_internal_remarks = $fromLog;
                    $existing = $row->return_remarks ?? null;
                    if ($existing === null || $existing === '') {
                        $row->return_remarks = $fromLog;
                    }
                }
                unset($row->return_remarks_raw);
            }

            if (
                strtoupper(trim((string) ($row->appl_status ?? ''))) === 'QU'
                && (
                    empty($row->return_remarks)
                    || empty($row->return_log_internal_queries)
                )
            ) {
                $this->attachNearestReturnLogToWorkflowRow($row);
            }
        }

        return $workflows;
    }

    private function attachNearestReturnLogToWorkflowRow(object $row): void
    {
        if (! Schema::hasTable('tnelb_return_to_applicant_log')) {
            return;
        }

        $query = DB::table('tnelb_return_to_applicant_log as r')
            ->where('r.application_id', $row->application_id ?? '');

        $raisedBy = strtoupper(trim((string) ($row->raised_by ?? '')));
        $processedBy = strtoupper(trim((string) ($row->processed_by ?? '')));

        if (in_array($raisedBy, ['SE', 'PR'], true)) {
            $query->where('r.returned_by_role', $raisedBy);
        } elseif (in_array($processedBy, ['SE', 'PR'], true)) {
            $query->where('r.returned_by_role', $processedBy);
        } elseif (isset($row->processed_by) && is_numeric($row->processed_by)) {
            $query->where('r.returned_by_staff_id', (int) $row->processed_by);
        } elseif (isset($row->login_id) && is_numeric($row->login_id)) {
            $query->where('r.returned_by_staff_id', (int) $row->login_id);
        }

        if (! empty($row->created_at)) {
            $query->orderByRaw('abs(extract(epoch from (?::timestamp - r.created_at))) asc', [$row->created_at]);
        } else {
            $query->orderByDesc('r.created_at');
        }

        $logRow = $query->first();
        if (! $logRow) {
            return;
        }

        if (empty($row->return_remarks) && ! empty($logRow->remarks)) {
            $row->return_remarks = $logRow->remarks;
            $row->return_log_internal_remarks = $logRow->remarks;
        }

        if (empty($row->return_log_internal_queries) && ! empty($logRow->query_types)) {
            $decoded = is_string($logRow->query_types) ? json_decode($logRow->query_types, true) : $logRow->query_types;
            if (is_array($decoded)) {
                $row->return_queries = $decoded;
                $row->return_log_internal_queries = array_values(array_filter(
                    $decoded,
                    static fn ($item) => is_string($item) && $item !== ''
                ));
            }
        }
    }
}
