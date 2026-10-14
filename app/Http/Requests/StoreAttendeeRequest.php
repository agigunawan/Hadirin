<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Gender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreAttendeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    private ?\App\Models\Event $cachedEvent = null;

    private function getEvent(): ?\App\Models\Event
    {
        if ($this->cachedEvent === null) {
            $token = (string) $this->route('token');
            $this->cachedEvent = \App\Models\Event::where('qr_token', $token)->first();
        }

        return $this->cachedEvent;
    }

    protected function prepareForValidation(): void
    {
        $hasTransport = filter_var($this->input('has_transport'), FILTER_VALIDATE_BOOLEAN);
        $fullName = strip_tags(trim((string) $this->input('full_name')));
        $nipOrId = trim(preg_replace('/[^0-9a-zA-Z]/', '', (string) $this->input('nip_or_id')));
        $agency = strip_tags(trim((string) $this->input('agency')));
        $position = strip_tags(trim((string) $this->input('position')));
        $phone = preg_replace('/[^0-9+]/', '', (string) $this->input('phone'));
        $address = strip_tags(trim((string) $this->input('address')));
        $gender = $this->input('gender');

        $this->merge([
            'full_name' => $fullName !== '' ? $fullName : 'Peserta Hadir',
            'agency' => $agency !== '' ? $agency : null,
            'is_internal' => $this->has('is_internal') ? filter_var($this->input('is_internal'), FILTER_VALIDATE_BOOLEAN) : true,
            'nip_or_id' => $nipOrId !== '' ? $nipOrId : null,
            'gender' => $gender ?: null,
            'position' => $position !== '' ? $position : null,
            'phone' => $phone !== '' ? $phone : null,
            'address' => $address !== '' ? $address : null,
            'has_transport' => $hasTransport,
            'bank_name' => $hasTransport ? strip_tags(trim((string) $this->input('bank_name'))) : null,
            'bank_account_no' => $hasTransport ? preg_replace('/[^0-9]/', '', (string) $this->input('bank_account_no')) : null,
            'bank_account_name' => $hasTransport ? strip_tags(trim((string) $this->input('bank_account_name'))) : null,
        ]);
    }

    public function rules(): array
    {
        $event = $this->getEvent();

        $rules = [
            'is_internal' => ['nullable', 'boolean'],
            'signature_data' => ['required', 'string', 'starts_with:data:image/png;base64,'],
            'coordinates' => ['nullable', 'string', 'max:100'],
        ];

        // full_name
        if ($event && $event->isFieldActive('full_name')) {
            $rules['full_name'] = $event->isFieldRequired('full_name')
                ? ['required', 'string', 'max:150']
                : ['nullable', 'string', 'max:150'];
        } else {
            $rules['full_name'] = ['nullable', 'string', 'max:150'];
        }

        // nip_or_id
        if ($event && $event->isFieldActive('nip_or_id')) {
            $rules['nip_or_id'] = $event->isFieldRequired('nip_or_id')
                ? ['required', 'string', 'min:4', 'max:20']
                : ['nullable', 'string', 'max:20'];
        } else {
            $rules['nip_or_id'] = ['nullable', 'string', 'max:20'];
        }

        // gender
        if ($event && $event->isFieldActive('gender')) {
            $rules['gender'] = $event->isFieldRequired('gender')
                ? ['required', Rule::enum(Gender::class)]
                : ['nullable', Rule::enum(Gender::class)];
        } else {
            $rules['gender'] = ['nullable', Rule::enum(Gender::class)];
        }

        // agency
        if ($event && $event->isFieldActive('agency')) {
            $rules['agency'] = $event->isFieldRequired('agency')
                ? ['required', 'string', 'max:150']
                : ['nullable', 'string', 'max:150'];
        } else {
            $rules['agency'] = ['nullable', 'string', 'max:150'];
        }

        // position
        if ($event && $event->isFieldActive('position')) {
            $rules['position'] = $event->isFieldRequired('position')
                ? ['required', 'string', 'max:100']
                : ['nullable', 'string', 'max:100'];
        } else {
            $rules['position'] = ['nullable', 'string', 'max:100'];
        }

        // phone
        if ($event && $event->isFieldActive('phone')) {
            $rules['phone'] = $event->isFieldRequired('phone')
                ? ['required', 'string', 'max:25']
                : ['nullable', 'string', 'max:25'];
        } else {
            $rules['phone'] = ['nullable', 'string', 'max:25'];
        }

        // address
        if ($event && $event->isFieldActive('address')) {
            $rules['address'] = $event->isFieldRequired('address')
                ? ['required', 'string', 'max:255']
                : ['nullable', 'string', 'max:255'];
        } else {
            $rules['address'] = ['nullable', 'string', 'max:255'];
        }

        // transport
        if ($event && $event->isFieldActive('transport')) {
            $rules['has_transport'] = ['sometimes', 'boolean'];
            $rules['bank_name'] = ['required_if:has_transport,true', 'nullable', 'string', 'max:50'];
            $rules['bank_account_no'] = ['required_if:has_transport,true', 'nullable', 'string', 'max:30'];
            $rules['bank_account_name'] = ['required_if:has_transport,true', 'nullable', 'string', 'max:100'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Nama lengkap dan gelar kedinasan wajib diisi.',
            'agency.required' => 'Instansi / bagian asal wajib diisi.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'nip_or_id.required' => 'NIP, NIK, atau nomor identitas pegawai wajib diisi.',
            'nip_or_id.min' => 'NIP, NIK, atau nomor identitas minimal :min karakter.',
            'nip_or_id.max' => 'NIP, NIK, atau nomor identitas maksimal :max karakter.',
            'position.required' => 'Jabatan kedinasan wajib diisi.',
            'phone.required' => 'Nomor HP / WhatsApp wajib diisi untuk koordinasi kedinasan.',
            'address.required' => 'Alamat instansi/domisili wajib diisi.',
            'bank_name.required_if' => 'Nama Bank wajib diisi apabila memilih penggantian transport.',
            'bank_account_no.required_if' => 'Nomor Rekening wajib diisi apabila memilih penggantian transport.',
            'bank_account_name.required_if' => 'Nama Pemilik Rekening wajib diisi sesuai buku tabungan.',
            'signature_data.required' => 'Tanda tangan digital wajib dibubuhkan pada kanvas.',
            'signature_data.starts_with' => 'Format tanda tangan digital tidak valid (harus data image PNG).',
        ];
    }
}
