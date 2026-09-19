<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAboutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize "images" before validation.
     * With exactly one file uploaded under the field "images", PHP hands
     * back a single UploadedFile instead of an array, which fails the
     * "array" rule and breaks foreach in the controller. This guarantees
     * "images" is always an array — whether 1 file or many are sent.
     */
    protected function prepareForValidation(): void
    {
        if ($this->hasFile('images') && ! is_array($this->file('images'))) {
            $this->files->set('images', [$this->file('images')]);
        }
    }

    public function rules(): array
    {
        return [
            'title'       => 'nullable|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:abouts,slug',
            'description' => 'nullable|string',
            'images'      => 'required|array|min:1',
            'images.*'    => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ];
    }
}
