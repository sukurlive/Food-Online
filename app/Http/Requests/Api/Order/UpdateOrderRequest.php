<?php

namespace App\Http\Requests\Api\Order;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateOrderRequest extends FormRequest
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
            'customer_id' => 'sometimes|exists:customers,customer_id',
            'order_total' => 'sometimes|numeric|min:0',
            'status'      => 'sometimes|in:pending,paid,delivered,canceled'
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.exists'    => 'Customer tidak ditemukan.',
            'order_total.numeric'   => 'Total order harus berupa angka.',
            'order_total.min'       => 'Total order minimal 0.',
            'status.in'             => 'Status order tidak valid.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'status'   => 'Gagal',
                'message'  => 'Validation Error',
                'errors'   => $validator->errors()
            ], 422)
        );
    }
}
