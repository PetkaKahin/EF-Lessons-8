<?php

namespace App\Http\Requests\Comment;

use App\Models\Comment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', [Comment::class, $this->route('project'), $this->route('comment')]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'body' => [
                'required', // пока одно поле, чтобы пустой update не делать
                'string',
                'max:65000',
                'min:1',
            ],
        ];
    }
}
