<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class XtreamApiRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:255', 'alpha_dash'],
            'password' => ['required', 'string', 'max:255'],
            'action' => ['sometimes', 'string', 'in:get_live_categories,get_live_streams,get_short_epg,get_simple_data_table'],
            'category_id' => ['sometimes', 'integer', 'min:1'],
            'stream_id' => ['sometimes', 'integer', 'min:1'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'type' => ['sometimes', 'string', 'in:m3u,m3u_plus'],
            'output' => ['sometimes', 'string', 'in:ts,m3u8'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'username.required' => 'Username is required for API access.',
            'username.alpha_dash' => 'Username must contain only letters, numbers, dashes and underscores.',
            'password.required' => 'Password is required for API access.',
            'action.in' => 'Invalid action specified.',
            'limit.max' => 'Limit cannot exceed 100 items.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException(
            $validator,
            response()->json([
                'user_info' => [
                    'auth' => 0,
                    'status' => 'Error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ],
            ], 422)
        );
    }
}
