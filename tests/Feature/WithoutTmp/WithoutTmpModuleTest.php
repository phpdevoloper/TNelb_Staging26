<?php

namespace Tests\Feature\WithoutTmp;

use App\Enums\ScertAlterationStatus;
use App\Enums\ScertAppStatus;
use App\Models\CAlterationRequest;
use App\Models\CEducation;
use App\Models\ScertApp;
use App\Services\WithoutTmp\ScertAlterationService;
use App\Services\WithoutTmp\ScertApplicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WithoutTmpModuleTest extends TestCase
{
    use RefreshDatabase;

    protected ScertApplicationService $applicationService;
    protected ScertAlterationService $alterationService;
    protected ScertApp $application;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('without_tmp');
        $this->applicationService = app(ScertApplicationService::class);
        $this->alterationService = app(ScertAlterationService::class);
        $this->application = $this->applicationService->createApplication('Test Applicant');
    }

    public function test_initial_upload_stores_file_directly_in_without_tmp_folder(): void
    {
        $file = UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf');

        $this->applicationService->saveApplication($this->application, [
            'applicant_name' => 'Test Applicant',
            'education_level' => ['Diploma'],
            'institution_name' => ['Test College'],
            'year_of_passing' => ['2020'],
            'grade' => ['A'],
            'education_file' => [$file],
        ], submit: false);

        $education = CEducation::where('application_id', $this->application->id)->first();

        $this->assertNotNull($education);
        $this->assertStringStartsWith('Education/', $education->file_path);
        $this->assertStringContainsString('_EDU_', $education->file_name);
        $this->assertStringContainsString($this->application->fresh()->application_code, $education->file_name);
        Storage::disk('without_tmp')->assertExists($education->file_path);
    }

    public function test_submit_changes_draft_status_to_submitted(): void
    {
        $this->applicationService->saveApplication($this->application, [
            'applicant_name' => 'Test Applicant',
        ], submit: true);

        $this->application->refresh();
        $this->assertEquals(ScertAppStatus::SUBMITTED, $this->application->status);
        $this->assertNotNull($this->application->submitted_at);
    }

    public function test_alteration_stores_new_file_separately_and_keeps_old_active(): void
    {
        $file = UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf');
        $this->applicationService->saveApplication($this->application, [
            'applicant_name' => 'Test Applicant',
            'education_level' => ['Diploma'],
            'institution_name' => ['Test College'],
            'education_file' => [$file],
        ], submit: true);

        $education = CEducation::where('application_id', $this->application->id)->first();
        $oldPath = $education->file_path;

        $newFile = UploadedFile::fake()->create('certificate_new.pdf', 120, 'application/pdf');
        $request = $this->alterationService->requestAlteration(
            $this->application->fresh(),
            'c_education',
            $education->id,
            $newFile,
            'Corrected scan'
        );

        $education->refresh();
        $this->assertEquals($oldPath, $education->file_path);
        $this->assertEquals(ScertAppStatus::ALTERATION, $this->application->fresh()->status);
        Storage::disk('without_tmp')->assertExists($oldPath);
        Storage::disk('without_tmp')->assertExists($request->new_file_path);
        $this->assertNotEquals($oldPath, $request->new_file_path);
    }

    public function test_approve_alteration_replaces_active_file_reference_and_deletes_old_file(): void
    {
        $file = UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf');
        $this->applicationService->saveApplication($this->application, [
            'applicant_name' => 'Test Applicant',
            'education_level' => ['Diploma'],
            'institution_name' => ['Test College'],
            'education_file' => [$file],
        ], submit: true);

        $education = CEducation::where('application_id', $this->application->id)->first();
        $oldPath = $education->file_path;

        $newFile = UploadedFile::fake()->create('certificate_new.pdf', 120, 'application/pdf');
        $request = $this->alterationService->requestAlteration(
            $this->application->fresh(),
            'c_education',
            $education->id,
            $newFile,
            'Corrected scan'
        );

        $this->alterationService->approve($request);

        $education->refresh();
        $request->refresh();

        $this->assertEquals($request->new_file_path, $education->file_path);
        $this->assertEquals(ScertAlterationStatus::APPROVED, $request->status);
        Storage::disk('without_tmp')->assertMissing($oldPath);
        Storage::disk('without_tmp')->assertExists($education->file_path);
    }

    public function test_reject_alteration_deletes_pending_file_and_keeps_old_file(): void
    {
        $file = UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf');
        $this->applicationService->saveApplication($this->application, [
            'applicant_name' => 'Test Applicant',
            'education_level' => ['Diploma'],
            'institution_name' => ['Test College'],
            'education_file' => [$file],
        ], submit: true);

        $education = CEducation::where('application_id', $this->application->id)->first();
        $oldPath = $education->file_path;

        $newFile = UploadedFile::fake()->create('certificate_new.pdf', 120, 'application/pdf');
        $request = $this->alterationService->requestAlteration(
            $this->application->fresh(),
            'c_education',
            $education->id,
            $newFile,
            'Bad upload'
        );

        $this->alterationService->reject($request, 'Invalid document');

        $education->refresh();
        $this->assertEquals($oldPath, $education->file_path);
        Storage::disk('without_tmp')->assertExists($oldPath);
        Storage::disk('without_tmp')->assertMissing($request->new_file_path);
    }

    public function test_submitted_application_inline_file_replace_creates_alteration_request(): void
    {
        $file = UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf');
        $this->applicationService->saveApplication($this->application, [
            'applicant_name' => 'Test Applicant',
            'education_level' => ['Diploma'],
            'institution_name' => ['Test College'],
            'education_file' => [$file],
        ], submit: true);

        $education = CEducation::where('application_id', $this->application->id)->first();
        $oldPath = $education->file_path;

        $newFile = UploadedFile::fake()->create('certificate_new.pdf', 120, 'application/pdf');
        $this->applicationService->saveApplication($this->application->fresh(), [
            'applicant_name' => 'Test Applicant',
            'education_id' => [$education->id],
            'education_level' => ['Diploma'],
            'institution_name' => ['Test College'],
            'education_file' => [$newFile],
            'education_alteration_reason' => ['Corrected scan quality'],
        ], submit: false);

        $education->refresh();
        $this->assertEquals($oldPath, $education->file_path);
        $this->assertDatabaseHas('c_alteration_requests', [
            'application_id' => $this->application->id,
            'target_table' => 'c_education',
            'target_row_id' => $education->id,
            'status' => ScertAlterationStatus::PENDING->value,
        ]);
    }
}
