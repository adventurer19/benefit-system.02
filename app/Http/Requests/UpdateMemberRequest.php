<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMemberRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $memberId = $this->route('member')->id ?? null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:members,email,' . $memberId],
            'phone' => ['nullable', 'string', 'max:32'],
            'card_uid' => ['required', 'string', 'max:64', 'unique:members,card_uid,' . $memberId],
            'membership_start' => ['required', 'date'],
            'membership_end' => ['required', 'date', 'after_or_equal:membership_start'],
            'membership_type' => ['nullable', 'string', 'max:32'],
        ];
    }

    /**
     * Comment
     */
    public function messages(): array{
        return [
            'membership_end.after_or_equal' => 'The membership end field must be a date after or equal to membership start.',
        ];
    }
}
