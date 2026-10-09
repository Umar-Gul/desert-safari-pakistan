<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\RoleName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-users');
    }

    public function rules(): array
    {
        $editing = $this->route('user') !== null;

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'role' => ['required', Rule::in(RoleName::values())],
            'password' => [$editing ? 'nullable' : 'required', 'confirmed', Password::defaults()],
        ];
    }
}