<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\IkmRating;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'feedback' => strip_tags(trim((string) $this->input('feedback'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', Rule::enum(IkmRating::class)],
            'feedback' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'Silakan pilih salah satu penilaian kepuasan layanan.',
        ];
    }
}
