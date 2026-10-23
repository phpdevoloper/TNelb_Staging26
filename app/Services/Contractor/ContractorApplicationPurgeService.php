<?php

namespace App\Services\Contractor;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sample-tool purge for a Form A (contractor licence) application_id
 * and descendant IDs (renewal / alteration rows whose old_application points at it).
 */
class ContractorApplicationPurgeService
{
    public function __construct(
        protected ContractorMetaService $metaService
    ) {}

    /**
     * @return list<string>
     */
    public function relatedApplicationIds(string $applicationId): array
    {
        return $this->metaService->relatedApplicationIds($applicationId);
    }

    /**
     * @return list<string>
     */
    public function childrenOf(string $parentId): array
    {
        return $this->metaService->childrenOf($parentId);
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
            throw new \RuntimeException('No Form A meta row found for this application_id.');
        }

        $applicationIds = $this->relatedApplicationIds($applicationId);
        $deleted = [];

        DB::transaction(function () use ($applicationIds, &$deleted) {
            foreach ($this->detailTables() as $table => $column) {
                $deleted[$table] = $this->deleteByIds($table, $column, $applicationIds);
            }

            $deleted[ContractorMetaService::META_TABLE] = $this->deleteByIds(
                ContractorMetaService::META_TABLE,
                'application_id',
                $applicationIds
            );
        });

        return [
            'application_ids' => $applicationIds,
            'tables' => array_filter($deleted, static fn (int $count): bool => $count > 0),
        ];
    }

    /**
     * @return array<string, string> table => column
     */
    private function detailTables(): array
    {
        return [
            'cl_ownership_table' => 'application_id',
            'cl_staff_tbl' => 'application_id',
            'tnelb_applicant_cl_staffdetails' => 'application_id',
            'tnelb_applicant_doc_A' => 'application_id',
            'tnelb_addressproof_cl' => 'application_id',
            'tnelb_attachments_cl' => 'application_id',
            'tnelb_banksolvency_a' => 'application_id',
            'tnelb_equimentsuser_cl' => 'application_id',
            'equipmentforma_tbls' => 'application_id',
            'equipment_storetmp_a' => 'application_id',
            'cl_forma_signs' => 'application_id',
            'cl_forma_lic' => 'application_id',
            'tnelb_workflow_a' => 'application_id',
            'payments' => 'application_id',
            'payment_transactions' => 'application_id',
            'cl_checklist_applicant' => 'applicant_id',
            'tnelb_return_to_applicant_log' => 'application_id',
            'tnelb_query_applicable' => 'application_id',
            'tnelb_ea_qc_models' => 'application_id',
            'cl_staff_detail_adminstore' => 'application_id',
            'tnelb_cl_validitychecks' => 'application_id',
            'mapping_digi_cls' => 'application_id',
            'tnelb_temp_uploaded_documents' => 'application_id',
            'tnelb_applicant_institute' => 'application_id',
        ];
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
