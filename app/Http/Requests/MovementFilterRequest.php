<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Filtres de l'historique des mouvements (requête GET).
 */
class MovementFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isResponsable();
    }

    public function rules(): array
    {
        return [
            'type' => ['nullable', 'in:entry,exit'],
            'product_id' => ['nullable', 'integer'],
            'technician_id' => ['nullable', 'integer'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }
}
