<?php

namespace App\Http\Requests\River;

use App\Models\River;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreRiverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'city' => trim((string) $this->input('city')),
            'state' => Str::upper(trim((string) $this->input('state'))),
            'difficulty_class' => $this->filled('difficulty_class')
                ? trim((string) $this->input('difficulty_class'))
                : null,
            'description' => $this->filled('description')
                ? trim((string) $this->input('description'))
                : null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', 'string', 'size:2'],
            'difficulty_class' => ['nullable', 'string', Rule::in(River::DIFFICULTY_CLASSES)],
            'description' => ['nullable', 'string', 'max:1200'],
            'extension_km' => ['required', 'numeric', 'gt:0', 'max:10000'],
            'start_latitude' => ['required', 'numeric', 'between:-90,90'],
            'start_longitude' => ['required', 'numeric', 'between:-180,180'],
            'end_latitude' => ['required', 'numeric', 'between:-90,90'],
            'end_longitude' => ['required', 'numeric', 'between:-180,180'],
            'route_coordinates' => ['required', 'array', 'min:2', 'max:10000'],
            'route_coordinates.*' => ['required', 'array', 'size:2'],
            'route_coordinates.*.0' => ['required', 'numeric', 'between:-180,180'],
            'route_coordinates.*.1' => ['required', 'numeric', 'between:-90,90'],
        ];
    }

    /**
     * Get the "after" validation callables for the request.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $startLatitude = $this->input('start_latitude');
                $startLongitude = $this->input('start_longitude');
                $endLatitude = $this->input('end_latitude');
                $endLongitude = $this->input('end_longitude');

                if (
                    ! is_numeric($startLatitude)
                    || ! is_numeric($startLongitude)
                    || ! is_numeric($endLatitude)
                    || ! is_numeric($endLongitude)
                ) {
                    return;
                }

                if (
                    (float) $startLatitude === (float) $endLatitude
                    && (float) $startLongitude === (float) $endLongitude
                ) {
                    $validator->errors()->add(
                        'end_latitude',
                        'O ponto de saida precisa ser diferente do ponto de entrada.',
                    );
                }

                $routeCoordinates = $this->input('route_coordinates');

                if (! is_array($routeCoordinates) || count($routeCoordinates) < 2) {
                    return;
                }

                $firstCoordinate = $routeCoordinates[0] ?? null;
                $lastCoordinate = $routeCoordinates[array_key_last($routeCoordinates)] ?? null;

                if (
                    ! $this->matchesCoordinate($firstCoordinate, (float) $startLongitude, (float) $startLatitude)
                    || ! $this->matchesCoordinate($lastCoordinate, (float) $endLongitude, (float) $endLatitude)
                ) {
                    $validator->errors()->add(
                        'route_coordinates',
                        'O percurso precisa comecar na entrada e terminar na saida selecionadas.',
                    );
                }
            },
        ];
    }

    private function matchesCoordinate(mixed $coordinate, float $longitude, float $latitude): bool
    {
        return is_array($coordinate)
            && isset($coordinate[0], $coordinate[1])
            && is_numeric($coordinate[0])
            && is_numeric($coordinate[1])
            && abs((float) $coordinate[0] - $longitude) < 0.000001
            && abs((float) $coordinate[1] - $latitude) < 0.000001;
    }
}
