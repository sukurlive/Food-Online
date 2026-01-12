<?php

namespace App\Http\Requests\Api\Customer;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCustomerRequest extends FormRequest
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
        $customerId = $this->route('customer');

        return [
            'name'  => 'sometimes|string|max:100',
            'phone' => 'sometimes|string|max:20|unique:customers,phone,' . $customerId . ',customer_id',
            'email' => 'sometimes|email|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.string'   => 'Nama harus berupa teks.',
            'name.max'      => 'Nama maksimal 100 karakter.',
            'phone.string'  => 'Nomor telepon harus berupa teks.',
            'phone.unique'  => 'Nomor telepon sudah digunakan oleh pelanggan lain.',
            'email.email'   => 'Format email tidak valid.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'status'  => 'gagal',
                'message' => 'Validation Error',
                'errors'  => $validator->errors()
            ], 422)
        );
    }
}
