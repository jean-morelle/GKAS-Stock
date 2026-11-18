<?php

namespace App\Http\Requests;

use App\Enums\ProductUnit;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Le stock n'est volontairement pas modifiable ici :
 * il évolue uniquement via les entrées et sorties.
 */
class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = $this->route('product');

        return $product
            ? $this->user()->can('update', $product)
            : $this->user()->can('create', Product::class);
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'reference' => ['nullable', 'string', 'max:50', Rule::unique('products')->ignore($this->route('product'))],
            'description' => ['nullable', 'string', 'max:2000'],
            'unit' => ['required', Rule::enum(ProductUnit::class)],
            'alert_threshold' => ['required', 'integer', 'min:0', 'max:1000000'],
        ];
    }
}
