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
        'relieving_doc' => 'Relieving Letter',
        'identity' => 'Identity Document',
        'supporting' => 'Supporting Document',
        'photo' => 'Applicant Photo',
        'signature' => 'Applicant Signature',
        'aadhaar_doc' => 'Aadhaar Document',
        'pancard_doc' => 'PAN Document',
        'name_proof' => 'Name Change Supporting Proof',
        'address_proof' => 'Address Supporting Proof',
    ],

    'module_types' => [
        'application' => 'Application Level',
        'education' => 'Education Record',
        'experience' => 'Experience Record',
        'photo' => 'Applicant Photo',
        'signature' => 'Applicant Signature',
        'aadhaar' => 'Aadhaar Document',
        'pan' => 'PAN Document',
        'alteration' => 'Alteration Supporting Proof',
    ],

    'disk' => 'private_documents',

    /**
     * Physical document root.
     * - Empty → {project}/competency
     * - Absolute → used as-is (/data/docs or D:\docs)
     * - Relative → from project root (e.g. competency → {project}/competency/FORM_S/...)
     *
     * Change anytime via DOCUMENT_STORAGE_ROOT in .env.
     */
    'storage_root' => (static function (): string {
        $root = env('DOCUMENT_STORAGE_ROOT');
        if ($root === null || trim((string) $root) === '') {
            return base_path('competency');
        }

        $root = trim(str_replace(['\\'], ['/'], (string) $root));

        // Absolute: Unix (/...) or Windows (D:/... or D:\...)
        if (str_starts_with($root, '/') || preg_match('/^[A-Za-z]:[\/\\\\]/', $root) === 1) {
            return str_replace('/', DIRECTORY_SEPARATOR, $root);
        }

        return base_path($root);
    })(),

    /**
     * Public URL path segment before stored relative path (FORM_S/...).
     * Browser: {DOCUMENT_PUBLIC_BASE_URL}/{prefix}/FORM_S/NEW/EDUCATION/file.pdf
     * Change via DOCUMENT_PUBLIC_URL_PREFIX in .env.
     */
    'public_url_prefix' => env('DOCUMENT_PUBLIC_URL_PREFIX', 'competency'),

    /**
     * Optional absolute origin for document view links.
     * Empty → relative URLs (/competency/FORM_S/...).
     * Set on staging/prod if needed, e.g. https://lnxstgweb.tn.gov.in
     * Change via DOCUMENT_PUBLIC_BASE_URL in .env.
     */
    'public_base_url' => env('DOCUMENT_PUBLIC_BASE_URL'),

    /**
     * When true, Laravel registers GET /{prefix}/FORM_* to stream files from storage_root.
     * Production with nginx/Apache Alias: set DOCUMENT_SERVE_VIA_LARAVEL=false.
     */
    'serve_via_laravel' => env('DOCUMENT_SERVE_VIA_LARAVEL', true),

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
        'aadhaar' => 'PROOF',
        'pan' => 'PROOF',
        'photo' => 'PHOTO',
        'signature' => 'SIGNATURE',
        'alteration' => 'PROOF',
    ],

    'document_folders' => [
        'certificate' => 'EDUCATION',
        'experience_doc' => 'EXPERIENCE',
        'relieving_doc' => 'EXPERIENCE',
        'identity' => 'META',
        'supporting' => 'QC_QSC',
        'photo' => 'PHOTO',
        'signature' => 'SIGNATURE',
        'aadhaar_doc' => 'PROOF',
        'pancard_doc' => 'PROOF',
        'name_proof' => 'PROOF',
        'address_proof' => 'PROOF',
    ],

    'file_type_codes' => [
        'certificate' => 'EDU',
        'experience_doc' => 'EXP',
        'relieving_doc' => 'RLV',
        'identity' => 'META',
        'supporting' => 'QSC',
        'photo' => 'PHOTO',
        'signature' => 'SIGN',
        'aadhaar_doc' => 'AADH',
        'pancard_doc' => 'PAN',
        'name_proof' => 'NMCH',
        'address_proof' => 'ADDR',
    ],

    'max_file_size_kb' => 5120,
    'allowed_mimes' => ['pdf', 'jpg', 'jpeg', 'png'],
];
