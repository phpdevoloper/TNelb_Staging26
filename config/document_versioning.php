<?php

return [
    'approval_levels' => [
        1 => ['label' => 'Supervisor', 'role' => 'supervisor'],
    ],

    'document_types' => [
        'certificate' => 'Educational Certificate',
        'experience_doc' => 'Experience Certificate',
        'identity' => 'Identity Document',
        'supporting' => 'Supporting Document',
    ],

    'module_types' => [
        'application' => 'Application Level',
        'education' => 'Education Record',
        'experience' => 'Experience Record',
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
    ],

    'file_type_codes' => [
        'certificate' => 'EDU',
        'experience_doc' => 'EXP',
        'identity' => 'META',
        'supporting' => 'QSC',
    ],

    'max_file_size_kb' => 5120,
    'allowed_mimes' => ['pdf', 'jpg', 'jpeg', 'png'],
];
