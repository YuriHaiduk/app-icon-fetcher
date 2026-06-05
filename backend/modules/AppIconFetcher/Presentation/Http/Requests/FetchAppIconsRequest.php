<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class FetchAppIconsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'input' => ['required', 'string', 'max:2048'],
        ];
    }

    public function appInput(): string
    {
        return (string) $this->validated('input');
    }
}
