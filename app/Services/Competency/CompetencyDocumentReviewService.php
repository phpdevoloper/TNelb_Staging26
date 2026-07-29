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

            $resolveDocPath = function (string $documentType, ?string $storedPath) use ($refId, $workflowIds): ?string {
                $storedPath = trim((string) ($storedPath ?? ''));
                if ($storedPath !== '') {
                    return $storedPath;
                }

                foreach ($workflowIds as $workflowPk) {
                    if ($workflowPk <= 0) {
                        continue;
                    }

                    $ccLog = \App\Models\CC_Doc_Log::forGroup(
                        $workflowPk,
                        'experience',
                        $refId,
                        $documentType
                    )->orderByDesc('doc_id')->first();

                    if ($ccLog && trim((string) ($ccLog->file_path ?? '')) !== '') {
                        return trim((string) $ccLog->file_path);
                    }
                }

                $legacyLog = competency_find_document_log('experience', $refId, $documentType, $workflowIds);

                return $legacyLog && trim((string) ($legacyLog->file_path ?? '')) !== ''
                    ? trim((string) $legacyLog->file_path)
                    : null;
            };

            $supportPath = $resolveDocPath('experience_doc', $row->support_document ?? $row->upload_document ?? null);
            if ($supportPath) {
                $row->support_document = $supportPath;
            }

            $relievePath = $resolveDocPath(
                'relieving_doc',
                $row->relieve_document ?? $row->releive_document ?? null
            );
            if ($relievePath) {
                $row->relieve_document = $relievePath;
            }

            $row->setAttribute('support_document_url', competency_document_url(
                $supportPath,
                'experience',
                $refId,
                'experience_doc',
                $workflowIds
            ));

            $row->setAttribute('releive_document_url', competency_document_url(
                $relievePath,
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
                $workflowPk = $this->workflowService->workflowPk($application);
                $ccLogs = CC_Doc_Log::query()
                    ->where('application_id', $workflowPk)
                    ->where('module_type', 'alteration')
                    ->whereIn('document_type', ['name_proof', 'address_proof'])
                    ->orderByDesc('doc_id')
                    ->get()
                    ->unique('document_type')
                    ->values();

                if ($ccLogs->isNotEmpty()) {
                    $alterationProofs = $ccLogs->map(function (CC_Doc_Log $log) use ($workflowAppPks) {
                        $type = (string) ($log->document_type ?? '');
                        $label = match ($type) {
                            'name_proof' => 'Name alteration supporting proof',
                            'address_proof' => 'Address alteration supporting proof',
                            default => ucwords(str_replace('_', ' ', $type)),
                        };
                        $storedPath = trim((string) ($log->file_path ?? ''));

                        return (object) [
                            'document_type' => $type,
                            'label' => $label,
                            'file_name' => (string) ($log->file_name ?: basename($storedPath)),
                            'url' => $storedPath !== ''
                                ? (competency_document_path_url($storedPath)
                                    ?? competency_document_url(
                                        $storedPath,
                                        'alteration',
                                        (int) ($log->module_ref_id ?? 0),
                                        $type,
                                        $workflowAppPks
                                    ))
                                : null,
                            'proof_doc' => $storedPath !== '' ? $storedPath : null,
                        ];
                    });
                } else {
                    $alterationProofs = DocumentsLog::query()
                        ->where('application_id', $workflowPk)
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

        $fromProof = $this->resolvePhotoFromProofDoc($application);
        if ($fromProof) {
            return $fromProof;
        }

        foreach ($this->mediaApplicationIds($application) as $applicationId) {
            $photo = \App\Models\TnelbApplicantPhoto::where('application_id', $applicationId)->first();
            if ($photo && $this->legacyMediaIsReachable((string) ($photo->upload_path ?? ''))) {
                $photo->setAttribute('media_url', competency_media_url($photo->upload_path));

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

        $fromProof = $this->resolveSignFromProofDoc($application);
        if ($fromProof) {
            return $fromProof;
        }

        foreach ($this->mediaApplicationIds($application) as $applicationId) {
            $sign = \App\Models\TnelbApplicantsSign::where('application_id', $applicationId)->first();
            if ($sign && $this->legacyMediaIsReachable((string) ($sign->uploaded_doc ?? ''))) {
                $sign->setAttribute('media_url', competency_media_url($sign->uploaded_doc));

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
            $photo->setAttribute('media_url', $this->viewableMediaUrl($log->file_path, $log));

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
            $sign->setAttribute('media_url', $this->viewableMediaUrl($log->file_path, $log));

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

    protected function resolvePhotoFromProofDoc(CC_CompetencyMeta $application): ?\App\Models\TnelbApplicantPhoto
    {
        $proofService = app(FormSProofDocumentService::class);

        foreach ($this->mediaApplicationIds($application) as $applicationId) {
            $path = $proofService->resolveProofPath($applicationId, FormSProofDocumentService::PROOF_PHOTO);
            if (! $path) {
                continue;
            }

            $photo = new \App\Models\TnelbApplicantPhoto([
                'application_id' => $applicationId,
                'upload_path' => $path,
            ]);
            $photo->setAttribute('media_url', competency_media_url($path));

            return $photo;
        }

        return null;
    }

    protected function resolveSignFromProofDoc(CC_CompetencyMeta $application): ?\App\Models\TnelbApplicantsSign
    {
        $proofService = app(FormSProofDocumentService::class);

        foreach ($this->mediaApplicationIds($application) as $applicationId) {
            $path = $proofService->resolveProofPath($applicationId, FormSProofDocumentService::PROOF_SIGN);
            if (! $path) {
                continue;
            }

            $sign = new \App\Models\TnelbApplicantsSign([
                'application_id' => $applicationId,
                'uploaded_doc' => $path,
            ]);
            $sign->setAttribute('media_url', competency_media_url($path));

            return $sign;
        }

        return null;
    }

    protected function viewableMediaUrl(?string $storedPath, DocumentsLog|CC_Doc_Log|null $log = null): ?string
    {
        $storedPath = trim(str_replace('\\', '/', (string) $storedPath));

        if ($storedPath !== '' && preg_match('#^FORM_[A-Z]+/#', $storedPath)) {
            return competency_document_path_url($storedPath);
        }

        if ($log) {
            return competency_document_log_download_url($log);
        }

        return competency_media_url($storedPath);
    }

    protected function legacyMediaIsReachable(string $storedPath): bool
    {
        $storedPath = trim(str_replace('\\', '/', $storedPath));
        if ($storedPath === '') {
            return false;
        }

        if (preg_match('#^FORM_[A-Z]+/#', $storedPath)) {
            return app(\App\Services\DocumentVersion\DocumentStorageService::class)->exists($storedPath);
        }

        return $this->legacyMediaFileExists($storedPath);
    }
}
