<?php

namespace Tests\Feature\DocumentVersion;

use App\Enums\DocumentApplicationType;
use App\Enums\DocumentRequestType;
use App\Enums\DocumentStorageType;
use App\Enums\DocumentVersionStatus;
use App\Models\DApplication;
use App\Models\DDocument;
use App\Models\DEducation;
use App\Services\DocumentVersion\DocumentApplicationService;
use App\Services\DocumentVersion\DocumentApprovalService;
use App\Services\DocumentVersion\DocumentVersionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentVersionModuleTest extends TestCase
{
    use RefreshDatabase;

    protected DocumentVersionService $versionService;
    protected DocumentApprovalService $approvalService;
    protected DApplication $application;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private_documents');
        $this->versionService = app(DocumentVersionService::class);
        $this->approvalService = app(DocumentApprovalService::class);

        $this->application = DApplication::create([
            'application_no' => 'APP-TEST-001',
            'applicant_name' => 'Test Applicant',
            'status' => 'DRAFT',
        ]);
    }

    public function test_initial_upload_stores_file_in_new_application_folder(): void
    {
        $file = UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf');

        $version = $this->versionService->createInitialUpload(
            $file,
            $this->application->id,
            'application',
            'identity',
            workflowStage: 'NEW'
        );

        $this->assertEquals(1, $version->version_no);
        $this->assertEquals(DocumentVersionStatus::APPROVED, $version->status);
        $this->assertEquals(DocumentStorageType::PERMANENT, $version->storage_type);
        $this->assertEquals(DocumentRequestType::INITIAL, $version->request_type);
        $this->assertEquals(DocumentApplicationType::NEW, $version->application_type);
        $this->assertTrue($version->is_active);
        $this->assertNotNull($version->approved_at);
        $this->assertStringStartsWith('FORM_S/NEW/META/', $version->file_path);
        $this->assertStringContainsString('_META_001.', $version->file_name);
        Storage::disk('private_documents')->assertExists($version->file_path);
    }

    public function test_new_application_upload_updates_master_table_without_approval(): void
    {
        $education = DEducation::create([
            'application_id' => $this->application->id,
            'education_level' => 'DEE',
            'institution_name' => 'Test College',
        ]);

        $file = UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf');
        $version = $this->versionService->createInitialUpload(
            $file,
            $this->application->id,
            'education',
            'certificate',
            $education->id,
            workflowStage: 'NEW'
        );

        $education->refresh();

        $this->assertEquals(DocumentVersionStatus::APPROVED, $version->status);
        $this->assertTrue($version->is_active);
        $this->assertEquals($version->file_path, $education->file_path);
    }

    public function test_renewal_does_not_duplicate_master_rows(): void
    {
        $education = DEducation::create([
            'application_id' => $this->application->id,
            'education_level' => 'DEE',
            'institution_name' => 'Test College',
        ]);

        $file = UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf');
        $this->versionService->createInitialUpload(
            $file,
            $this->application->id,
            'education',
            'certificate',
            $education->id,
            workflowStage: 'NEW'
        );

        $renewal = app(DocumentApplicationService::class)->findOrCreateRenewalApplication($this->application);

        $this->assertEquals(1, DEducation::where('application_id', $this->application->id)->count());
        $this->assertEquals(0, DEducation::where('application_id', $renewal->id)->count());
        $this->assertTrue(
            DDocument::forGroup($renewal->id, 'education', $education->id, 'certificate')->exists()
        );
    }

    public function test_renewal_approval_updates_parent_master_file_path(): void
    {
        $parentEducation = DEducation::create([
            'application_id' => $this->application->id,
            'education_level' => 'DEE',
            'institution_name' => 'Test College',
        ]);

        $file = UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf');
        $v1 = $this->versionService->createInitialUpload(
            $file,
            $this->application->id,
            'education',
            'certificate',
            $parentEducation->id,
            workflowStage: 'NEW'
        );
        $parentEducation->refresh();
        $this->assertEquals($v1->file_path, $parentEducation->file_path);

        $renewal = app(DocumentApplicationService::class)->findOrCreateRenewalApplication($this->application);
        $replacement = UploadedFile::fake()->create('certificate_renewed.pdf', 100, 'application/pdf');
        $v2 = $this->versionService->uploadNewVersion(
            $replacement,
            $renewal->id,
            'education',
            'certificate',
            $parentEducation->id,
            'Renewal request: updated certificate',
            'RENEWAL'
        );

        $this->assertEquals(DocumentVersionStatus::PENDING, $v2->status);
        $this->assertFalse($v2->is_active);

        $approved = $this->approvalService->approve($v2->id, 1, 99);

        $parentEducation->refresh();

        $this->assertEquals(DocumentVersionStatus::APPROVED, $approved->status);
        $this->assertEquals($approved->file_path, $parentEducation->file_path);
        $this->assertNotEquals($v1->file_path, $parentEducation->file_path);
    }

    public function test_approval_keeps_physical_file_and_updates_master_table(): void
    {
        $parentEducation = DEducation::create([
            'application_id' => $this->application->id,
            'education_level' => 'DEE',
            'institution_name' => 'Test College',
        ]);

        $renewal = DApplication::create([
            'application_no' => 'REN-TEST-001',
            'applicant_name' => 'Test Applicant',
            'status' => 'DRAFT',
            'parent_application_id' => $this->application->id,
            'request_context' => 'RENEWAL',
        ]);

        $file = UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf');
        $version = $this->versionService->createInitialUpload(
            $file,
            $renewal->id,
            'education',
            'certificate',
            $parentEducation->id,
            workflowStage: 'RENEWAL'
        );
        $storedPath = $version->file_path;

        $this->assertEquals(DocumentVersionStatus::PENDING, $version->status);

        $approved = $this->approvalService->approve($version->id, 1, 99);

        $this->assertEquals(DocumentVersionStatus::APPROVED, $approved->status);
        $this->assertTrue($approved->is_active);
        $this->assertEquals($storedPath, $approved->file_path);
        $this->assertEquals(99, $approved->approved_by);
        $this->assertNotNull($approved->approved_at);
        Storage::disk('private_documents')->assertExists($storedPath);

        $parentEducation->refresh();
        $this->assertEquals($storedPath, $parentEducation->file_path);
    }

    public function test_new_version_does_not_deactivate_active_until_final_approval(): void
    {
        $v1File = UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf');
        $v1 = $this->versionService->createInitialUpload($v1File, $this->application->id, 'application', 'identity');

        $v2File = UploadedFile::fake()->create('certificate_v2.pdf', 100, 'application/pdf');
        $v2 = $this->versionService->requestAlteration(
            $v2File,
            $this->application->id,
            'application',
            'identity',
            null,
            'Corrected expiry date on certificate'
        );

        $v1->refresh();
        $this->assertTrue($v1->is_active);
        $this->assertEquals(2, $v2->version_no);
        $this->assertEquals(DocumentRequestType::ALTERATION, $v2->request_type);
        $this->assertEquals(DocumentApplicationType::ALTERATION, $v2->application_type);
        $this->assertFalse($v2->is_active);
        $this->assertStringContainsString('/ALTERATION/', $v2->file_path);
        $this->assertNotEquals($v1->file_path, $v2->file_path);
    }

    public function test_alteration_requires_approved_document(): void
    {
        $file = UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No approved document exists');

        $this->versionService->requestAlteration(
            $file,
            $this->application->id,
            'application',
            'identity',
            null,
            'Should fail without approved doc'
        );
    }

    public function test_alteration_list_excludes_documents_with_pending_version(): void
    {
        $v1File = UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf');
        $v1 = $this->versionService->createInitialUpload($v1File, $this->application->id, 'application', 'identity');

        $v2File = UploadedFile::fake()->create('certificate_v2.pdf', 100, 'application/pdf');
        $this->versionService->requestAlteration(
            $v2File,
            $this->application->id,
            'application',
            'identity',
            null,
            'Updated document with clearer scan'
        );

        $alterable = $this->versionService->listAlterableDocumentsForApplication($this->application->id);
        $this->assertCount(0, $alterable);
    }

    public function test_reject_keeps_physical_file_for_audit(): void
    {
        $v1File = UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf');
        $v1 = $this->versionService->createInitialUpload($v1File, $this->application->id, 'application', 'identity');
        $approvedPath = $v1->fresh()->file_path;

        $v2File = UploadedFile::fake()->create('certificate_v2.pdf', 100, 'application/pdf');
        $v2 = $this->versionService->requestAlteration(
            $v2File,
            $this->application->id,
            'application',
            'identity',
            null,
            'Incorrect scan quality on replacement'
        );
        $rejectedPath = $v2->file_path;
        $rejected = $this->approvalService->reject($v2->fresh()->id, 1, 1, 'Invalid');

        $v1->refresh();
        $this->assertTrue($v1->is_active);
        $this->assertEquals(DocumentVersionStatus::REJECTED, $rejected->status);
        Storage::disk('private_documents')->assertExists($rejectedPath);
        Storage::disk('private_documents')->assertExists($approvedPath);
    }

    public function test_new_approved_version_deactivates_previous_active_without_overwriting_old_file(): void
    {
        $education = DEducation::create([
            'application_id' => $this->application->id,
            'education_level' => 'DEE',
            'institution_name' => 'Test College',
        ]);

        $v1File = UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf');
        $v1 = $this->versionService->createInitialUpload(
            $v1File,
            $this->application->id,
            'education',
            'certificate',
            $education->id
        );
        $firstPath = $v1->fresh()->file_path;

        $v2File = UploadedFile::fake()->create('certificate_v2.pdf', 100, 'application/pdf');
        $v2 = $this->versionService->requestAlteration(
            $v2File,
            $this->application->id,
            'education',
            'certificate',
            $education->id,
            'Updated certificate'
        );
        $this->approvalService->approve($v2->fresh()->id, 1, 1);

        $v1->refresh();
        $v2->refresh();
        $education->refresh();

        $this->assertFalse($v1->is_active);
        $this->assertTrue($v2->is_active);
        $this->assertNotEquals($firstPath, $v2->file_path);
        $this->assertEquals($v2->file_path, $education->file_path);
        Storage::disk('private_documents')->assertExists($firstPath);
        Storage::disk('private_documents')->assertExists($v2->file_path);
    }

    public function test_temp_upload_overwrites_existing_orphan_file(): void
    {
        $storage = app(\App\Services\DocumentVersion\DocumentStorageService::class);
        $sequenceNo = $storage->nextSequenceNo($this->application->id, 'identity');
        $orphanPath = $storage->buildRelativePath(
            DocumentRequestType::INITIAL,
            'APP-TEST-001',
            'application',
            'identity',
            $sequenceNo,
            'NEW',
            'pdf'
        );
        Storage::disk('private_documents')->put($orphanPath, 'orphan');

        $file = UploadedFile::fake()->create('identity.pdf', 100, 'application/pdf');
        $stored = $storage->store(
            $file,
            'APP-TEST-001',
            $this->application->id,
            'application',
            'identity',
            DocumentRequestType::INITIAL,
            'NEW'
        );

        $this->assertEquals($orphanPath, $stored['file_path']);
        Storage::disk('private_documents')->assertExists($orphanPath);
        $this->assertNotEquals('orphan', Storage::disk('private_documents')->get($orphanPath));
    }

    public function test_multiple_education_rows_get_unique_file_paths(): void
    {
        $edu1 = DEducation::create([
            'application_id' => $this->application->id,
            'education_level' => 'BEE',
            'institution_name' => 'College A',
        ]);
        $edu2 = DEducation::create([
            'application_id' => $this->application->id,
            'education_level' => 'AMIE',
            'institution_name' => 'College B',
        ]);

        $file1 = UploadedFile::fake()->create('edu1.pdf', 100, 'application/pdf');
        $file2 = UploadedFile::fake()->create('edu2.pdf', 100, 'application/pdf');

        $v1 = $this->versionService->createInitialUpload(
            $file1,
            $this->application->id,
            'education',
            'certificate',
            $edu1->id
        );
        $v2 = $this->versionService->createInitialUpload(
            $file2,
            $this->application->id,
            'education',
            'certificate',
            $edu2->id
        );

        $this->assertNotEquals($v1->file_path, $v2->file_path);
        $this->assertStringContainsString('_EDU_001.', $v1->file_name);
        $this->assertStringContainsString('_EDU_002.', $v2->file_name);
        Storage::disk('private_documents')->assertExists($v1->file_path);
        Storage::disk('private_documents')->assertExists($v2->file_path);
    }

    public function test_education_upload_uses_education_module_folder(): void
    {
        $file = UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf');

        $version = $this->versionService->createInitialUpload(
            $file,
            $this->application->id,
            'education',
            'certificate',
            moduleRefId: 10,
            workflowStage: 'NEW'
        );

        $this->assertStringStartsWith('FORM_S/NEW/EDUCATION/', $version->file_path);
        $this->assertStringContainsString('_EDU_001.', $version->file_name);
    }

    public function test_multiple_document_types_have_independent_version_chains(): void
    {
        $cert = UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf');
        $identity = UploadedFile::fake()->create('identity.pdf', 100, 'application/pdf');

        $this->versionService->createInitialUpload($cert, $this->application->id, 'application', 'identity');
        $this->versionService->createInitialUpload($identity, $this->application->id, 'application', 'supporting');

        $this->assertEquals(2, DDocument::where('application_id', $this->application->id)->distinct('document_type')->count('document_type'));
    }

    public function test_reset_clears_all_tables_and_storage(): void
    {
        $file = UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf');
        $version = $this->versionService->createInitialUpload($file, $this->application->id, 'application', 'identity');

        DEducation::create([
            'application_id' => $this->application->id,
            'education_level' => 'DEE',
            'institution_name' => 'Test',
        ]);

        $summary = app(\App\Services\DocumentVersion\DocumentModuleResetService::class)->resetAll();

        $this->assertEquals(1, $summary['applications']);
        $this->assertEquals(1, $summary['documents']);
        $this->assertEquals(0, DApplication::count());
        $this->assertEquals(0, DDocument::count());
        $this->assertEquals(0, DEducation::count());
        Storage::disk('private_documents')->assertMissing($version->file_path);

        $freshApp = DApplication::create([
            'application_no' => 'APP-FRESH-001',
            'applicant_name' => 'Fresh Start',
            'status' => 'DRAFT',
        ]);

        $this->assertEquals(1, $freshApp->id);
    }

    public function test_renewal_replacement_upload_uses_renewal_folder(): void
    {
        $parent = $this->application;
        $education = DEducation::create([
            'application_id' => $parent->id,
            'education_level' => 'DEE',
            'institution_name' => 'Test College',
        ]);

        $file = UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf');
        $v1 = $this->versionService->createInitialUpload(
            $file,
            $parent->id,
            'education',
            'certificate',
            $education->id,
            workflowStage: 'NEW'
        );
        $education->refresh();

        $renewal = app(DocumentApplicationService::class)->findOrCreateRenewalApplication($parent);
        $parentEducation = DEducation::where('application_id', $parent->id)->firstOrFail();

        $replacement = UploadedFile::fake()->create('certificate_renewed.pdf', 100, 'application/pdf');
        $v2 = $this->versionService->uploadNewVersion(
            $replacement,
            $renewal->id,
            'education',
            'certificate',
            $parentEducation->id,
            'Renewal request: updated certificate',
            'RENEWAL'
        );

        $this->assertStringContainsString('/RENEWAL/', $v2->file_path);
        $this->assertStringNotContainsString('/ALTERATION/', $v2->file_path);
        $this->assertEquals(DocumentVersionStatus::PENDING, $v2->status);
        $this->assertEquals(DocumentRequestType::RENEWAL, $v2->request_type);
        $this->assertEquals(DocumentApplicationType::RENEWAL, $v2->application_type);
        Storage::disk('private_documents')->assertExists($v2->file_path);
    }

    public function test_alteration_application_references_parent_paths_without_copying_files(): void
    {
        $education = DEducation::create([
            'application_id' => $this->application->id,
            'education_level' => 'DEE',
            'institution_name' => 'Test College',
        ]);

        $file = UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf');
        $version = $this->versionService->createInitialUpload(
            $file,
            $this->application->id,
            'education',
            'certificate',
            $education->id
        );
        $parentPath = $version->fresh()->file_path;

        $altApplication = app(DocumentApplicationService::class)
            ->findOrCreateAlterationApplication($this->application);

        $this->assertEquals('ALT-TEST-001', $altApplication->application_no);
        $this->assertEquals($this->application->id, $altApplication->parent_application_id);
        $this->assertEquals(0, DEducation::where('application_id', $altApplication->id)->count());

        $altDocument = DDocument::forGroup($altApplication->id, 'education', $education->id, 'certificate')
            ->active()
            ->first();

        $this->assertNotNull($altDocument);
        $this->assertEquals(DocumentVersionStatus::APPROVED, $altDocument->status);
        $this->assertEquals($parentPath, $altDocument->file_path);
        $this->assertEquals($parentPath, $altDocument->old_file_path);
        $this->assertEquals($this->application->id, $altDocument->parent_application_id);
        $this->assertEquals(DocumentApplicationType::ALTERATION, $altDocument->application_type);
        Storage::disk('private_documents')->assertExists($parentPath);
        $this->assertEquals(1, count(Storage::disk('private_documents')->allFiles('FORM_S/NEW/EDUCATION')));
    }
}
