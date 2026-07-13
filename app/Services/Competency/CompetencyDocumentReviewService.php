<?php

namespace App\Services\Competency;

use App\Models\CC_Doc_Log;
use App\Models\CC_Education;
use App\Models\CC_Experience;
use App\Models\CC_Forms_Meta;
use App\Models\Competency\CC_CompetencyMeta;
use App\Models\DocumentsLog;
use App\Services\FormS\FormSDocumentVersionService;
use App\Services\FormS\FormSApplicationWorkflowService;
use App\Services\FormS\FormSProofDocumentService;
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
     *     educationalQualifications: Collection,
     *     workExperience: Collection,
     *     uploadedPhoto: ?\App\Models\TnelbApplicantPhoto,
     *     uploadedSign: ?\App\Models\TnelbApplicantsSign,
     *     alterationProofs: Collection,
     *     parentApplication: CC_CompetencyMeta
     * }
     */
    public function buildStaffReviewContext(CC_CompetencyMeta $application): array
    {
        $master = $this->workflowService->masterApplication($application);
        $masterId = (string) $master->application_id;
        $childId = (string) $application->application_id;
        $workflowAppPks = array_values(array_unique(array_filter([
            $this->workflowService->workflowPk($application),
            $this->workflowService->workflowPk($master),
        ])));

        if ($this->workflowService->isChildWorkflow($application)) {
            $this->documentVersionService->ensureCarriedForwardDocuments($application);
        }

        $educationalQualifications = CC_Education::where('application_id', $masterId)
            ->orderByDesc('year_of_passing')
            ->get()
            ->map(function ($row) use ($workflowAppPks) {
                $eduId = (int) ($row->edu_id ?? 0);
                $row->id = $eduId;
                $row->document_url = competency_document_url(
                    $row->upload_document ?? null,
                    'education',
                    $eduId,
                    'certificate',
                    $workflowAppPks
                );

                return $row;
            });

        $enrichExperienceDocument = function (CC_Experience $row, bool $isNew) use ($application, $workflowAppPks) {
            $refId = (int) $row->exp_id;
            $workflowIds = $isNew
                ? [$this->workflowService->workflowPk($application)]
                : $workflowAppPks;

            if (empty($row->support_document)) {
                $log = competency_find_document_log('experience', $refId, 'experience_doc', $workflowIds);
                if ($log && ! empty($log->file_path)) {
                    $row->support_document = $log->file_path;
                }
            }

            if (empty($row->releive_document)) {
                $log = competency_find_document_log('experience', $refId, 'relieving_doc', $workflowIds);
                if ($log && ! empty($log->file_path)) {
                    $row->releive_document = $log->file_path;
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

        $parentExperience = CC_Experience::where('application_id', $masterId)
            ->orderBy('exp_id')
            ->get()
            ->map(fn (CC_Experience $row) => $enrichExperienceDocument($row, false));

        $newExperience = collect();
        if ($childId !== $masterId && $this->workflowService->isAlterationApplication($application)) {
            $newExperience = CC_Experience::where('application_id', $childId)
                ->orderBy('exp_id')
                ->get()
                ->map(function (CC_Experience $row) use ($enrichExperienceDocument) {
                    $row->setAttribute('is_alteration_new', true);

                    return $enrichExperienceDocument($row, true);
                });
        }

        $workExperience = $parentExperience->concat($newExperience)->values();

        $alterationProofs = collect();
        if ($this->workflowService->isAlterationApplication($application)) {
            $proofItems = app(FormSProofDocumentService::class)->collectAlterationProofsForReview(
                (string) $application->application_id,
                $workflowAppPks
            );

            if ($proofItems !== []) {
                $alterationProofs = collect($proofItems);
            } else {
                $alterationProofs = DocumentsLog::query()
                    ->where('application_id', $this->workflowService->workflowPk($application))
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
        }

        $uploadedPhoto = $this->resolveApplicantPhoto($application, $workflowAppPks);
        $uploadedSign = $this->resolveApplicantSign($application, $workflowAppPks);

        if ($uploadedPhoto && empty($uploadedPhoto->media_url) && ! empty($uploadedPhoto->upload_path)) {
            $uploadedPhoto->setAttribute('media_url', $this->resolveMediaUrl(
                $uploadedPhoto->upload_path,
                'photo',
                'photo',
                $workflowAppPks
            ));
        }

        if ($uploadedSign && empty($uploadedSign->media_url) && ! empty($uploadedSign->uploaded_doc)) {
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
        $storedPath = trim($storedPath);

        if ($storedPath !== '' && preg_match('#^FORM_[A-Z]+/#', $storedPath)) {
            return competency_document_path_url($storedPath);
        }

        foreach ($workflowAppPks as $wfPk) {
            $log = $this->findActiveDocLog((int) $wfPk, $moduleType, $documentType);
            if ($log) {
                return competency_document_log_download_url($log)
                    ?? competency_document_path_url($log->file_path);
            }
        }

        if ($storedPath !== '' && $this->legacyMediaFileExists($storedPath)) {
            return competency_media_url($storedPath);
        }

        return null;
    }

    protected function findActiveDocLog(int $workflowAppPk, string $moduleType, string $documentType): DocumentsLog|CC_Doc_Log|null
    {
        if ($workflowAppPk <= 0) {
            return null;
        }

        $ccLog = CC_Doc_Log::query()
            ->where('application_id', $workflowAppPk)
            ->where('module_type', $moduleType)
            ->where('document_type', $documentType)
            ->where('is_active', true)
            ->orderByDesc('doc_id')
            ->first();

        if ($ccLog) {
            return $ccLog;
        }

        return DocumentsLog::query()
            ->where('application_id', $workflowAppPk)
            ->where('module_type', $moduleType)
            ->where('document_type', $documentType)
            ->where('is_active', true)
            ->orderByDesc('id')
            ->first();
    }

    protected function legacyMediaFileExists(string $storedPath): bool
    {
        $storedPath = trim(str_replace('\\', '/', $storedPath));
        if ($storedPath === '') {
            return false;
        }

        if (str_starts_with($storedPath, 'http://') || str_starts_with($storedPath, 'https://')) {
            return true;
        }

        $relative = ltrim($storedPath, '/');

        return is_file(public_path($relative)) || is_file(storage_path('app/'.$relative));
    }

    protected function resolveApplicantPhoto(CC_CompetencyMeta $application, array $workflowAppPks = []): ?\App\Models\TnelbApplicantPhoto
    {
        $fromLog = $this->resolvePhotoFromDocLog($workflowAppPks);
        if ($fromLog) {
            return $fromLog;
        }

        foreach ($this->mediaApplicationIds($application) as $applicationId) {
            $photo = \App\Models\TnelbApplicantPhoto::where('application_id', $applicationId)->first();
            if ($photo && trim((string) ($photo->upload_path ?? '')) !== '') {
                return $photo;
            }
        }

        return null;
    }

    protected function resolveApplicantSign(CC_CompetencyMeta $application, array $workflowAppPks = []): ?\App\Models\TnelbApplicantsSign
    {
        $fromLog = $this->resolveSignFromDocLog($workflowAppPks);
        if ($fromLog) {
            return $fromLog;
        }

        foreach ($this->mediaApplicationIds($application) as $applicationId) {
            $sign = \App\Models\TnelbApplicantsSign::where('application_id', $applicationId)->first();
            if ($sign && trim((string) ($sign->uploaded_doc ?? '')) !== '') {
                return $sign;
            }
        }

        return null;
    }

    /**
     * @param  list<int>  $workflowAppPks
     */
    protected function resolvePhotoFromDocLog(array $workflowAppPks): ?\App\Models\TnelbApplicantPhoto
    {
        foreach (array_values(array_unique(array_filter(array_map('intval', $workflowAppPks)))) as $wfPk) {
            $log = $this->findActiveDocLog($wfPk, 'photo', 'photo');
            if (! $log || empty($log->file_path)) {
                continue;
            }

            $photo = new \App\Models\TnelbApplicantPhoto([
                'upload_path' => $log->file_path,
            ]);
            $photo->setAttribute('media_url', competency_document_log_download_url($log)
                ?? competency_document_path_url($log->file_path));

            return $photo;
        }

        return null;
    }

    /**
     * @param  list<int>  $workflowAppPks
     */
    protected function resolveSignFromDocLog(array $workflowAppPks): ?\App\Models\TnelbApplicantsSign
    {
        foreach (array_values(array_unique(array_filter(array_map('intval', $workflowAppPks)))) as $wfPk) {
            $log = $this->findActiveDocLog($wfPk, 'signature', 'signature');
            if (! $log || empty($log->file_path)) {
                continue;
            }

            $sign = new \App\Models\TnelbApplicantsSign([
                'uploaded_doc' => $log->file_path,
            ]);
            $sign->setAttribute('media_url', competency_document_log_download_url($log)
                ?? competency_document_path_url($log->file_path));

            return $sign;
        }

        return null;
    }

    /**
     * @return list<string>
     */
    protected function mediaApplicationIds(CC_CompetencyMeta $application): array
    {
        $ids = [];
        $seen = [];
        $current = $application;

        while ($current) {
            $appId = trim((string) ($current->application_id ?? ''));
            if ($appId !== '' && ! isset($seen[$appId])) {
                $ids[] = $appId;
                $seen[$appId] = true;
            }

            $oldId = trim((string) ($current->old_application ?? ''));
            if ($oldId === '' || isset($seen[$oldId])) {
                break;
            }

            $current = CC_Forms_Meta::findByApplicationId($oldId);
        }

        return $ids;
    }
}
