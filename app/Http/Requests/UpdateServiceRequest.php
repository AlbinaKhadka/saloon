<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $serviceId = $this->route('service'); // or $this->route('id') depending on route

        return [
            'title'       => ['sometimes', 'string', 'max:255'],
            'slug'        => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('services', 'slug')->ignore($serviceId),
            ],
            'description' => ['nullable', 'string'],
            'price'       => ['sometimes', 'numeric', 'min:0'],
            'duration'    => ['nullable', 'integer', 'min:1'],
            'image'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
            'status'      => ['required', 'in:0,1'],
            'orderby'     => ['nullable', 'integer'],
        ];
    }
}
