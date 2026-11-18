<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\StockExit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Une sortie peut contenir plusieurs produits (lines[i][product_id], lines[i][quantity])
 * pour une même intervention.
 *
 * Le contrôle « stock suffisant » est fait par StockService,
 * sous verrou, au moment de l'enregistrement.
 */
class StockExitRequest extends FormRequest
{
    public const MAX_LINES = 30;

    public function authorize(): bool
    {
        return $this->user()->can('create', StockExit::class);
    }

    /**
     * Un technicien ne peut sortir du matériel que pour lui-même :
     * la valeur envoyée par le formulaire est ignorée.
     */
    protected function prepareForValidation(): void
    {
        if ($this->user()->isTechnicien()) {
            $this->merge(['technician_id' => $this->user()->id]);
        }
    }

    public function rules(): array
    {
        return [
            'technician_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('role', UserRole::Technicien->value),
            ],
            'lines' => ['required', 'array', 'min:1', 'max:'.self::MAX_LINES],
            'lines.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'lines.*.quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'reason' => ['required', 'string', 'max:255'],
            'intervention' => ['nullable', 'string', 'max:255'],
            'observation' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'lines.required' => 'Ajoutez au moins un produit.',
            'lines.max' => 'Une sortie est limitée à '.self::MAX_LINES.' produits.',
            'lines.*.product_id.required' => 'Choisissez un produit.',
            'lines.*.product_id.distinct' => 'Ce produit est déjà dans la liste.',
            'lines.*.quantity.required' => 'Indiquez la quantité.',
            'lines.*.quantity.integer' => 'La quantité doit être un nombre entier.',
            'lines.*.quantity.min' => 'La quantité doit être strictement supérieure à zéro.',
        ];
    }
}
