<?php

namespace App\Http\Requests\Api\Customer;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateCustomerRequest extends FormRequest
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
        return [
            'name'  => 'required|string|max:100',
            'phone' => 'required|string|max:20|unique:customers,phone',
            'email' => 'required|email|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required'  => 'Nama pelanggan wajib diisi.',
            'name.max'       => 'Nama pelanggan maksimal 100 karakter.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.unique'   => 'Nomor telepon sudah terdaftar.',
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
        ];
    }
}
