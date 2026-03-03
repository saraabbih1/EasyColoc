<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999.99'],
            'expense_date' => ['required', 'date', 'before_or_equal:today'],
            'category_id' => ['required', 'exists:categories,id'],
        ];
    }
}
