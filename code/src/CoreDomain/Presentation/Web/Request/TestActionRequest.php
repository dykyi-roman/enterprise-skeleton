<?php

declare(strict_types=1);

namespace App\CoreDomain\Presentation\Web\Request;

use Illuminate\Foundation\Http\FormRequest;

final class TestActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'message' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
