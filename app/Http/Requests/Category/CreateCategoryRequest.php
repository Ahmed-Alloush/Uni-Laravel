<?php

namespace App\Http\Requests\Category;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class CreateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(Request $request)
    {
        
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:15|unique:categories,name',
        ];
    }
}
