<?php

namespace App\Http\Requests\DocumentVersion;

use Illuminate\Foundation\Http\FormRequest;

class ResetDocumentModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'confirm_phrase' => ['required', 'string', 'in:DELETE ALL'],
            'confirm_check' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'confirm_phrase.in' => 'Type DELETE ALL exactly to confirm.',
            'confirm_check.accepted' => 'You must confirm that you understand this action is permanent.',
        ];
    }
}
