<?php

namespace App\Http\Controllers\DocumentVersion;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentVersion\AlterationDocumentRequest;
use App\Http\Requests\DocumentVersion\ApproveDocumentRequest;
use App\Http\Requests\DocumentVersion\ResetDocumentModuleRequest;
use App\Http\Requests\DocumentVersion\RejectDocumentRequest;
use App\Http\Requests\DocumentVersion\UploadDocumentVersionRequest;
use App\Models\DApplication;
use App\Models\DDocument;
use App\Models\DEducation;
use App\Models\DExperience;
use App\Services\DocumentVersion\DocumentApplicationService;
use App\Services\DocumentVersion\DocumentApprovalService;
use App\Services\DocumentVersion\DocumentGroupKey;
use App\Services\DocumentVersion\DocumentModuleResetService;
use App\Services\DocumentVersion\DocumentStorageService;
use App\Services\DocumentVersion\DocumentVersionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class DocumentSampleController extends Controller
{
    public function __construct(
        protected DocumentVersionService $versionService,
        protected DocumentApprovalService $approvalService,
        protected DocumentStorageService $storageService,
        protected DocumentModuleResetService $resetService,
        protected DocumentApplicationService $applicationService
    ) {}

    public function tableData(Request $request): View
    {
        $selectedApplicationId = (int) $request->session()->get('document_version_application_id', 0);
        $selectedApplication = $selectedApplicationId ? DApplication::find($selectedApplicationId) : null;
        $highlightApplicationIds = collect([$selectedApplicationId])->filter();

        if ($selectedApplication?->parent_application_id) {
            $highlightApplicationIds->push($selectedApplication->parent_application_id);
        }

        if ($selectedApplication) {
            $highlightApplicationIds = $highlightApplicationIds->merge(
                $selectedApplication->alterationApplications()->pluck('id')
            );
        }

        $highlightApplicationIds = $highlightApplicationIds->unique()->filter()->values();

        return view('document-version.sample.table-data', [
            'applications' => DApplication::with(['parentApplication', 'alterationApplications'])
                ->orderBy('id')
                ->get(),
            'educations' => DEducation::with('application')->orderBy('id')->get(),
            'experiences' => DExperience::with('application')->orderBy('id')->get(),
            'documents' => DDocument::orderBy('id')->get(),
            'selectedApplicationId' => $selectedApplicationId,
            'highlightApplicationIds' => $highlightApplicationIds,
        ]);
    }

    public function storageExplorer(Request $request): View
    {
        $storage = $this->storageService->listStorageTree();
        $selectedApplicationId = (int) $request->session()->get('document_version_application_id', 0);
        $selectedApplication = $selectedApplicationId ? DApplication::find($selectedApplicationId) : null;
        $highlightFolder = $selectedApplication?->application_no;

        $documentsByPath = DDocument::query()
            ->get(['id', 'file_path', 'file_name', 'status', 'storage_type'])
            ->keyBy('file_path');

        return view('document-version.sample.storage-explorer', [
            'tree' => $storage['tree'],
            'stats' => $storage['stats'],
            'highlightFolder' => $highlightFolder,
            'selectedApplication' => $selectedApplication,
            'documentsByPath' => $documentsByPath,
            'moduleCounts' => [
                'applications' => DApplication::count(),
                'educations' => DEducation::count(),
                'experiences' => DExperience::count(),
                'documents' => DDocument::count(),
            ],
        ]);
    }

    public function resetModule(ResetDocumentModuleRequest $request): RedirectResponse
    {
        try {
            $summary = $this->resetService->resetAll();
        } catch (\Throwable $e) {
            return back()->with('error', 'Reset failed: ' . $e->getMessage());
        }

        $request->session()->forget('document_version_application_id');

        return redirect()
            ->route('document-version.sample.storage')
            ->with('success', sprintf(
                'Module reset complete. Deleted %d application(s), %d education row(s), %d experience row(s), %d document row(s), and %d file(s) from storage.',
                $summary['applications'],
                $summary['educations'],
                $summary['experiences'],
                $summary['documents'],
                $summary['files_removed']
            ));
    }

    public function index(Request $request): View
    {
        $applicationId = (int) $request->session()->get('document_version_application_id', 0);
        $application = $applicationId
            ? DApplication::with(['educations', 'experiences', 'parentApplication'])->find($applicationId)
            : null;
        $applications = DApplication::with('parentApplication')->orderByDesc('id')->get();
        $documents = $application
            ? $this->versionService->listDocumentsForApplication($application->id)
            : collect();

        $educationRows = collect();
        $experienceRows = collect();

        if ($application) {
            $masterApplication = $this->applicationService->masterApplication($application);
            $masterApplication->loadMissing(['educations', 'experiences']);

            $educationRows = $masterApplication->educations->map(function (DEducation $edu) use ($application) {
                $doc = $this->versionService->getDocumentSummaryForRef(
                    $application->id,
                    'education',
                    $edu->id,
                    'certificate'
                );

                return [
                    'id' => $edu->id,
                    'education_level' => $edu->education_level,
                    'institution_name' => $edu->institution_name,
                    'certificate_no' => $edu->certificate_no,
                    'file_path' => $edu->file_path,
                    'document' => $doc,
                ];
            });

            $experienceRows = $masterApplication->experiences->map(function (DExperience $exp) use ($application) {
                $doc = $this->versionService->getDocumentSummaryForRef(
                    $application->id,
                    'experience',
                    $exp->id,
                    'experience_doc'
                );

                return [
                    'id' => $exp->id,
                    'company_name' => $exp->company_name,
                    'designation' => $exp->designation,
                    'file_path' => $exp->file_path,
                    'document' => $doc,
                ];
            });
        }

        return view('document-version.sample.index', [
            'application' => $application,
            'applications' => $applications,
            'documents' => $documents,
            'educationRows' => $educationRows,
            'experienceRows' => $experienceRows,
            'requestContext' => (string) $request->session()->get('document_version_request_context', 'NEW'),
            'documentTypes' => config('document_versioning.document_types', []),
            'moduleTypes' => config('document_versioning.module_types', []),
            'currentYear' => (int) date('Y'),
        ]);
    }

    public function createApplication(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'application_no' => ['required', 'string', 'max:30', 'unique:d_applications,application_no'],
            'applicant_name' => ['required', 'string', 'max:150'],
            'request_context' => ['nullable', 'in:NEW,RENEWAL,DIGITISATION'],
        ]);

        $application = DApplication::create([
            'application_no' => $data['application_no'],
            'applicant_name' => $data['applicant_name'],
            'status' => 'DRAFT',
            'request_context' => strtoupper((string) ($data['request_context'] ?? 'NEW')),
        ]);

        $request->session()->put('document_version_application_id', $application->id);
        $request->session()->put(
            'document_version_request_context',
            strtoupper((string) ($data['request_context'] ?? 'NEW'))
        );

        return redirect()
            ->route('document-version.sample.index')
            ->with('success', 'Application ' . $application->application_no . ' created.');
    }

    public function setApplication(Request $request): RedirectResponse
    {
        $request->validate([
            'application_id' => ['required', 'integer', 'exists:d_applications,id'],
            'request_context' => ['nullable', 'in:NEW,RENEWAL,DIGITISATION,ALTERATION'],
        ]);

        $requestContext = strtoupper((string) $request->input('request_context', 'NEW'));
        $selectedApplication = DApplication::findOrFail((int) $request->input('application_id'));

        if ($requestContext === 'ALTERATION') {
            $parentApplication = $selectedApplication->isAlterationApplication()
                ? ($selectedApplication->parentApplication ?? $selectedApplication)
                : $selectedApplication;

            $alterationApplication = $this->applicationService->findOrCreateAlterationApplication($parentApplication);

            $request->session()->put('document_version_application_id', $alterationApplication->id);
            $request->session()->put('document_version_request_context', 'ALTERATION');

            return redirect()
                ->route('document-version.sample.index')
                ->with('success', "Alteration application {$alterationApplication->application_no} opened (parent: {$parentApplication->application_no}).");
        }

        if ($requestContext === 'RENEWAL') {
            $parentApplication = $selectedApplication->isRenewalApplication()
                ? ($selectedApplication->parentApplication ?? $selectedApplication)
                : $selectedApplication;

            $renewalApplication = $this->applicationService->findOrCreateRenewalApplication($parentApplication);

            $request->session()->put('document_version_application_id', $renewalApplication->id);
            $request->session()->put('document_version_request_context', 'RENEWAL');

            return redirect()
                ->route('document-version.sample.index')
                ->with('success', "Renewal application {$renewalApplication->application_no} opened (parent: {$parentApplication->application_no}).");
        }

        $request->session()->put('document_version_application_id', $selectedApplication->id);
        $request->session()->put('document_version_request_context', $requestContext);

        return redirect()
            ->route('document-version.sample.index')
            ->with('success', 'Application selected.');
    }

    public function storeApplicationRows(Request $request): RedirectResponse
    {
        $sourceApplicationId = (int) $request->input('application_id');
        $sourceApplication = DApplication::findOrFail($sourceApplicationId);
        $requestContext = strtoupper((string) $request->session()->get('document_version_request_context', 'NEW'));
        if (!in_array($requestContext, ['NEW', 'RENEWAL', 'DIGITISATION', 'ALTERATION'], true)) {
            $requestContext = 'NEW';
        }

        $applicationId = $sourceApplicationId;
        if ($requestContext === 'ALTERATION' && !$sourceApplication->isAlterationApplication()) {
            $alterationApplication = $this->applicationService->findOrCreateAlterationApplication($sourceApplication);
            $applicationId = $alterationApplication->id;
        } elseif ($requestContext === 'RENEWAL' && !$sourceApplication->isRenewalApplication()) {
            $renewalApplication = $this->applicationService->findOrCreateRenewalApplication($sourceApplication);
            $applicationId = $renewalApplication->id;
        }

        $maxKb = config('document_versioning.max_file_size_kb', 5120);

        $request->validate([
            'application_id' => ['required', 'integer', 'exists:d_applications,id'],
            'education_id' => ['array'],
            'education_id.*' => ['nullable', 'integer'],
            'education_level' => ['array'],
            'education_level.*' => ['nullable', 'string', 'max:100'],
            'institution_name' => ['array'],
            'institution_name.*' => ['nullable', 'string', 'max:255'],
            'year_of_passing' => ['array'],
            'year_of_passing.*' => ['nullable', 'string', 'max:20'],
            'percentage_grade' => ['array'],
            'percentage_grade.*' => ['nullable', 'string', 'max:50'],
            'certificate_no' => ['array'],
            'certificate_no.*' => ['nullable', 'string', 'max:100'],
            'education_document' => ['array'],
            'education_document.*' => ['nullable', 'file', 'mimes:pdf', 'max:' . $maxKb],
            'education_alteration_reason' => ['array'],
            'education_alteration_reason.*' => ['nullable', 'string', 'max:1000'],
            'experience_id' => ['array'],
            'experience_id.*' => ['nullable', 'integer'],
            'company_name' => ['array'],
            'company_name.*' => ['nullable', 'string', 'max:255'],
            'years_of_experience' => ['array'],
            'years_of_experience.*' => ['nullable', 'string', 'max:50'],
            'designation' => ['array'],
            'designation.*' => ['nullable', 'string', 'max:255'],
            'experience_document' => ['array'],
            'experience_document.*' => ['nullable', 'file', 'mimes:pdf', 'max:' . $maxKb],
            'experience_alteration_reason' => ['array'],
            'experience_alteration_reason.*' => ['nullable', 'string', 'max:1000'],
        ]);

        $savedEducations = 0;
        $savedExperiences = 0;
        $sessionApplicationId = $applicationId;
        $workflowApplication = DApplication::findOrFail($applicationId);
        $masterApplication = $this->applicationService->masterApplication($workflowApplication);
        $masterApplicationId = $masterApplication->id;

        try {
            DB::transaction(function () use ($request, $sourceApplication, $applicationId, $masterApplicationId, $requestContext, &$savedEducations, &$savedExperiences, &$sessionApplicationId) {
            $educationLevels = $request->input('education_level', []);
            foreach ($educationLevels as $index => $level) {
                $level = trim((string) $level);
                $institution = trim((string) ($request->input('institution_name.' . $index) ?? ''));
                $file = $request->file('education_document.' . $index);
                $existingId = $request->input('education_id.' . $index);

                if ($level === '' && $institution === '' && !$file && !$existingId) {
                    continue;
                }

                if ($existingId) {
                    $education = DEducation::where('application_id', $masterApplicationId)->findOrFail($existingId);

                    if ($level !== '' || $institution !== '') {
                        $year = trim((string) ($request->input('year_of_passing.' . $index) ?? ''));
                        $grade = trim((string) ($request->input('percentage_grade.' . $index) ?? ''));
                        $certNo = trim((string) ($request->input('certificate_no.' . $index) ?? ''));
                        if ($year !== '' || $grade !== '') {
                            $certNo = trim($year . ($grade !== '' ? ' | ' . $grade : ''));
                        }

                        $education->update([
                            'education_level' => $level ?: $education->education_level,
                            'institution_name' => $institution ?: $education->institution_name,
                            'certificate_no' => $certNo ?: $education->certificate_no,
                        ]);
                    }
                } else {
                    if ($level === '' || $institution === '') {
                        throw new RuntimeException('Education row ' . ($index + 1) . ': level and institution are required.');
                    }

                    $year = trim((string) ($request->input('year_of_passing.' . $index) ?? ''));
                    $grade = trim((string) ($request->input('percentage_grade.' . $index) ?? ''));
                    $certNo = trim((string) ($request->input('certificate_no.' . $index) ?? ''));
                    if ($certNo === '' && ($year !== '' || $grade !== '')) {
                        $certNo = trim($year . ($grade !== '' ? ' | ' . $grade : ''));
                    }

                    $education = DEducation::create([
                        'application_id' => $masterApplicationId,
                        'education_level' => $level,
                        'institution_name' => $institution,
                        'certificate_no' => $certNo ?: null,
                    ]);
                    $savedEducations++;
                }

                if ($file) {
                    $sessionApplicationId = $this->handleRowDocumentUpload(
                        $sourceApplication,
                        $applicationId,
                        $education->id,
                        'education',
                        'certificate',
                        $file,
                        trim((string) ($request->input('education_alteration_reason.' . $index) ?? '')),
                        $requestContext,
                        'Education row ' . ($index + 1)
                    );
                }
            }

            $companyNames = $request->input('company_name', []);
            foreach ($companyNames as $index => $company) {
                $company = trim((string) $company);
                $designation = trim((string) ($request->input('designation.' . $index) ?? ''));
                $years = trim((string) ($request->input('years_of_experience.' . $index) ?? ''));
                $file = $request->file('experience_document.' . $index);
                $existingId = $request->input('experience_id.' . $index);

                if ($company === '' && $designation === '' && !$file && !$existingId) {
                    continue;
                }

                if ($existingId) {
                    $experience = DExperience::where('application_id', $masterApplicationId)->findOrFail($existingId);

                    if ($company !== '' || $designation !== '') {
                        $designationValue = $years !== '' ? ($designation ?: $experience->designation) . ' (' . $years . ' yrs)' : ($designation ?: $experience->designation);
                        $experience->update([
                            'company_name' => $company ?: $experience->company_name,
                            'designation' => $designationValue,
                        ]);
                    }
                } else {
                    if ($company === '' || $designation === '') {
                        throw new RuntimeException('Experience row ' . ($index + 1) . ': company and designation are required.');
                    }

                    $designationValue = $years !== '' ? $designation . ' (' . $years . ' yrs)' : $designation;

                    $experience = DExperience::create([
                        'application_id' => $masterApplicationId,
                        'company_name' => $company,
                        'designation' => $designationValue,
                    ]);
                    $savedExperiences++;
                }

                if ($file) {
                    $sessionApplicationId = $this->handleRowDocumentUpload(
                        $sourceApplication,
                        $applicationId,
                        $experience->id,
                        'experience',
                        'experience_doc',
                        $file,
                        trim((string) ($request->input('experience_alteration_reason.' . $index) ?? '')),
                        $requestContext,
                        'Experience row ' . ($index + 1)
                    );
                }
            }

            DApplication::whereKey($applicationId)->update([
                'status' => match ($requestContext) {
                    'DIGITISATION' => 'DIGITIZATION',
                    'ALTERATION' => 'ALTERATION',
                    default => 'SUBMITTED',
                },
            ]);

            $workingApplication = DApplication::findOrFail($applicationId);
            if (in_array($requestContext, ['RENEWAL', 'ALTERATION', 'DIGITISATION'], true)) {
                $this->versionService->ensureCarriedForwardDocuments($workingApplication, $requestContext);
            }
            });
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $request->session()->put('document_version_application_id', $sessionApplicationId);
        if (DApplication::find($sessionApplicationId)?->isChildApplication()) {
            $request->session()->put('document_version_request_context', DApplication::find($sessionApplicationId)?->request_context ?? 'NEW');
        }

        $successMessage = "Application submitted. Saved new education rows: {$savedEducations}, new experience rows: {$savedExperiences}.";
        $activeApplication = DApplication::find($sessionApplicationId);
        if ($activeApplication?->isChildApplication()) {
            $successMessage .= " Working on {$activeApplication->request_context} application {$activeApplication->application_no}.";
        }

        return redirect()
            ->route('document-version.sample.index')
            ->with('success', $successMessage);
    }

    public function deleteEducation(Request $request, int $id): RedirectResponse
    {
        $education = DEducation::findOrFail($id);
        $applicationId = $education->application_id;
        $education->delete();

        $request->session()->put('document_version_application_id', $applicationId);

        return back()->with('success', 'Education row deleted.');
    }

    public function deleteExperience(Request $request, int $id): RedirectResponse
    {
        $experience = DExperience::findOrFail($id);
        $applicationId = $experience->application_id;
        $experience->delete();

        $request->session()->put('document_version_application_id', $applicationId);

        return back()->with('success', 'Experience row deleted.');
    }

    public function alteration(Request $request): View
    {
        $applicationId = (int) $request->session()->get('document_version_application_id', 0);
        $application = $applicationId
            ? DApplication::with('parentApplication')->find($applicationId)
            : null;
        $parentApplication = $application?->isAlterationApplication()
            ? $application->parentApplication
            : $application;
        $alterableDocuments = $parentApplication
            ? $this->versionService->listAlterableDocumentsForApplication($parentApplication->id)
            : collect();

        return view('document-version.sample.alteration', [
            'application' => $application,
            'parentApplication' => $parentApplication,
            'alterableDocuments' => $alterableDocuments,
            'documentTypes' => config('document_versioning.document_types', []),
            'moduleTypes' => config('document_versioning.module_types', []),
        ]);
    }

    public function alterationForm(string $groupKey): View|RedirectResponse
    {
        $summary = $this->versionService->getGroupSummary($groupKey);

        if (empty($summary) || !$summary['active_version']) {
            abort(404, 'No approved document found for alteration.');
        }

        if ($summary['pending_version']) {
            return redirect()
                ->route('document-version.sample.review', $groupKey)
                ->with('error', 'A pending alteration already exists for this document.');
        }

        return view('document-version.sample.alteration-form', [
            'summary' => $summary,
            'groupKey' => $groupKey,
            'activeVersion' => $summary['active_version'],
            'documentTypes' => config('document_versioning.document_types', []),
            'moduleTypes' => config('document_versioning.module_types', []),
        ]);
    }

    public function storeAlteration(string $groupKey, AlterationDocumentRequest $request): RedirectResponse
    {
        try {
            $group = DocumentGroupKey::decode($groupKey);
            $parentApplication = DApplication::findOrFail($group['application_id']);
            $alterationApplication = $this->applicationService->findOrCreateAlterationApplication($parentApplication);
            $moduleRefId = $this->applicationService->mapModuleRefId(
                $parentApplication,
                $alterationApplication,
                $group['module_type'],
                $group['module_ref_id']
            );

            $version = $this->versionService->uploadNewVersion(
                file: $request->file('document_file'),
                applicationId: $alterationApplication->id,
                moduleType: $group['module_type'],
                documentType: $group['document_type'],
                moduleRefId: $moduleRefId,
                remarks: 'Alteration request: ' . trim((string) $request->input('alteration_reason')),
                workflowStage: 'ALTERATION'
            );

            $groupKey = DocumentGroupKey::encode(
                $alterationApplication->id,
                $group['module_type'],
                $moduleRefId,
                $group['document_type']
            );
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $request->session()->put('document_version_application_id', $alterationApplication->id);
        $request->session()->put('document_version_request_context', 'ALTERATION');

        return redirect()
            ->route('document-version.sample.review', $groupKey)
            ->with('success', "Alteration uploaded on {$alterationApplication->application_no} as version {$version->version_no}. Pending approval.");
    }

    public function upload(Request $request): View
    {
        $applicationId = (int) $request->session()->get('document_version_application_id', 0);
        $application = $applicationId
            ? DApplication::with(['educations', 'experiences', 'parentApplication'])->findOrFail($applicationId)
            : null;
        $masterApplication = $application ? $this->applicationService->masterApplication($application) : null;

        $documents = $application
            ? $this->versionService->listDocumentsForApplication($application->id)
            : collect();

        return view('document-version.sample.upload', [
            'application' => $application,
            'documents' => $documents,
            'documentTypes' => config('document_versioning.document_types', []),
            'moduleTypes' => config('document_versioning.module_types', []),
            'educationOptions' => $masterApplication ? $masterApplication->educations->map(function ($e) {
                return ['id' => $e->id, 'label' => $e->education_level . ' — ' . $e->institution_name];
            })->values() : collect(),
            'experienceOptions' => $masterApplication ? $masterApplication->experiences->map(function ($e) {
                return ['id' => $e->id, 'label' => $e->company_name . ' — ' . $e->designation];
            })->values() : collect(),
        ]);
    }

    public function storeUpload(UploadDocumentVersionRequest $request): RedirectResponse
    {
        $moduleRefId = $request->input('module_ref_id');
        $requestContext = strtoupper((string) $request->session()->get('document_version_request_context', 'NEW'));
        if (!in_array($requestContext, ['NEW', 'RENEWAL', 'DIGITISATION'], true)) {
            $requestContext = 'NEW';
        }

        try {
            $this->versionService->uploadNewVersion(
                file: $request->file('document_file'),
                applicationId: (int) $request->input('application_id'),
                moduleType: $request->input('module_type'),
                documentType: $request->input('document_type'),
                moduleRefId: $moduleRefId !== null && $moduleRefId !== '' ? (int) $moduleRefId : null,
                remarks: $request->input('remarks'),
                workflowStage: $requestContext
            );
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $request->session()->put('document_version_application_id', $request->input('application_id'));

        return redirect()
            ->route('document-version.sample.upload')
            ->with('success', 'Document uploaded. New version is pending approval.');
    }

    public function review(string $groupKey): View
    {
        $summary = $this->versionService->getGroupSummary($groupKey);

        if (empty($summary)) {
            abort(404, 'Document group not found.');
        }

        $pending = $summary['pending_version'];
        $stepper = $pending ? $this->approvalService->getStepperState($pending) : [];

        return view('document-version.sample.review', [
            'summary' => $summary,
            'groupKey' => $groupKey,
            'activeVersion' => $summary['active_version'],
            'pendingVersion' => $pending,
            'approvalLevels' => $this->approvalService->getApprovalLevels(),
            'stepper' => $stepper,
            'documentTypes' => config('document_versioning.document_types', []),
            'moduleTypes' => config('document_versioning.module_types', []),
        ]);
    }

    public function approve(string $groupKey, ApproveDocumentRequest $request): RedirectResponse
    {
        $pending = $this->versionService->getPendingVersionByGroupKey($groupKey);

        if (!$pending) {
            return back()->with('error', 'No pending version found for this document.');
        }

        try {
            $this->approvalService->approve(
                $pending->id,
                (int) $request->input('approval_level'),
                Auth::id(),
                $request->input('remarks')
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('document-version.sample.review', $groupKey)
            ->with('success', 'Document approved at level ' . $request->input('approval_level') . '.');
    }

    public function reject(string $groupKey, RejectDocumentRequest $request): RedirectResponse
    {
        $pending = $this->versionService->getPendingVersionByGroupKey($groupKey);

        if (!$pending) {
            return back()->with('error', 'No pending version found for this document.');
        }

        try {
            $this->approvalService->reject(
                $pending->id,
                (int) $request->input('approval_level'),
                Auth::id(),
                $request->input('remarks')
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('document-version.sample.review', $groupKey)
            ->with('success', 'Document rejected at level ' . $request->input('approval_level') . '.');
    }

    public function history(string $groupKey): View
    {
        $summary = $this->versionService->getGroupSummary($groupKey);

        if (empty($summary)) {
            abort(404, 'Document group not found.');
        }

        $versions = $this->versionService->getVersionHistory(
            $summary['application_id'],
            $summary['module_type'],
            $summary['module_ref_id'],
            $summary['document_type']
        );

        return view('document-version.sample.history', [
            'summary' => $summary,
            'groupKey' => $groupKey,
            'versions' => $versions,
            'documentTypes' => config('document_versioning.document_types', []),
            'moduleTypes' => config('document_versioning.module_types', []),
        ]);
    }

    public function download(int $versionId): Response
    {
        $version = $this->versionService->findVersionById($versionId);

        return $this->storageService->download($version->file_path, $version->file_name);
    }

    public function applicationDetails(Request $request, ?int $application = null): View
    {
        $applications = DApplication::orderByDesc('id')->get();
        $applicationId = $application ?? (int) $request->session()->get('document_version_application_id', 0);
        $applicationModel = $applicationId
            ? DApplication::with(['educations', 'experiences'])->find($applicationId)
            : null;

        if ($applicationModel) {
            $request->session()->put('document_version_application_id', $applicationModel->id);
        }

        $detail = $this->buildApplicationDetailData($applicationModel);

        return view('document-version.sample.application-details', array_merge($detail, [
            'application' => $applicationModel,
            'applications' => $applications,
            'documentTypes' => config('document_versioning.document_types', []),
            'moduleTypes' => config('document_versioning.module_types', []),
        ]));
    }

    protected function handleRowDocumentUpload(
        DApplication $sourceApplication,
        int $workingApplicationId,
        int $moduleRefId,
        string $moduleType,
        string $documentType,
        $file,
        string $alterationReason,
        string $requestContext,
        string $label
    ): int {
        $parentApplication = $sourceApplication->isChildApplication()
            ? ($sourceApplication->parentApplication ?? $sourceApplication)
            : $sourceApplication;

        $parentRefId = $moduleRefId;
        if ($workingApplicationId !== $parentApplication->id) {
            $parentRefId = $this->applicationService->resolveParentModuleRefId(
                $workingApplicationId,
                $parentApplication->id,
                $moduleType,
                $moduleRefId
            );
        }

        $parentDocSummary = $this->versionService->getDocumentSummaryForRef(
            $parentApplication->id,
            $moduleType,
            $parentRefId,
            $documentType
        );

        $targetApplicationId = $workingApplicationId;
        $targetModuleRefId = $moduleRefId;
        $workflowStage = in_array($requestContext, ['ALTERATION', 'RENEWAL', 'DIGITISATION'], true)
            ? $requestContext
            : 'NEW';
        $isRenewalContext = $requestContext === 'RENEWAL' || $sourceApplication->isRenewalApplication();
        $isAlterationContext = $requestContext === 'ALTERATION' || $sourceApplication->isAlterationApplication();
        $reasonLabel = $isRenewalContext ? 'renewal reason' : 'alteration reason';

        if (
            !empty($parentDocSummary['active'])
            && !$sourceApplication->isChildApplication()
            && !$isRenewalContext
            && !$isAlterationContext
        ) {
            if ($alterationReason === '') {
                throw new RuntimeException(
                    "{$label}: {$reasonLabel} is required when replacing an approved document."
                );
            }

            $alterationApplication = $this->applicationService->findOrCreateAlterationApplication($parentApplication);
            $targetApplicationId = $alterationApplication->id;
            $targetModuleRefId = $this->applicationService->mapModuleRefId(
                $parentApplication,
                $alterationApplication,
                $moduleType,
                $moduleRefId
            );
            $workflowStage = 'ALTERATION';
            $alterationReason = 'Alteration request: ' . $alterationReason;
        } elseif ($isAlterationContext) {
            $workflowStage = 'ALTERATION';
        } elseif ($isRenewalContext) {
            $workflowStage = 'RENEWAL';
        }

        $targetDocSummary = $this->versionService->getDocumentSummaryForRef(
            $targetApplicationId,
            $moduleType,
            $targetModuleRefId,
            $documentType
        );

        if (!empty($targetDocSummary['active'])) {
            if ($alterationReason === '') {
                throw new RuntimeException(
                    "{$label}: {$reasonLabel} is required when replacing an approved document."
                );
            }

            $workflowStage = $isRenewalContext ? 'RENEWAL' : 'ALTERATION';
            $prefix = $isRenewalContext ? 'Renewal request:' : 'Alteration request:';
            $remarks = str_starts_with($alterationReason, $prefix)
                ? $alterationReason
                : $prefix . ' ' . $alterationReason;
        } else {
            $remarks = $alterationReason !== ''
                ? $alterationReason
                : ucfirst(str_replace('_', ' ', $moduleType)) . ' document upload';
        }

        $this->versionService->uploadNewVersion(
            file: $file,
            applicationId: $targetApplicationId,
            moduleType: $moduleType,
            documentType: $documentType,
            moduleRefId: $targetModuleRefId,
            remarks: $remarks,
            workflowStage: $workflowStage
        );

        return $targetApplicationId;
    }

    protected function buildApplicationDetailData(?DApplication $application): array
    {
        if (!$application) {
            return [
                'educationRows' => collect(),
                'experienceRows' => collect(),
                'documentGroups' => collect(),
                'allDocumentVersions' => collect(),
            ];
        }

        $masterApplication = $this->applicationService->masterApplication($application);
        $masterApplication->loadMissing(['educations', 'experiences']);

        $educationRows = $masterApplication->educations->map(function (DEducation $edu) use ($application) {
            $doc = $this->versionService->getDocumentSummaryForRef(
                $application->id,
                'education',
                $edu->id,
                'certificate'
            );

            return [
                'id' => $edu->id,
                'education_level' => $edu->education_level,
                'institution_name' => $edu->institution_name,
                'certificate_no' => $edu->certificate_no,
                'created_at' => $edu->created_at,
                'document' => $doc,
            ];
        });

        $experienceRows = $masterApplication->experiences->map(function (DExperience $exp) use ($application) {
            $doc = $this->versionService->getDocumentSummaryForRef(
                $application->id,
                'experience',
                $exp->id,
                'experience_doc'
            );

            return [
                'id' => $exp->id,
                'company_name' => $exp->company_name,
                'designation' => $exp->designation,
                'created_at' => $exp->created_at,
                'document' => $doc,
            ];
        });

        $documentGroups = $this->versionService->listDocumentsForApplication($application->id);

        $allDocumentVersions = DDocument::forApplication($application->id)
            ->orderBy('module_type')
            ->orderBy('module_ref_id')
            ->orderBy('document_type')
            ->orderByDesc('version_no')
            ->get();

        return [
            'educationRows' => $educationRows,
            'experienceRows' => $experienceRows,
            'documentGroups' => $documentGroups,
            'allDocumentVersions' => $allDocumentVersions,
        ];
    }
}
