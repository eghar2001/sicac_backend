<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', \Illuminate\Validation\Rules\Password::defaults()],
            'role' => ['sometimes', 'string', \Illuminate\Validation\Rule::in(config('app.accepted_roles', []))],
            'address' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'city' => ['required', 'string'],
            'dni'      => 'required|string|unique:users',
        ];
    }
}
