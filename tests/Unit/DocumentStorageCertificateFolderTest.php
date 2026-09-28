<?php

namespace Tests\Unit;

use App\Enums\DocumentRequestType;
use App\Services\DocumentVersion\DocumentStorageService;
use Tests\TestCase;

class DocumentStorageCertificateFolderTest extends TestCase
{
    public function test_alteration_application_ids_use_form_w_and_wh_folders(): void
    {
        $storage = app(DocumentStorageService::class);

        $formW = $storage->buildRelativePath(
            DocumentRequestType::ALTERATION,
            'AWB261111234',
            'alteration',
            'name_proof',
            1,
            'ALTERATION',
            'pdf'
        );
        $this->assertStringStartsWith('FORM_W/ALTERATION/PROOF/', $formW);

        $formWh = $storage->buildRelativePath(
            DocumentRequestType::ALTERATION,
            'AWHH261111234',
            'alteration',
            'address_proof',
            1,
            'ALTERATION',
            'pdf'
        );
        $this->assertStringStartsWith('FORM_WH/ALTERATION/PROOF/', $formWh);

        $formS = $storage->buildRelativePath(
            DocumentRequestType::ALTERATION,
            'ASC261111234',
            'alteration',
            'name_proof',
            1,
            'ALTERATION',
            'pdf'
        );
        $this->assertStringStartsWith('FORM_S/ALTERATION/PROOF/', $formS);
    }

    public function test_new_renewal_and_digitisation_ids_keep_matching_form_folders(): void
    {
        $storage = app(DocumentStorageService::class);

        $newW = $storage->buildRelativePath(
            DocumentRequestType::INITIAL,
            'WB261111234',
            'education',
            'certificate',
            1,
            'NEW',
            'pdf'
        );
        $this->assertStringStartsWith('FORM_W/NEW/EDUCATION/', $newW);

        $renewalW = $storage->buildRelativePath(
            DocumentRequestType::RENEWAL,
            'RWB261111234',
            'education',
            'certificate',
            1,
            'RENEWAL',
            'pdf'
        );
        $this->assertStringStartsWith('FORM_W/RENEWAL/EDUCATION/', $renewalW);

        $digitisationWh = $storage->buildRelativePath(
            DocumentRequestType::INITIAL,
            'DWHH261111234',
            'education',
            'certificate',
            1,
            'DIGITISATION',
            'pdf'
        );
        $this->assertStringStartsWith('FORM_WH/DIGITISATION/EDUCATION/', $digitisationWh);
    }
}
