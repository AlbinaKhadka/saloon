<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_category_id' => ['sometimes', 'exists:service_categories,id'],
            'title'               => ['sometimes', 'string', 'max:255'],
            'slug'                => ['sometimes', 'nullable', 'string', 'max:255'],
            'description'         => ['nullable', 'string'],
            'price'               => ['sometimes', 'numeric', 'min:0'],
            'duration'            => ['nullable', 'integer', 'min:1'],
            'image'               => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
            'status'              => ['required', 'in:0,1'],
            'orderby'             => ['nullable', 'integer'],
        ];
    }
}
