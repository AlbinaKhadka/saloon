<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStylistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $stylistParam = $this->route('stylist');
        $stylistId = is_object($stylistParam) ? $stylistParam->id : $stylistParam;

        return [
            'name'             => 'nullable|string|max:255',
            'designation'      => 'nullable|string|max:255',
            'bio'              => 'nullable|string',
            'photo'            => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'phone'            => 'nullable|string|max:30',
            'email'            => 'nullable|email|max:255',
            'experience_years' => 'nullable|integer|min:0',
            'social_links'     => 'nullable|array',
            'status'           => 'required|in:0,1',
            'orderby'          => 'nullable|integer',
            'service_ids'      => 'nullable|array',
            'service_ids.*'    => 'integer|exists:services,id',
        ];
    }
}
