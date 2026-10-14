<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Event;
use App\Services\CollisionDetectionService;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $startTime = (string) $this->input('start_time');
        $endTime = (string) $this->input('end_time');

        $rawConfigFields = $this->input('config_fields');
        $formattedConfigFields = null;

        if (is_array($rawConfigFields)) {
            $formattedConfigFields = [];
            foreach (Event::DEFAULT_FIELDS_CONFIG as $key => $default) {
                $isWajib = false;
                if (isset($rawConfigFields[$key])) {
                    if (is_array($rawConfigFields[$key])) {
                        $isWajib = filter_var($rawConfigFields[$key]['required'] ?? $rawConfigFields[$key]['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    } else {
                        $isWajib = filter_var($rawConfigFields[$key], FILTER_VALIDATE_BOOLEAN);
                    }
                }

                $formattedConfigFields[$key] = [
                    'enabled' => true,
                    'required' => $isWajib,
                ];
            }
        }

        $mergeData = [
            'title' => strip_tags(trim((string) $this->input('title'))),
            'work_unit' => strip_tags(trim((string) $this->input('work_unit'))),
            'start_minutes' => Event::timeToMinutes($startTime),
            'end_minutes' => Event::timeToMinutes($endTime),
        ];

        if ($formattedConfigFields !== null) {
            $mergeData['config_fields'] = $formattedConfigFields;
        }

        $this->merge($mergeData);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'room_id' => ['required', 'uuid', 'exists:rooms,id'],
            'event_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'start_minutes' => ['required', 'integer', 'min:0', 'max:1439'],
            'end_minutes' => ['required', 'integer', 'gt:start_minutes', 'max:1439'],
            'work_unit' => ['required', 'string', 'max:100'],
            'config_fields' => ['nullable', 'array'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $collisionService = app(CollisionDetectionService::class);
            if ($collisionService->hasCollision(
                roomId: (string) $this->input('room_id'),
                eventDate: (string) $this->input('event_date'),
                startMinutes: (int) $this->input('start_minutes'),
                endMinutes: (int) $this->input('end_minutes'),
                ignoreEventId: $this->route('event')?->id
            )) {
                $validator->errors()->add('room_id', 'Ruang rapat terpilih telah terisi pada tanggal dan rentang jam tersebut. Silakan pilih ruang atau jam lain.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Nama kegiatan / rapat wajib diisi.',
            'room_id.required' => 'Pilihan ruang rapat wajib ditentukan.',
            'event_date.required' => 'Tanggal pelaksanaan kegiatan wajib ditentukan.',
            'start_time.required' => 'Jam mulai kegiatan wajib diisi.',
            'end_time.required' => 'Jam selesai kegiatan wajib diisi.',
            'end_time.after' => 'Jam selesai kegiatan harus lebih akhir dari jam mulai kegiatan.',
            'work_unit.required' => 'Tim kerja / seksi penyelenggara wajib diisi.',
        ];
    }
}
