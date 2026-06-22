<?php

declare(strict_types=1);

namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'string',
                Rule::enum(TaskStatus::class),
            ],
            'priority' => [
                'string',
                Rule::enum(TaskPriority::class),
            ],
            'due_date_from' => [
                'date',
            ],
            'due_date_to' => [
                'date',
                'after_or_equal:due_date_from',
            ],
            'search' => [
                'string',
                'max:255',
            ],
        ];
    }
}
