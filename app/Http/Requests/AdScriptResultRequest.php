<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdScriptResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'task_id' => ['required', 'integer'],
            'new_script' => ['required_without:error', 'string'],
            'analysis' => ['required_without:error', 'string'],
            'error' => ['sometimes', 'string'],
        ];
    }

    public function isSuccess(): bool
    {
        return ! $this->has('error');
    }
}
