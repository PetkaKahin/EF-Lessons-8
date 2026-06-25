<?php

namespace App\Http\Requests\webhook;

use App\Models\Webhook;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', [Webhook::class, $this->route('project')]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'url' => [
                'required',
                'url:http,https',
                'max:255',
            ],
            'enabled' => [
                'required',
                'boolean',
            ],
            'secret' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }
}
