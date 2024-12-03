<?php


namespace App\Http\Requests\Product;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class CreateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
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
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'price' => 'required|numeric|min:0',
            'available_numbers' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
              
            // 'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // to remember this in case always when i spend hours on it 
            
        ];
    }
}
