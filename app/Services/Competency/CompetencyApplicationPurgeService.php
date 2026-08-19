<?php

namespace App\Services\Competency;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sample-tool purge for a competency application_id and descendant IDs
 * (renewal / alteration rows whose old_application points at it).
 */
class CompetencyApplicationPurgeService
{
    public function __construct(
        protected CompetencyMetaService $metaService,
        protected CompetencyCertificateService $certificateService
    ) {}

    /**
     * Current application plus child / grandchild IDs linked via old_application.
     *
     * @return list<string>
     */
    public function relatedApplicationIds(string $applicationId): array
    {
        $applicationId = trim($applicationId);
        if ($applicationId === '') {
            return [];
        }

        $ids = [$applicationId];
        $queue = [$applicationId];
        $seen = [$applicationId => true];

        while ($queue !== []) {
            $parentId = array_shift($queue);
            foreach ($this->childrenOf($parentId) as $childId) {
                if (isset($seen[$childId])) {
                    continue;
                }
                $seen[$childId] = true;
                $ids[] = $childId;
                $queue[] = $childId;
            }
        }

        return $ids;
    }

    /**
     * Direct children (old_application = $parentId) across all form meta tables.
     *
     * @return list<string>
     */
    public function childrenOf(string $parentId): array
    {
        $parentId = trim($parentId);
        if ($parentId === '') {
            return [];
        }

        $children = [];
        $seen = [];

        foreach ($this->metaService->allMetaTables() as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'old_application')) {
                continue;
            }

            $rows = DB::table($table)
                ->where('old_application', $parentId)
                ->pluck('application_id');

            foreach ($rows as $child) {
                $childId = trim((string) $child);
                if ($childId === '' || isset($seen[$childId])) {
                    continue;
                }
                $seen[$childId] = true;
                $children[] = $childId;
            }
        }

        return $children;
    }

    /**
     * @return array{
     *     application_ids: list<string>,
     *     tables: array<string, int>
     * }
     */
    public function purge(string $applicationId): array
    {
        $applicationId = trim($applicationId);
        if ($applicationId === '' || $this->metaService->findModel($applicationId) === null) {
            throw new \RuntimeException('No competency meta row found for this application_id.');
        }

        $applicationIds = $this->relatedApplicationIds($applicationId);
        $numericAppIds = $this->numericAppIds($applicationIds);
        $deleted = [];

        DB::transaction(function () use ($applicationIds, $numericAppIds, &$deleted) {
            foreach ($this->sharedDetailTables() as $table => $column) {
                $deleted[$table] = $this->deleteByIds($table, $column, $applicationIds);
            }

            $deleted['cc_doc_log'] = $this->deleteDocLog($numericAppIds, $applicationIds);

            foreach ($this->workflowTables() as $table) {
                $deleted[$table] = $this->deleteByIds($table, 'application_id', $applicationIds);
            }

            foreach ($this->certificateTables() as $table) {
                $deleted[$table] = $this->deleteByIds($table, 'application_id', $applicationIds);
            }

            foreach ($this->metaService->allMetaTables() as $table) {
                $deleted[$table] = $this->deleteByIds($table, 'application_id', $applicationIds);
            }
        });

        return [
            'application_ids' => $applicationIds,
            'tables' => array_filter($deleted, static fn (int $count): bool => $count > 0),
        ];
    }

    /**
     * @param  list<string>  $applicationIds
     * @return list<int>
     */
    private function numericAppIds(array $applicationIds): array
    {
        $appIds = [];

        foreach ($this->metaService->allMetaTables() as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $rows = DB::table($table)
                ->whereIn('application_id', $applicationIds)
                ->pluck('app_id');

            foreach ($rows as $appId) {
                $appIds[] = (int) $appId;
            }
        }

        return array_values(array_unique(array_filter($appIds)));
    }

    /**
     * @return array<string, string> table => column
     */
    private function sharedDetailTables(): array
    {
        return [
            'cc_edu' => 'application_id',
            'cc_exp' => 'application_id',
            'cc_proof_doc' => 'application_id',
            'cc_payments' => 'application_id',
            'payment_transactions' => 'application_id',
            'tnelb_applicant_photos' => 'application_id',
            'tnelb_applicants_sign' => 'application_id',
            'cc_checklist_applicant' => 'applicant_id',
            'tnelb_return_to_applicant_log' => 'application_id',
        ];
    }

    /** @return list<string> */
    private function workflowTables(): array
    {
        return array_values(array_unique(array_values(CompetencySchema::WORKFLOW_TABLES)));
    }

    /** @return list<string> */
    private function certificateTables(): array
    {
        $tables = array_values(CompetencySchema::CERT_TABLES);

        foreach (['S', 'W', 'WH', 'P'] as $form) {
            $table = $this->certificateService->certTableForForm($form);
            if ($table) {
                $tables[] = $table;
            }
        }

        return array_values(array_unique($tables));
    }

    /**
     * @param  list<int>  $numericAppIds
     * @param  list<string>  $applicationIds
     */
    private function deleteDocLog(array $numericAppIds, array $applicationIds): int
    {
        if (! Schema::hasTable('cc_doc_log')) {
            return 0;
        }

        $query = DB::table('cc_doc_log');

        $query->where(function ($inner) use ($numericAppIds, $applicationIds) {
            if ($numericAppIds !== []) {
                $inner->whereIn('application_id', $numericAppIds);
                if (Schema::hasColumn('cc_doc_log', 'parent_application_id')) {
                    $inner->orWhereIn('parent_application_id', $numericAppIds);
                }
            }

            $inner->orWhereIn('application_id', $applicationIds);
        });

        return $query->delete();
    }

    /**
     * @param  list<string>  $ids
     */
    private function deleteByIds(string $table, string $column, array $ids): int
    {
        if ($ids === [] || ! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return 0;
        }

        return DB::table($table)->whereIn($column, $ids)->delete();
    }
}
