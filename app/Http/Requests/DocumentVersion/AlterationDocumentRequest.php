<?php

namespace App\Http\Requests\DocumentVersion;

use Illuminate\Foundation\Http\FormRequest;

class AlterationDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxKb = config('document_versioning.max_file_size_kb', 5120);
        $mimes = implode(',', config('document_versioning.allowed_mimes', ['pdf']));

        return [
            'alteration_reason' => ['required', 'string', 'min:10', 'max:1000'],
            'document_file' => ['required', 'file', 'mimes:' . $mimes, 'max:' . $maxKb],
        ];
    }

    public function messages(): array
    {
        return [
            'alteration_reason.required' => 'Please provide a reason for this document alteration.',
            'alteration_reason.min' => 'Alteration reason must be at least 10 characters.',
        ];
    }
}
