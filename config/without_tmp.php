<?php

return [
    'disk' => 'without_tmp',

    'root_folder' => 'without_tmp',

    'max_file_size_kb' => 5120,

    'allowed_mimes' => ['pdf', 'jpg', 'jpeg', 'png'],

    'upload_types' => [
        'EDU' => ['folder' => 'Education', 'label' => 'Education Certificate'],
        'EXP' => ['folder' => 'Experience', 'label' => 'Experience Document'],
        'PHOTO' => ['folder' => 'Photo', 'label' => 'Photo'],
        'SIGN' => ['folder' => 'Signature', 'label' => 'Signature'],
        'DOC' => ['folder' => 'Documents', 'label' => 'Supporting Document'],
    ],

    'target_tables' => [
        'c_education' => 'EDU',
        'c_experience' => 'EXP',
        'c_photo' => 'PHOTO',
        'c_signature' => 'SIGN',
        'c_documents' => 'DOC',
    ],
];
