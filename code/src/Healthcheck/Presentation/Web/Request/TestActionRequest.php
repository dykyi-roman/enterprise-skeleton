<?php

declare(strict_types=1);

namespace App\Healthcheck\Presentation\Web\Request;

use Illuminate\Foundation\Http\FormRequest;

final class TestActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
