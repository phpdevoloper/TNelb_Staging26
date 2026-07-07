<?php

namespace App\Services\Competency;

use App\Models\DocumentsLog;
use App\Models\Mst_experience;
use App\Models\Mst_Form_s_w;
use App\Services\FormS\FormSDocumentVersionService;
use App\Services\FormS\FormSApplicationWorkflowService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Staff / admin views: resolve competency application media via documents_log.
 */
class CompetencyDocumentReviewService
{
    public function __construct(
        protected FormSApplicationWorkflowService $workflowService,
        protected FormSDocumentVersionService $documentVersionService
    ) {}

    /**
     * @return array{
     *     educationalQualifications: \Illuminate\Support\Collection,
     *     workExperience: Collection,
     *     uploadedPhoto: ?\App\Models\TnelbApplicantPhoto,
     *     uploadedSign: ?\App\Models\TnelbApplicantsSign,
     *     alterationProofs: Collection,
     *     parentApplication: Mst_Form_s_w
     * }
     */
    public function buildStaffReviewContext(Mst_Form_s_w $application): array
    {
        $master = $this->workflowService->masterApplication($application);
        $masterId = (string) $master->application_id;
        $childId = (string) $application->application_id;
        $workflowAppPks = array_values(array_unique(array_filter([
            (int) $application->id,
            (int) $master->id,
        ])));

        if ($this->workflowService->isChildWorkflow($application)) {
            $this->documentVersionService->ensureCarriedForwardDocuments($application);
        }

        $educationalQualifications = DB::table('tnelb_applicants_edu')
            ->where('application_id', $masterId)
            ->orderByDesc('year_of_passing')
            ->get()
            ->map(function ($row) use ($workflowAppPks) {
                $eduId = (int) ($row->id ?? 0);
                $row->document_url = competency_document_url(
                    $row->upload_document ?? null,
                    'education',
                    $eduId,
                    'certificate',
                    $workflowAppPks
                );

                return $row;
            });

        $enrichExperienceDocument = function (Mst_experience $row, bool $isNew) use ($application, $workflowAppPks) {
            $refId = (int) $row->exp_id;
            $workflowIds = $isNew
                ? [(int) $application->id]
                : $workflowAppPks;

            if (empty($row->support_document)) {
                $log = competency_find_document_log('experience', $refId, 'experience_doc', $workflowIds);
                if ($log && !empty($log->file_path)) {
                    $row->support_document = $log->file_path;
                }
            }

            $row->setAttribute('support_document_url', competency_document_url(
                $row->support_document,
                'experience',
                $refId,
                'experience_doc',
                $workflowIds
            ));

            $row->setAttribute('releive_document_url', competency_document_url(
                $row->releive_document,
                'experience',
                $refId,
                'relieving_doc',
                $workflowIds
            ));

            return $row;
        };

        $parentExperience = Mst_experience::where('application_id', $masterId)
            ->orderBy('exp_id')
            ->get()
            ->map(fn (Mst_experience $row) => $enrichExperienceDocument($row, false));

        $newExperience = collect();
        if ($childId !== $masterId && $this->workflowService->isAlterationApplication($application)) {
            $newExperience = Mst_experience::where('application_id', $childId)
                ->orderBy('exp_id')
                ->get()
                ->map(function (Mst_experience $row) use ($enrichExperienceDocument) {
                    $row->setAttribute('is_alteration_new', true);

                    return $enrichExperienceDocument($row, true);
                });
        }

        $workExperience = $parentExperience->concat($newExperience)->values();

        $alterationProofs = collect();
        if ($this->workflowService->isAlterationApplication($application)) {
            $alterationProofs = DocumentsLog::query()
                ->where('application_id', (int) $application->id)
                ->where('module_type', 'alteration')
                ->where('is_active', true)
                ->orderByDesc('id')
                ->get()
                ->unique('document_type')
                ->values()
                ->map(function (DocumentsLog $log) {
                    $type = (string) ($log->document_type ?? '');
                    $label = match ($type) {
                        'name_proof' => 'Name alteration supporting proof',
                        'address_proof' => 'Address alteration supporting proof',
                        default => ucwords(str_replace('_', ' ', $type)),
                    };

                    return (object) [
                        'document_type' => $type,
                        'label' => $label,
                        'file_name' => $log->file_name,
                        'url' => competency_document_log_download_url($log),
                    ];
                });
        }

        $uploadedPhoto = $this->resolveApplicantPhoto($application);
        $uploadedSign = $this->resolveApplicantSign($application);

        if ($uploadedPhoto && !empty($uploadedPhoto->upload_path)) {
            $uploadedPhoto->setAttribute('media_url', $this->resolveMediaUrl(
                $uploadedPhoto->upload_path,
                'photo',
                'photo',
                $workflowAppPks
            ));
        }

        if ($uploadedSign && !empty($uploadedSign->uploaded_doc)) {
            $uploadedSign->setAttribute('media_url', $this->resolveMediaUrl(
                $uploadedSign->uploaded_doc,
                'signature',
                'signature',
                $workflowAppPks
            ));
        }

        return [
            'educationalQualifications' => $educationalQualifications,
            'workExperience' => $workExperience,
            'uploadedPhoto' => $uploadedPhoto,
            'uploadedSign' => $uploadedSign,
            'alterationProofs' => $alterationProofs,
            'parentApplication' => $master,
        ];
    }

  protected function resolveMediaUrl(string $storedPath, string $moduleType, string $documentType, array $workflowAppPks): ?string
    {
        foreach ($workflowAppPks as $wfPk) {
            $url = competency_document_url($storedPath, $moduleType, $wfPk, $documentType, [$wfPk]);
            if ($url) {
                return $url;
            }
        }

        return competency_media_url($storedPath);
    }

    protected function resolveApplicantPhoto(Mst_Form_s_w $application): ?\App\Models\TnelbApplicantPhoto
    {
        foreach ($this->mediaApplicationIds($application) as $applicationId) {
            $photo = \App\Models\TnelbApplicantPhoto::where('application_id', $applicationId)->first();
            if ($photo && trim((string) ($photo->upload_path ?? '')) !== '') {
                return $photo;
            }
        }

        return null;
    }

    protected function resolveApplicantSign(Mst_Form_s_w $application): ?\App\Models\TnelbApplicantsSign
    {
        foreach ($this->mediaApplicationIds($application) as $applicationId) {
            $sign = \App\Models\TnelbApplicantsSign::where('application_id', $applicationId)->first();
            if ($sign && trim((string) ($sign->uploaded_doc ?? '')) !== '') {
                return $sign;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    protected function mediaApplicationIds(Mst_Form_s_w $application): array
    {
        $ids = [];
        $seen = [];
        $current = $application;

        while ($current) {
            $appId = trim((string) ($current->application_id ?? ''));
            if ($appId !== '' && !isset($seen[$appId])) {
                $ids[] = $appId;
                $seen[$appId] = true;
            }

            $oldId = trim((string) ($current->old_application ?? ''));
            if ($oldId === '' || isset($seen[$oldId])) {
                break;
            }

            $current = Mst_Form_s_w::where('application_id', $oldId)->first();
        }

        return $ids;
    }
}
