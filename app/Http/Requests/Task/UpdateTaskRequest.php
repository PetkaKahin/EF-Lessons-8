<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', [Task::class, $this->route('project')]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => [
                'string',
                'max:255',
                'min:1',
            ],
            'description' => [
                'string',
                'max:65000',
            ],
            'status' => [
                Rule::enum(TaskStatus::class),
            ],
            'priority' => [
                Rule::enum(TaskPriority::class),
            ],
            'due_date' => [
                'date',
            ],
        ];
    }
}
