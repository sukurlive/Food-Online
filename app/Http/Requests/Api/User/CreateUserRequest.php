<?php

namespace App\Http\Requests\Api\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users|max:255',
            'phone'     => 'required|string|max:20|unique:users',
            'password'  => 'required|string|min:8|confirmed',
            'role'      => [
                'nullable',
                'string',
                Rule::in(['user']),
            ],
        ];

        return $rules;
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'success'   => false,
                'errors'    => $validator->errors()
            ], 422)
        );
    }
}
