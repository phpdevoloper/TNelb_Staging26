<?php

namespace App\Services\FormS;

use App\Models\CC_Education;
use App\Models\CC_Experience;
use App\Models\CC_Proof_doc;
use App\Models\Competency\CC_CompetencyMeta;

/**
 * Copy parent cc_edu / cc_exp / cc_proof_doc onto a renewal or alteration
 * application_id. Unchanged files keep the parent path string; no re-upload.
 */
class FormSChildDocumentSnapshotService
{
    public function __construct(
        protected FormSApplicationWorkflowService $workflowService
    ) {}

    /**
     * @return list<string>
     */
    public static function identityProofNames(): array
    {
        return [
            FormSProofDocumentService::PROOF_AADHAAR,
            FormSProofDocumentService::PROOF_PAN,
            FormSProofDocumentService::PROOF_PHOTO,
            FormSProofDocumentService::PROOF_SIGN,
        ];
    }

    public function isChildSnapshotTarget(CC_CompetencyMeta $workflow): bool
    {
        return $this->workflowService->isChildWorkflow($workflow)
            && (string) $this->workflowService->masterApplication($workflow)->application_id
                !== (string) $workflow->application_id;
    }

    /**
     * Insert parent education rows onto the child. Caller may delete child rows first.
     *
     * @param  array<int, true>  $skipParentEduIds
     */
    public function copyParentEducationToChild(
        CC_CompetencyMeta $child,
        string $loginId,
        array $skipParentEduIds = []
    ): void {
        if (! $this->isChildSnapshotTarget($child)) {
            return;
        }

        $parent = $this->workflowService->masterApplication($child);
        $childId = (string) $child->application_id;

        foreach (CC_Education::where('application_id', $parent->application_id)->orderBy('edu_id')->get() as $parentEdu) {
            $parentEduId = (int) ($parentEdu->edu_id ?? 0);
            if ($parentEduId <= 0 || isset($skipParentEduIds[$parentEduId])) {
                continue;
            }

            $level = trim((string) ($parentEdu->educational_level ?? ''));
            if ($level === '') {
                continue;
            }

            CC_Education::create([
                'login_id' => $loginId !== '' ? $loginId : $parentEdu->login_id,
                'application_id' => $childId,
                'educational_level' => $parentEdu->educational_level,
                'institute_name' => $parentEdu->institute_name,
                'month_passing' => $parentEdu->month_passing,
                'year_of_passing' => $parentEdu->year_of_passing,
                'certificate_no' => $parentEdu->certificate_no,
                'upload_document' => $parentEdu->upload_document,
                'status' => $parentEdu->status,
            ]);
        }
    }

    /**
     * Copy PHOTO / SIGN / AADHAAR / PAN onto the child. Existing child paths
     * (new uploads) are kept. Missing child rows get the parent path string only.
     */
    public function copyParentIdentityProofsToChild(CC_CompetencyMeta $child): void
    {
        if (! $this->isChildSnapshotTarget($child)) {
            return;
        }

        $parent = $this->workflowService->masterApplication($child);
        $childId = (string) $child->application_id;
        $appType = (string) ($child->appl_type ?? '');

        foreach (self::identityProofNames() as $proofName) {
            $parentProof = CC_Proof_doc::where('application_id', $parent->application_id)
                ->where('proof_name', $proofName)
                ->first();
            if (! $parentProof) {
                continue;
            }

            $childProof = CC_Proof_doc::firstOrNew([
                'application_id' => $childId,
                'proof_name' => $proofName,
            ]);
            $isNew = ! $childProof->exists;

            $childProof->app_type = $appType !== '' ? $appType : $parentProof->app_type;
            $childProof->proof_type = $parentProof->proof_type ?: $childProof->proof_type;
            if ($isNew || trim((string) ($childProof->proof_no ?? '')) === '') {
                $childProof->proof_no = $parentProof->proof_no;
            }
            if ($isNew || trim((string) ($childProof->proof_doc ?? '')) === '') {
                $childProof->proof_doc = $parentProof->proof_doc;
            }
            $childProof->status = $childProof->status ?: ($parentProof->status ?: 'A');
            $childProof->updated_at = now()->toDateString();
            if ($isNew) {
                $childProof->created_at = now()->toDateString();
            }
            $childProof->save();
        }
    }

    /**
     * Insert parent experience rows onto the child. Caller may delete child rows first.
     *
     * @param  array<int, true>  $skipParentExpIds
     */
    public function copyParentExperienceToChild(
        CC_CompetencyMeta $child,
        string $loginId,
        array $skipParentExpIds = []
    ): void {
        if (! $this->isChildSnapshotTarget($child)) {
            return;
        }

        $parent = $this->workflowService->masterApplication($child);
        $childId = (string) $child->application_id;

        foreach (CC_Experience::where('application_id', $parent->application_id)->orderBy('exp_id')->get() as $parentExp) {
            $parentExpId = (int) ($parentExp->exp_id ?? 0);
            if ($parentExpId <= 0 || isset($skipParentExpIds[$parentExpId])) {
                continue;
            }

            $orgName = trim((string) ($parentExp->org_name ?? ''));
            $designation = trim((string) ($parentExp->designation ?? ''));
            if ($orgName === '' || $designation === '') {
                continue;
            }

            CC_Experience::create([
                'login_id' => $loginId !== '' ? $loginId : $parentExp->login_id,
                'application_id' => $childId,
                'emp_type' => $parentExp->emp_type,
                'emp_cate' => $parentExp->emp_cate,
                'org_name' => $orgName,
                'org_address' => $parentExp->org_address,
                'designation' => $designation,
                'from_date' => $parentExp->from_date,
                'to_date' => $parentExp->to_date,
                'work_to_till_date' => (int) ($parentExp->work_to_till_date ?? 0),
                'total_y' => $parentExp->total_y,
                'total_m' => $parentExp->total_m,
                'total_d' => $parentExp->total_d,
                'total_exp' => $parentExp->total_exp,
                'nature_work' => $parentExp->nature_work,
                'voltage_level' => $parentExp->voltage_level,
                'transformer_kva' => $parentExp->transformer_kva,
                'board_meeting_details' => $parentExp->board_meeting_details,
                'board_meeting_date' => $parentExp->board_meeting_date,
                'support_document' => $parentExp->support_document,
                'relieve_document' => $parentExp->relieve_document ?? $parentExp->releive_document,
            ]);
        }
    }

    public function preferredEducationApplicationId(CC_CompetencyMeta $workflow): string
    {
        $childId = (string) $workflow->application_id;
        $masterId = (string) $this->workflowService->masterApplication($workflow)->application_id;
        if ($childId !== $masterId && CC_Education::where('application_id', $childId)->exists()) {
            return $childId;
        }

        return $masterId;
    }

    public function preferredExperienceApplicationId(CC_CompetencyMeta $workflow): string
    {
        $childId = (string) $workflow->application_id;
        $masterId = (string) $this->workflowService->masterApplication($workflow)->application_id;
        if ($childId !== $masterId && CC_Experience::where('application_id', $childId)->exists()) {
            return $childId;
        }

        return $masterId;
    }

    public function preferredIdentityProofApplicationId(CC_CompetencyMeta $workflow): string
    {
        $childId = (string) $workflow->application_id;
        $masterId = (string) $this->workflowService->masterApplication($workflow)->application_id;
        if ($childId !== $masterId
            && CC_Proof_doc::where('application_id', $childId)
                ->whereIn('proof_name', self::identityProofNames())
                ->exists()
        ) {
            return $childId;
        }

        return $masterId;
    }

    public function resolveParentExperienceFromPostedId(?CC_Experience $found, string $parentId): ?CC_Experience
    {
        if (! $found) {
            return null;
        }

        if ((string) $found->application_id === $parentId) {
            return $found;
        }

        $sourceId = $this->decodeCopiedExperienceSourceId((string) ($found->board_meeting_details ?? ''));
        if ($sourceId > 0) {
            $parentExp = CC_Experience::find($sourceId);
            if ($parentExp && (string) $parentExp->application_id === $parentId) {
                return $parentExp;
            }
        }

        return CC_Experience::where('application_id', $parentId)
            ->where('org_name', $found->org_name)
            ->where('designation', $found->designation)
            ->where('from_date', $found->from_date)
            ->where('to_date', $found->to_date)
            ->first();
    }

    public function resolveParentEducationFromPostedId(?CC_Education $found, string $parentId, string $level): ?CC_Education
    {
        if ($found && (string) $found->application_id === $parentId) {
            return $found;
        }

        $query = CC_Education::where('application_id', $parentId);
        if ($found) {
            $match = (clone $query)
                ->where('educational_level', $found->educational_level)
                ->where('institute_name', $found->institute_name)
                ->first();
            if ($match) {
                return $match;
            }

            return $query->where('educational_level', $found->educational_level)->first();
        }

        $level = trim($level);
        if ($level === '') {
            return null;
        }

        return $query->where('educational_level', $level)->first();
    }

    public function decodeCopiedExperienceSourceId(string $boardDetails): int
    {
        $details = trim($boardDetails);
        $prefixes = [
            '__ALT_SRC_EXP__:',
            '__SRC_EXP__:',
        ];

        foreach ($prefixes as $prefix) {
            if ($prefix !== '' && str_starts_with($details, $prefix)) {
                $rest = substr($details, strlen($prefix));
                $line = strtok($rest, "\r\n") ?: $rest;

                return (int) $line;
            }
        }

        return 0;
    }
}
