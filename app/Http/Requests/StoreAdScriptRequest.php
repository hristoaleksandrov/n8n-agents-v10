<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdScriptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reference_script' => ['required', 'string', 'min:10', 'max:50000'],
            'outcome_description' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }
}
