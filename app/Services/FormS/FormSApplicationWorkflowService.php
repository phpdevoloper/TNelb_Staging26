<?php

namespace App\Services\FormS;

use App\Models\Mst_Form_s_w;

class FormSApplicationWorkflowService
{
    public function isRenewalApplication(Mst_Form_s_w $application): bool
    {
        return strtoupper((string) ($application->appl_type ?? '')) === 'R';
    }

    public function isAlterationApplication(Mst_Form_s_w $application): bool
    {
        return strtoupper((string) ($application->appl_type ?? '')) === 'A';
    }

    public function isChildWorkflow(Mst_Form_s_w $application): bool
    {
        return $this->isRenewalApplication($application) || $this->isAlterationApplication($application);
    }

    /**
     * Master application row — education/experience data lives on the parent APP.
     */
    public function masterApplication(Mst_Form_s_w $application): Mst_Form_s_w
    {
        if ($this->isChildWorkflow($application) && !empty($application->old_application)) {
            $parent = Mst_Form_s_w::where('application_id', $application->old_application)->first();
            if ($parent) {
                return $parent;
            }
        }

        return $application;
    }

    public function workflowStage(Mst_Form_s_w $application): string
    {
        if ($this->isRenewalApplication($application)) {
            return 'RENEWAL';
        }
        if ($this->isAlterationApplication($application)) {
            return 'ALTERATION';
        }

        return 'NEW';
    }

    public function parentApplicationPk(Mst_Form_s_w $workflowApplication): ?int
    {
        if (!$this->isChildWorkflow($workflowApplication) || empty($workflowApplication->old_application)) {
            return null;
        }

        return Mst_Form_s_w::where('application_id', $workflowApplication->old_application)->value('id');
    }

    /**
     * documents_log.parent_application_id FK may reference d_applications (sample module),
     * while Form S workflow uses tnelb_application_tbl PKs — return null when not valid.
     */
    public function documentsLogParentApplicationId(Mst_Form_s_w $workflowApplication): ?int
    {
        $parentPk = $this->parentApplicationPk($workflowApplication);
        if ($parentPk === null) {
            return null;
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('d_applications')
            && !\App\Models\DApplication::whereKey($parentPk)->exists()) {
            return null;
        }

        return $parentPk;
    }
}
