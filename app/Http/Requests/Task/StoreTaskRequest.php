<?php

declare(strict_types=1);

namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', [Task::class, $this->route('project')]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                'min:1',
            ],
            'description' => [
                'required',
                'string',
                'max:65000',
            ],
            'status' => [
                'required',
                Rule::enum(TaskStatus::class),
            ],
            'priority' => [
                'required',
                Rule::enum(TaskPriority::class),
            ],
            'due_date' => [
                'required',
                'date',
            ],
        ];
    }
}
