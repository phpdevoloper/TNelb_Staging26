<?php

namespace App\Http\Requests\DocumentVersion;

use Illuminate\Foundation\Http\FormRequest;

class DeleteCcInspectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'application_id' => ['required', 'string', 'max:50'],
            'confirm_check' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'confirm_check.accepted' => 'Confirm that this delete cannot be undone.',
        ];
    }
}
