<?php

return [
    /**
     * When true, competency form uploads use documents_log + private FORM_* storage
     * instead of public education_document / work_experience / attached_documents paths.
     */
    'production_enabled' => env('DOCUMENT_VERSIONING_PRODUCTION', true),

    /** Competency form codes (tnelb_application_tbl.form_name) using versioned storage. */
    'versioned_form_codes' => ['S', 'W', 'WH'],

    'approval_levels' => [
        1 => ['label' => 'Supervisor', 'role' => 'supervisor'],
    ],

    'document_types' => [
        'certificate' => 'Educational Certificate',
        'experience_doc' => 'Experience Certificate',
        'identity' => 'Identity Document',
        'supporting' => 'Supporting Document',
        'photo' => 'Applicant Photo',
        'signature' => 'Applicant Signature',
    ],

    'module_types' => [
        'application' => 'Application Level',
        'education' => 'Education Record',
        'experience' => 'Experience Record',
        'photo' => 'Applicant Photo',
        'signature' => 'Applicant Signature',
    ],

    'disk' => 'private_documents',

    'default_certificate_folder' => 'FORM_S',

    'certificate_folders' => [
        'FORM_S' => 'FORM_S',
        'FORM_W' => 'FORM_W',
        'FORM_WH' => 'FORM_WH',
        'FORM_P' => 'FORM_P',
    ],

    'request_folders' => [
        'INITIAL' => 'NEW',
        'ALTERATION' => 'ALTERATION',
        'RENEWAL' => 'RENEWAL',
        'DIGITISATION' => 'DIGITISATION',
    ],

    'module_folders' => [
        'education' => 'EDUCATION',
        'experience' => 'EXPERIENCE',
        'application' => 'META',
    ],

    'document_folders' => [
        'certificate' => 'EDUCATION',
        'experience_doc' => 'EXPERIENCE',
        'identity' => 'META',
        'supporting' => 'QC_QSC',
        'photo' => 'PHOTO',
        'signature' => 'SIGNATURE',
    ],

    'file_type_codes' => [
        'certificate' => 'EDU',
        'experience_doc' => 'EXP',
        'identity' => 'META',
        'supporting' => 'QSC',
        'photo' => 'PHOTO',
        'signature' => 'SIGN',
    ],

    'max_file_size_kb' => 5120,
    'allowed_mimes' => ['pdf', 'jpg', 'jpeg', 'png'],
];
