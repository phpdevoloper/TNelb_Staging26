<?php

namespace App\Services\FormS;

use App\Models\CC_Forms_cert;
use App\Services\Competency\CompetencyCertificateService;

/**
 * @deprecated Use {@see CompetencyCertificateService} directly.
 */
class FormSCertificateService
{
    public function __construct(
        private readonly CompetencyCertificateService $certificates
    ) {
    }

    public function findByApplicationId(string $applicationId): ?CC_Forms_cert
    {
        $cert = $this->certificates->findByApplicationId($applicationId, 'S');

        return $cert instanceof CC_Forms_cert ? $cert : null;
    }

    public function asLicenseDetails(string $applicationId): ?object
    {
        return $this->certificates->asLicenseDetails($applicationId, 'S');
    }

    public function asWorkflowLicense(string $applicationId): ?object
    {
        return $this->certificates->asWorkflowLicense($applicationId, 'S');
    }
}
