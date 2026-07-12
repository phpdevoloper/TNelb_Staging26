<?php

namespace App\Services\FormS;

use App\Models\CC_Forms_Meta;

class FormSApplicationWorkflowService
{
    public function isDigitisationApplication(CC_Forms_Meta $application): bool
    {
        return strtoupper((string) ($application->appl_type ?? '')) === 'D';
    }

    public function isRenewalApplication(CC_Forms_Meta $application): bool
    {
        return strtoupper((string) ($application->appl_type ?? '')) === 'R';
    }

    public function isAlterationApplication(CC_Forms_Meta $application): bool
    {
        return strtoupper((string) ($application->appl_type ?? '')) === 'A';
    }

    public function isChildWorkflow(CC_Forms_Meta $application): bool
    {
        return $this->isRenewalApplication($application) || $this->isAlterationApplication($application);
    }

    /**
     * Master application row — education/experience data lives on the parent APP.
     */
    public function masterApplication(CC_Forms_Meta $application): CC_Forms_Meta
    {
        if ($this->isChildWorkflow($application) && !empty($application->old_application)) {
            $parent = CC_Forms_Meta::where('application_id', $application->old_application)->first();
            if ($parent) {
                return $parent;
            }
        }

        return $application;
    }

    public function workflowStage(CC_Forms_Meta $application): string
    {
        if ($this->isRenewalApplication($application)) {
            return 'RENEWAL';
        }
        if ($this->isDigitisationApplication($application)) {
            return 'DIGITISATION';
        }
        if ($this->isAlterationApplication($application)) {
            return 'ALTERATION';
        }

        return 'NEW';
    }

    public function workflowPk(CC_Forms_Meta $application): int
    {
        return (int) $application->getKey();
    }

    public function parentApplicationPk(CC_Forms_Meta $workflowApplication): ?int
    {
        if (!$this->isChildWorkflow($workflowApplication) || empty($workflowApplication->old_application)) {
            return null;
        }

        return CC_Forms_Meta::where('application_id', $workflowApplication->old_application)
            ->value('app_id');
    }

    public function documentsLogParentApplicationId(CC_Forms_Meta $workflowApplication): ?int
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

    public function findWorkflowByPk(int $pk): ?CC_Forms_Meta
    {
        return CC_Forms_Meta::find($pk);
    }
}
