<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CanvasStoreRequest extends FormRequest
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
            'width' => 'sometimes|numeric|between:1280,8192',
            'height' => 'sometimes|numeric|between:720,4320',
            'bg_color' => 'sometimes|string',
            'resolution' => 'string|required_without:width,height'
        ];
    }
}
