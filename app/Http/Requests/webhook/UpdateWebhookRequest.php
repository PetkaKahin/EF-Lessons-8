<?php

namespace App\Http\Requests\webhook;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('webhook'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'url' => [
                'string',
                'url:http,https',
                'max:255',
            ],
            'enabled' => [
                'boolean',
            ],
            'secret' => [
                'string',
                'max:255',
            ],
        ];
    }
}
