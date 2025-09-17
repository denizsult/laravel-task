<?php

namespace App\Http\Requests;

 

class StoreCommentRequest extends ApiRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => 'required|string|max:2000',
        ];
    }
}
