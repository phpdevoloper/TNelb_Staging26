<?php

namespace App\Http\Controllers\WithoutTmp;

use App\Enums\ScertAlterationStatus;
use App\Enums\ScertAppStatus;
use App\Http\Controllers\Controller;
use App\Models\CAlterationRequest;
use App\Models\CEducation;
use App\Models\CExperience;
use App\Models\CPhoto;
use App\Models\CSignature;
use App\Models\ScertApp;
use App\Services\WithoutTmp\ScertAlterationService;
use App\Services\WithoutTmp\ScertApplicationService;
use App\Services\WithoutTmp\WithoutTmpStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class WithoutTmpController extends Controller
{
    public function __construct(
        protected ScertApplicationService $applicationService,
        protected ScertAlterationService $alterationService,
        protected WithoutTmpStorageService $storageService
    ) {}

    public function index(Request $request): View
    {
        $applicationId = (int) $request->session()->get('without_tmp_application_id', 0);
        $application = $applicationId
            ? ScertApp::with(['educations', 'experiences', 'photo', 'signature', 'documents'])->find($applicationId)
            : null;

        $pendingAlterations = $this->pendingAlterationsMap($application);
        $pendingReviewItems = CAlterationRequest::with('application')
            ->when($applicationId, fn ($q) => $q->where('application_id', $applicationId))
            ->where('status', ScertAlterationStatus::PENDING)
            ->orderByDesc('id')
            ->get();

        return view('without-tmp.index', [
            'application' => $application,
            'applications' => ScertApp::orderByDesc('id')->get(),
            'pendingAlterations' => $pendingAlterations,
            'pendingReviewItems' => $pendingReviewItems,
        ]);
    }

    protected function pendingAlterationsMap(?ScertApp $application): \Illuminate\Support\Collection
    {
        if (!$application) {
            return collect();
        }

        return CAlterationRequest::query()
            ->where('application_id', $application->id)
            ->where('status', ScertAlterationStatus::PENDING)
            ->get()
            ->keyBy(fn (CAlterationRequest $row) => $row->target_table . ':' . $row->target_row_id);
    }

    public function createApplication(Request $request): RedirectResponse
    {
        $request->validate([
            'applicant_name' => ['required', 'string', 'max:150'],
            'create_type' => ['required', 'in:draft,digitization'],
        ]);

        $status = $request->input('create_type') === 'digitization'
            ? ScertAppStatus::DIGITIZATION
            : ScertAppStatus::DRAFT;

        $application = $this->applicationService->createApplication(
            $request->input('applicant_name'),
            $status
        );

        $request->session()->put('without_tmp_application_id', $application->id);

        return redirect()
            ->route('without-tmp.index')
            ->with('success', "Application {$application->application_code} created ({$status->label()}).");
    }

    public function setApplication(Request $request): RedirectResponse
    {
        $request->validate([
            'application_id' => ['required', 'integer', 'exists:scert_app,id'],
        ]);

        $request->session()->put('without_tmp_application_id', (int) $request->input('application_id'));

        return redirect()->route('without-tmp.index');
    }

    public function storeApplication(Request $request): RedirectResponse
    {
        $application = ScertApp::findOrFail((int) $request->input('application_id'));
        $maxKb = config('without_tmp.max_file_size_kb', 5120);
        $submit = $request->input('action') === 'submit';

        $request->validate([
            'application_id' => ['required', 'integer', 'exists:scert_app,id'],
            'applicant_name' => ['nullable', 'string', 'max:150'],
            'education_level' => ['array'],
            'education_level.*' => ['nullable', 'string', 'max:100'],
            'institution_name' => ['array'],
            'institution_name.*' => ['nullable', 'string', 'max:255'],
            'year_of_passing' => ['array'],
            'year_of_passing.*' => ['nullable', 'string', 'max:20'],
            'grade' => ['array'],
            'grade.*' => ['nullable', 'string', 'max:50'],
            'education_file' => ['array'],
            'education_file.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:' . $maxKb],
            'education_alteration_reason' => ['array'],
            'education_alteration_reason.*' => ['nullable', 'string', 'max:1000'],
            'experience_id' => ['array'],
            'company_name' => ['array'],
            'company_name.*' => ['nullable', 'string', 'max:255'],
            'years_of_experience' => ['array'],
            'years_of_experience.*' => ['nullable', 'string', 'max:50'],
            'designation' => ['array'],
            'designation.*' => ['nullable', 'string', 'max:255'],
            'experience_file' => ['array'],
            'experience_file.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:' . $maxKb],
            'experience_alteration_reason' => ['array'],
            'experience_alteration_reason.*' => ['nullable', 'string', 'max:1000'],
            'photo_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:' . $maxKb],
            'photo_alteration_reason' => ['nullable', 'string', 'max:1000'],
            'signature_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:' . $maxKb],
            'signature_alteration_reason' => ['nullable', 'string', 'max:1000'],
            'document_label' => ['array'],
            'document_label.*' => ['nullable', 'string', 'max:150'],
            'document_file' => ['array'],
            'document_file.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:' . $maxKb],
            'document_alteration_reason' => ['array'],
            'document_alteration_reason.*' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->applicationService->saveApplication($application, [
                'applicant_name' => $request->input('applicant_name'),
                'education_id' => $request->input('education_id', []),
                'education_level' => $request->input('education_level', []),
                'institution_name' => $request->input('institution_name', []),
                'year_of_passing' => $request->input('year_of_passing', []),
                'grade' => $request->input('grade', []),
                'education_file' => $request->file('education_file', []),
                'education_alteration_reason' => $request->input('education_alteration_reason', []),
                'experience_id' => $request->input('experience_id', []),
                'company_name' => $request->input('company_name', []),
                'years_of_experience' => $request->input('years_of_experience', []),
                'designation' => $request->input('designation', []),
                'experience_file' => $request->file('experience_file', []),
                'experience_alteration_reason' => $request->input('experience_alteration_reason', []),
                'photo_file' => $request->file('photo_file'),
                'photo_alteration_reason' => $request->input('photo_alteration_reason'),
                'signature_file' => $request->file('signature_file'),
                'signature_alteration_reason' => $request->input('signature_alteration_reason'),
                'document_id' => $request->input('document_id', []),
                'document_label' => $request->input('document_label', []),
                'document_file' => $request->file('document_file', []),
                'document_alteration_reason' => $request->input('document_alteration_reason', []),
            ], $submit);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('without-tmp.index')
            ->with('success', $submit ? 'Application submitted successfully.' : 'Draft saved successfully.');
    }

    public function deleteEducation(Request $request, int $id): RedirectResponse
    {
        $education = CEducation::findOrFail($id);
        $applicationId = $education->application_id;
        $education->delete();

        $request->session()->put('without_tmp_application_id', $applicationId);

        return back()->with('success', 'Education row deleted.');
    }

    public function deleteExperience(Request $request, int $id): RedirectResponse
    {
        $experience = CExperience::findOrFail($id);
        $applicationId = $experience->application_id;
        $experience->delete();

        $request->session()->put('without_tmp_application_id', $applicationId);

        return back()->with('success', 'Experience row deleted.');
    }

    public function alteration(Request $request): View
    {
        $applicationId = (int) $request->session()->get('without_tmp_application_id', 0);
        $application = $applicationId
            ? ScertApp::with(['educations', 'experiences', 'photo', 'signature', 'documents'])->find($applicationId)
            : null;

        $alterableItems = $application && $application->canRequestAlteration()
            ? $this->alterationService->listAlterableItems($application)
            : [];

        $pendingRequests = $application
            ? CAlterationRequest::where('application_id', $application->id)
                ->where('status', 'pending')
                ->orderByDesc('id')
                ->get()
            : collect();

        return view('without-tmp.alteration', [
            'application' => $application,
            'alterableItems' => $alterableItems,
            'pendingRequests' => $pendingRequests,
        ]);
    }

    public function alterationForm(string $targetKey): View
    {
        $target = $this->alterationService->decodeTargetKey($targetKey);
        $applicationId = (int) session('without_tmp_application_id', 0);
        $application = ScertApp::findOrFail($applicationId);
        $row = $this->alterationService->resolveTarget(
            $target['target_table'],
            $target['target_row_id'],
            $application->id
        );

        return view('without-tmp.alteration-form', [
            'application' => $application,
            'targetKey' => $targetKey,
            'targetTable' => $target['target_table'],
            'row' => $row,
        ]);
    }

    public function storeAlteration(Request $request, string $targetKey): RedirectResponse
    {
        $application = ScertApp::findOrFail((int) session('without_tmp_application_id', 0));
        $maxKb = config('without_tmp.max_file_size_kb', 5120);
        $target = $this->alterationService->decodeTargetKey($targetKey);

        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:' . $maxKb],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $this->alterationService->requestAlteration(
                $application,
                $target['target_table'],
                $target['target_row_id'],
                $request->file('file'),
                $request->input('reason')
            );
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('without-tmp.alteration')
            ->with('success', 'Alteration request submitted. Pending file stored separately until approval.');
    }

    public function review(Request $request): View
    {
        $applicationId = (int) $request->session()->get('without_tmp_application_id', 0);

        $pendingReviewItems = CAlterationRequest::with('application')
            ->when($applicationId, fn ($q) => $q->where('application_id', $applicationId))
            ->where('status', ScertAlterationStatus::PENDING)
            ->orderByDesc('id')
            ->get();

        return view('without-tmp.review', [
            'pendingReviewItems' => $pendingReviewItems,
            'selectedApplicationId' => $applicationId,
        ]);
    }

    public function reviewShow(int $id): View|RedirectResponse
    {
        $alteration = CAlterationRequest::with('application')->findOrFail($id);

        if (!$alteration->isPending()) {
            return redirect()
                ->route('without-tmp.review')
                ->with('error', 'This alteration request is no longer pending review.');
        }

        return view('without-tmp.review-detail', [
            'alteration' => $alteration,
            'application' => $alteration->application,
            'targetLabel' => $this->alterationService->describeTarget($alteration),
            'uploadTypeLabel' => $this->alterationService->uploadTypeLabel($alteration->upload_type),
            'reviewerLabel' => 'Supervisor',
        ]);
    }

    public function approveAlteration(Request $request, int $id): RedirectResponse
    {
        $alteration = CAlterationRequest::findOrFail($id);

        try {
            $this->alterationService->approve($alteration, $request->input('remarks'));
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('without-tmp.review')
            ->with('success', 'Alteration approved. Active file reference updated.');
    }

    public function rejectAlteration(Request $request, int $id): RedirectResponse
    {
        $alteration = CAlterationRequest::findOrFail($id);

        try {
            $this->alterationService->reject($alteration, $request->input('remarks'));
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('without-tmp.review')
            ->with('success', 'Alteration rejected. Existing file unchanged.');
    }

    public function downloadFile(Request $request)
    {
        $request->validate([
            'path' => ['required', 'string'],
            'name' => ['nullable', 'string'],
        ]);

        return $this->storageService->download(
            $request->input('path'),
            $request->input('name', basename($request->input('path')))
        );
    }

    public function storageExplorer(): View
    {
        $storage = $this->storageService->listStorageTree();
        $applicationId = (int) session('without_tmp_application_id', 0);
        $application = $applicationId ? ScertApp::find($applicationId) : null;

        return view('without-tmp.storage', [
            'tree' => $storage['tree'],
            'stats' => $storage['stats'],
            'application' => $application,
        ]);
    }

    public function tableData(Request $request): View
    {
        $selectedApplicationId = (int) $request->session()->get('without_tmp_application_id', 0);

        return view('without-tmp.table-data', [
            'applications' => ScertApp::orderByDesc('id')->get(),
            'educations' => CEducation::with('application')->orderByDesc('id')->get(),
            'experiences' => CExperience::with('application')->orderByDesc('id')->get(),
            'photos' => CPhoto::with('application')->orderByDesc('id')->get(),
            'signatures' => CSignature::with('application')->orderByDesc('id')->get(),
            'alterations' => CAlterationRequest::with('application')->orderByDesc('id')->get(),
            'selectedApplicationId' => $selectedApplicationId,
        ]);
    }
}
