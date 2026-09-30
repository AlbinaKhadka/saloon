<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:255'],
            'icon'    => ['nullable', 'string', 'max:255'],
            'orderby' => ['nullable', 'integer'],
            'status'  => ['nullable', 'boolean'],
        ];
    }
}
