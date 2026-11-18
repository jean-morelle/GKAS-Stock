<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class TechnicianRequest extends FormRequest
{
    public function authorize(): bool
    {
        $technician = $this->route('technician');

        return $technician
            ? $this->user()->can('update', $technician)
            : $this->user()->can('create', User::class);
    }

    public function rules(): array
    {
        $technician = $this->route('technician');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($technician)],
            // Obligatoire à la création, facultatif en modification (laisser vide = inchangé).
            'password' => [$technician ? 'nullable' : 'required', 'confirmed', Password::defaults()],
        ];
    }
}
