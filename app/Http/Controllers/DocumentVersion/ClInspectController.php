<?php

namespace App\Http\Controllers\DocumentVersion;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentVersion\DeleteClInspectRequest;
use App\Services\Contractor\ContractorApplicationPurgeService;
use App\Services\Contractor\ContractorMetaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ClInspectController extends Controller
{
    public function __construct(
        protected ContractorMetaService $metaService,
        protected ContractorApplicationPurgeService $purgeService
    ) {}

    public function index(Request $request): View
    {
        $applicationId = trim((string) $request->query('application_id', $request->input('application_id', '')));

        $meta = null;
        $notFound = false;
        $relatedApplicationIds = [];
        $childApplicationIds = [];
        $tables = [];

        if ($applicationId !== '') {
            $meta = $this->metaService->findModel($applicationId);
            if (! $meta) {
                $notFound = true;
            } else {
                $id = (string) $meta->application_id;
                $relatedApplicationIds = $this->purgeService->relatedApplicationIds($id);
                $childApplicationIds = $this->purgeService->childrenOf($id);
                $tables = $this->loadTables($id);
            }
        }

        return view('document-version.sample.cl-inspect', [
            'applicationId' => $applicationId,
            'meta' => $meta,
            'metaTable' => ContractorMetaService::META_TABLE,
            'notFound' => $notFound,
            'relatedApplicationIds' => $relatedApplicationIds,
            'childApplicationIds' => $childApplicationIds,
            'tables' => $tables,
        ]);
    }

    public function destroy(DeleteClInspectRequest $request): RedirectResponse
    {
        $applicationId = trim((string) $request->input('application_id'));

        try {
            $summary = $this->purgeService->purge($applicationId);
        } catch (\Throwable $e) {
            return redirect()
                ->route('document-version.sample.cl-inspect', ['application_id' => $applicationId])
                ->with('error', 'Delete failed: ' . $e->getMessage());
        }

        $tableBits = [];
        foreach ($summary['tables'] as $table => $count) {
            $tableBits[] = $table . ' (' . $count . ')';
        }

        $message = 'Deleted application ID(s) ' . implode(', ', $summary['application_ids']) . '.';
        if ($tableBits !== []) {
            $message .= ' Rows removed from: ' . implode(', ', $tableBits) . '.';
        }

        return redirect()
            ->route('document-version.sample.cl-inspect')
            ->with('success', $message);
    }

    /**
     * @return array<string, array{rows: Collection<int, object>, columns: list<string>}>
     */
    private function loadTables(string $applicationId): array
    {
        $map = [
            'cl_ownership_table' => 'id',
            'cl_staff_tbl' => 'id',
            'tnelb_applicant_doc_A' => 'id',
            'tnelb_addressproof_cl' => 'id',
            'tnelb_attachments_cl' => 'id',
            'tnelb_banksolvency_a' => 'id',
            'tnelb_equimentsuser_cl' => 'id',
            'equipmentforma_tbls' => 'id',
            'cl_forma_signs' => 'id',
            'cl_forma_lic' => 'id',
            'tnelb_workflow_a' => 'id',
            'payments' => 'id',
            'payment_transactions' => 'id',
            'tnelb_temp_uploaded_documents' => 'id',
            'mapping_digi_cls' => 'id',
            'tnelb_query_applicable' => 'id',
            'cl_checklist_applicant' => 'id',
            'tnelb_cl_validitychecks' => 'id',
            'tnelb_ea_qc_models' => 'id',
        ];

        $out = [];
        foreach ($map as $table => $orderColumn) {
            $out[$table] = $this->loadTable($table, $applicationId, $orderColumn);
        }

        if (isset($out['cl_checklist_applicant']) && $out['cl_checklist_applicant']['rows']->isEmpty()) {
            $out['cl_checklist_applicant'] = $this->loadTable('cl_checklist_applicant', $applicationId, 'id', 'applicant_id');
        }

        return $out;
    }

    /**
     * @return array{rows: Collection<int, object>, columns: list<string>}
     */
    private function loadTable(string $table, string $applicationId, string $orderColumn, string $matchColumn = 'application_id'): array
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $matchColumn)) {
            return ['rows' => collect(), 'columns' => []];
        }

        $columns = Schema::getColumnListing($table);
        $query = DB::table($table)->where($matchColumn, $applicationId);
        if (Schema::hasColumn($table, $orderColumn)) {
            $query->orderBy($orderColumn);
        }

        return [
            'rows' => $query->get(),
            'columns' => $columns,
        ];
    }
}
