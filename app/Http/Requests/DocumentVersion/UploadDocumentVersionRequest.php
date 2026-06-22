<?php

namespace App\Http\Requests\DocumentVersion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadDocumentVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxKb = config('document_versioning.max_file_size_kb', 5120);
        $mimes = implode(',', config('document_versioning.allowed_mimes', ['pdf']));
        $moduleTypes = array_keys(config('document_versioning.module_types', []));
        $documentTypes = array_keys(config('document_versioning.document_types', []));

        return [
            'application_id' => ['required', 'integer', 'exists:d_applications,id'],
            'module_type' => ['required', 'string', Rule::in($moduleTypes)],
            'module_ref_id' => [
                'nullable',
                'integer',
                Rule::requiredIf(fn () => in_array($this->input('module_type'), ['education', 'experience'], true)),
            ],
            'document_type' => ['required', 'string', Rule::in($documentTypes)],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'document_file' => ['required', 'file', 'mimes:' . $mimes, 'max:' . $maxKb],
        ];
    }
}
