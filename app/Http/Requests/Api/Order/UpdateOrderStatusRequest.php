<?php

namespace App\Http\Requests\Api\Order;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateOrderStatusRequest extends FormRequest
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
            'status' => 'required|in:pending,paid,delivered,canceled'
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status order wajib diisi.',
            'status.in'       => 'Status order tidak valid.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'status'  => "Gagal",
                'message' => 'Validation Error',
                'errors'  => $validator->errors()
            ], 422)
        );
    }
}
