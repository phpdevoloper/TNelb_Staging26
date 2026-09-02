<?php

namespace App\Http\Controllers\DocumentVersion;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentVersion\DeleteCcInspectRequest;
use App\Models\CC_Education;
use App\Models\CC_Experience;
use App\Models\CC_Proof_doc;
use App\Services\Competency\CompetencyApplicationPurgeService;
use App\Services\Competency\CompetencyMetaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CcInspectController extends Controller
{
    public function __construct(
        protected CompetencyMetaService $metaService,
        protected CompetencyApplicationPurgeService $purgeService
    ) {}

    public function index(Request $request): View
    {
        $applicationId = trim((string) $request->query('application_id', $request->input('application_id', '')));

        $meta = null;
        $metaTable = null;
        $educations = collect();
        $experiences = collect();
        $proofs = collect();
        $relatedApplicationIds = [];
        $childApplicationIds = [];
        $notFound = false;

        if ($applicationId !== '') {
            $meta = $this->metaService->findModel($applicationId);
            $metaTable = $this->metaService->metaTableForApplicationId($applicationId);
            if (! $meta) {
                $notFound = true;
            } else {
                $id = (string) $meta->application_id;
                $educations = CC_Education::where('application_id', $id)->orderBy('edu_id')->get();
                $experiences = CC_Experience::where('application_id', $id)->orderBy('exp_id')->get();
                $proofs = CC_Proof_doc::where('application_id', $id)->orderBy('p_id')->get();
                $relatedApplicationIds = $this->purgeService->relatedApplicationIds($id);
                $childApplicationIds = $this->purgeService->childrenOf($id);
            }
        }

        return view('document-version.sample.cc-inspect', [
            'applicationId' => $applicationId,
            'meta' => $meta,
            'metaTable' => $metaTable,
            'educations' => $educations,
            'experiences' => $experiences,
            'proofs' => $proofs,
            'relatedApplicationIds' => $relatedApplicationIds,
            'childApplicationIds' => $childApplicationIds,
            'notFound' => $notFound,
        ]);
    }

    public function destroy(DeleteCcInspectRequest $request): RedirectResponse
    {
        $applicationId = trim((string) $request->input('application_id'));

        try {
            $summary = $this->purgeService->purge($applicationId);
        } catch (\Throwable $e) {
            return redirect()
                ->route('document-version.sample.cc-inspect', ['application_id' => $applicationId])
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
            ->route('document-version.sample.cc-inspect')
            ->with('success', $message);
    }
}
