<?php

namespace App\Services\FormS;

use App\Models\CC_Forms_Meta;
use App\Models\Competency\CC_CompetencyMeta;
use App\Services\Competency\CompetencyMetaService;

class FormSApplicationWorkflowService
{
    /** Marker on child experience rows copied from a parent exp_id (path-only, not a new entry). */
    public const COPIED_EXP_SRC_PREFIX = '__SRC_EXP__:';

    public function isDigitisationApplication(CC_CompetencyMeta $application): bool
    {
        return strtoupper((string) ($application->appl_type ?? '')) === 'D';
    }

    public function isRenewalApplication(CC_CompetencyMeta $application): bool
    {
        return strtoupper((string) ($application->appl_type ?? '')) === 'R';
    }

    public function isAlterationApplication(CC_CompetencyMeta $application): bool
    {
        return strtoupper((string) ($application->appl_type ?? '')) === 'A';
    }

    public function isChildWorkflow(CC_CompetencyMeta $application): bool
    {
        return $this->isRenewalApplication($application) || $this->isAlterationApplication($application);
    }

    /**
     * Master application row — education/experience data lives on the parent APP.
     */
    public function masterApplication(CC_CompetencyMeta $application): CC_CompetencyMeta
    {
        if ($this->isChildWorkflow($application) && ! empty($application->old_application)) {
            $parent = CC_Forms_Meta::findByApplicationId((string) $application->old_application);
            if ($parent) {
                return $parent;
            }
        }

        return $application;
    }

    public function workflowStage(CC_CompetencyMeta $application): string
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

    public function workflowPk(CC_CompetencyMeta $application): int
    {
        return (int) $application->getKey();
    }

    public function parentApplicationPk(CC_CompetencyMeta $workflowApplication): ?int
    {
        if (! $this->isChildWorkflow($workflowApplication) || empty($workflowApplication->old_application)) {
            return null;
        }

        $parent = CC_Forms_Meta::findByApplicationId((string) $workflowApplication->old_application);

        return $parent ? (int) $parent->app_id : null;
    }

    public function documentsLogParentApplicationId(CC_CompetencyMeta $workflowApplication): ?int
    {
        $parentPk = $this->parentApplicationPk($workflowApplication);
        if ($parentPk === null) {
            return null;
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('d_applications')
            && ! \App\Models\DApplication::whereKey($parentPk)->exists()) {
            return null;
        }

        return $parentPk;
    }

    public function findWorkflowByPk(int $pk): ?CC_CompetencyMeta
    {
        return app(CompetencyMetaService::class)->findModelByAppId($pk);
    }
}
