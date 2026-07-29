<?php

namespace App\Http\Requests;

use App\Rules\PhoneNumber;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\pM\s\'\-\.]+$/u'],
            'phone' => ['required', 'string', 'max:25', new PhoneNumber],
            'email' => ['required', 'email:rfc', 'max:255'],
            'comment' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Имя обязательно.',
            'name.regex' => 'Имя содержит недопустимые символы.',
            'phone.required' => 'Телефон обязателен.',
            'phone.max' => 'Телефон слишком длинный.',
            'email.required' => 'Email обязателен.',
            'email.email' => 'Некорректный email.',
            'comment.required' => 'Комментарий обязателен.',
            'comment.min' => 'Комментарий должен содержать минимум 10 символов.',
            'comment.max' => 'Комментарий слишком длинный (макс. 2000 символов).',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
            'phone' => is_string($this->phone) ? trim($this->phone) : $this->phone,
            'email' => is_string($this->email) ? trim($this->email) : $this->email,
            'comment' => is_string($this->comment) ? trim($this->comment) : $this->comment,
        ]);
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'error' => 'validation_error',
            'errors' => $validator->errors(),
        ], 422));
    }
}
