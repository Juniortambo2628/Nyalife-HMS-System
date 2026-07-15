<?php

namespace App\Http\Requests;

class UpdateBlogRequest extends StoreBlogRequest
{
    public function rules(): array
    {
        return array_map(fn ($rule) => str_replace('required', 'sometimes', $rule), parent::rules());
    }
}
