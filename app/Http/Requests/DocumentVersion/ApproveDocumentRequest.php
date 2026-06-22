<?php

namespace App\Http\Requests\DocumentVersion;

use Illuminate\Foundation\Http\FormRequest;

class ApproveDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxLevel = count(config('document_versioning.approval_levels', []));

        return [
            'approval_level' => ['required', 'integer', 'min:1', 'max:' . max(1, $maxLevel)],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
