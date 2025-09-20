<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RenewMembershipRequest extends FormRequest
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
        return [
            'period' => ['required', 'in:1_month,6_months,12_months']
        ];
    }
}
