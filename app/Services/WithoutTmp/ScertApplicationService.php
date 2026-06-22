<?php

namespace App\Services\WithoutTmp;

use App\Enums\ScertAppStatus;
use App\Models\CExperience;
use App\Models\CPhoto;
use App\Models\CScertDocument;
use App\Models\CSignature;
use App\Models\CEducation;
use App\Models\ScertApp;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ScertApplicationService
{
    public function __construct(
        protected WithoutTmpStorageService $storage,
        protected ScertAlterationService $alterationService
    ) {}

    public function createApplication(string $applicantName, ScertAppStatus $status = ScertAppStatus::DRAFT): ScertApp
    {
        $app = ScertApp::create([
            'application_code' => 'TEMP',
            'applicant_name' => $applicantName,
            'status' => $status,
            'submitted_at' => in_array($status, [ScertAppStatus::SUBMITTED, ScertAppStatus::DIGITIZATION], true)
                ? now()
                : null,
        ]);

        $app->update([
            'application_code' => 'APP' . str_pad((string) $app->id, 3, '0', STR_PAD_LEFT),
        ]);

        return $app->fresh();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function saveApplication(ScertApp $application, array $payload, bool $submit): ScertApp
    {
        return DB::transaction(function () use ($application, $payload, $submit) {
            $application->update([
                'applicant_name' => $payload['applicant_name'] ?? $application->applicant_name,
            ]);

            $this->saveEducationRows($application, $payload);
            $this->saveExperienceRows($application, $payload);
            $this->savePhoto($application, $payload);
            $this->saveSignature($application, $payload);
            $this->saveDocuments($application, $payload);

            if ($submit) {
                if ($application->status === ScertAppStatus::DRAFT) {
                    $application->status = ScertAppStatus::SUBMITTED;
                }

                $application->submitted_at = $application->submitted_at ?? now();
            }

            $application->save();

            return $application->fresh([
                'educations',
                'experiences',
                'photo',
                'signature',
                'documents',
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function saveEducationRows(ScertApp $application, array $payload): void
    {
        $levels = $payload['education_level'] ?? [];

        foreach ($levels as $index => $level) {
            $level = trim((string) $level);
            $institution = trim((string) ($payload['institution_name'][$index] ?? ''));
            $existingId = $payload['education_id'][$index] ?? null;
            /** @var UploadedFile|null $file */
            $file = $payload['education_file'][$index] ?? null;

            if ($level === '' && $institution === '' && !$file && !$existingId) {
                continue;
            }

            if ($existingId) {
                $row = CEducation::where('application_id', $application->id)->findOrFail($existingId);
                $row->update([
                    'education_level' => $level ?: $row->education_level,
                    'institution_name' => $institution ?: $row->institution_name,
                    'year_of_passing' => $payload['year_of_passing'][$index] ?? $row->year_of_passing,
                    'grade' => $payload['grade'][$index] ?? $row->grade,
                ]);
            } else {
                if ($level === '' || $institution === '') {
                    throw new RuntimeException('Education row ' . ($index + 1) . ': level and institution are required.');
                }

                $row = CEducation::create([
                    'application_id' => $application->id,
                    'education_level' => $level,
                    'institution_name' => $institution,
                    'year_of_passing' => $payload['year_of_passing'][$index] ?? null,
                    'grade' => $payload['grade'][$index] ?? null,
                ]);
            }

            if ($file) {
                $this->handleFileUpload(
                    $application,
                    $row,
                    $file,
                    'EDU',
                    'c_education',
                    trim((string) ($payload['education_alteration_reason'][$index] ?? '')),
                    ['file_name' => 'file_name', 'file_path' => 'file_path'],
                    'Education row ' . ($index + 1)
                );
            }
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function saveExperienceRows(ScertApp $application, array $payload): void
    {
        $companies = $payload['company_name'] ?? [];

        foreach ($companies as $index => $company) {
            $company = trim((string) $company);
            $existingId = $payload['experience_id'][$index] ?? null;
            /** @var UploadedFile|null $file */
            $file = $payload['experience_file'][$index] ?? null;

            if ($company === '' && !$file && !$existingId) {
                continue;
            }

            if ($existingId) {
                $row = CExperience::where('application_id', $application->id)->findOrFail($existingId);
                $row->update([
                    'company_name' => $company ?: $row->company_name,
                    'years_of_experience' => $payload['years_of_experience'][$index] ?? $row->years_of_experience,
                    'designation' => $payload['designation'][$index] ?? $row->designation,
                ]);
            } else {
                if ($company === '') {
                    throw new RuntimeException('Experience row ' . ($index + 1) . ': company name is required.');
                }

                $row = CExperience::create([
                    'application_id' => $application->id,
                    'company_name' => $company,
                    'years_of_experience' => $payload['years_of_experience'][$index] ?? null,
                    'designation' => $payload['designation'][$index] ?? null,
                ]);
            }

            if ($file) {
                $this->handleFileUpload(
                    $application,
                    $row,
                    $file,
                    'EXP',
                    'c_experience',
                    trim((string) ($payload['experience_alteration_reason'][$index] ?? '')),
                    ['file_name' => 'file_name', 'file_path' => 'file_path'],
                    'Experience row ' . ($index + 1)
                );
            }
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function savePhoto(ScertApp $application, array $payload): void
    {
        /** @var UploadedFile|null $file */
        $file = $payload['photo_file'] ?? null;

        if (!$file) {
            return;
        }

        $row = CPhoto::firstOrCreate(['application_id' => $application->id]);
        $this->handleFileUpload(
            $application,
            $row,
            $file,
            'PHOTO',
            'c_photo',
            trim((string) ($payload['photo_alteration_reason'] ?? '')),
            ['file_name' => 'file_name', 'file_path' => 'file_path'],
            'Photo'
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function saveSignature(ScertApp $application, array $payload): void
    {
        /** @var UploadedFile|null $file */
        $file = $payload['signature_file'] ?? null;

        if (!$file) {
            return;
        }

        $row = CSignature::firstOrCreate(['application_id' => $application->id]);
        $this->handleFileUpload(
            $application,
            $row,
            $file,
            'SIGN',
            'c_signature',
            trim((string) ($payload['signature_alteration_reason'] ?? '')),
            ['file_name' => 'file_name', 'file_path' => 'file_path'],
            'Signature'
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function saveDocuments(ScertApp $application, array $payload): void
    {
        $labels = $payload['document_label'] ?? [];

        foreach ($labels as $index => $label) {
            $label = trim((string) $label);
            $existingId = $payload['document_id'][$index] ?? null;
            /** @var UploadedFile|null $file */
            $file = $payload['document_file'][$index] ?? null;

            if ($label === '' && !$file && !$existingId) {
                continue;
            }

            if ($existingId) {
                $row = CScertDocument::where('application_id', $application->id)->findOrFail($existingId);
                if ($label !== '') {
                    $row->update(['document_label' => $label]);
                }
            } else {
                if ($label === '') {
                    throw new RuntimeException('Document row ' . ($index + 1) . ': label is required.');
                }

                $row = CScertDocument::create([
                    'application_id' => $application->id,
                    'document_label' => $label,
                ]);
            }

            if ($file) {
                $this->handleFileUpload(
                    $application,
                    $row,
                    $file,
                    'DOC',
                    'c_documents',
                    trim((string) ($payload['document_alteration_reason'][$index] ?? '')),
                    ['file_name' => 'file_name', 'file_path' => 'file_path'],
                    'Document row ' . ($index + 1)
                );
            }
        }
    }

    /**
     * @param  array<string, string>  $columns
     */
    protected function handleFileUpload(
        ScertApp $application,
        CEducation|CExperience|CPhoto|CSignature|CScertDocument $row,
        UploadedFile $file,
        string $uploadType,
        string $targetTable,
        string $alterationReason,
        array $columns,
        string $label
    ): void {
        $hasExistingFile = !empty($row->{$columns['file_path']});
        $locked = in_array($application->status, [
            ScertAppStatus::SUBMITTED,
            ScertAppStatus::DIGITIZATION,
            ScertAppStatus::ALTERATION,
        ], true);

        if ($locked && $hasExistingFile) {
            if ($alterationReason === '') {
                throw new RuntimeException(
                    "{$label}: alteration reason is required when replacing an existing uploaded file."
                );
            }

            $this->alterationService->requestAlteration(
                $application,
                $targetTable,
                $row->id,
                $file,
                $alterationReason
            );

            return;
        }

        $stored = $this->storage->storeUpload($file, $application, $uploadType);

        if ($hasExistingFile) {
            $this->storage->deleteFile($row->{$columns['file_path']});
        }

        $row->update([
            $columns['file_name'] => $stored['file_name'],
            $columns['file_path'] => $stored['file_path'],
            'is_active' => true,
        ]);
    }
}
