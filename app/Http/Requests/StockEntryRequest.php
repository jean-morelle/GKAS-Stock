<?php

namespace App\Http\Requests;

use App\Models\StockEntry;
use Illuminate\Foundation\Http\FormRequest;

class StockEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', StockEntry::class);
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'reason' => ['nullable', 'string', 'max:255'],
            'observation' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.min' => 'La quantité doit être strictement supérieure à zéro.',
        ];
    }
}
