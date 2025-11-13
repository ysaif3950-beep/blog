<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        if ($this->route('user') instanceof \App\Models\User) {
    // لو اللي راجع من الراوت كائن User
    $userId = $this->route('user')->id;
} else {
    // لو اللي راجع رقم id بس
    $userId = $this->route('user');
}

        return [
            //
              'name'     => 'required|string|max:100|min:3',
            'email'    => 'required|email|unique:users,email,' . $userId,
            'password' => 'nullable|string|min:8|confirmed',
            'role'     => 'required|in:admin,user',
        ];
        }
    }
