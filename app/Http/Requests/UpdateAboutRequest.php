<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAboutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->hasFile('images') && ! is_array($this->file('images'))) {
            $this->files->set('images', [$this->file('images')]);
        }
    }

    public function rules(): array
    {
        return [
            'title'            => 'nullable|string|max:255',
            'slug'             => 'nullable|string|max:255|unique:abouts,slug,' . $this->route('about')->id,
            'description'      => 'nullable|string',
            'images'           => 'nullable|array',
            'images.*'         => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'remove_images'    => 'nullable|array',
            'remove_images.*'  => 'string',
        ];
    }
}