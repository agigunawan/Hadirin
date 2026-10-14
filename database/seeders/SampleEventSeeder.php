<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class SampleEventSeeder extends Seeder
{
    public function run(): void
    {
        $room = Room::first();
        $creator = User::where('role', 'ADMIN_TIM_KERJA')->first() ?? User::first();

        if ($room && $creator) {
            Event::firstOrCreate(
                ['title' => 'Rapat Koordinasi Kesiapsiagaan Kekarantinaan Kesehatan Pelabuhan'],
                [
                    'room_id' => $room->id,
                    'event_date' => Carbon::today(),
                    'start_minutes' => 540,  // 09:00
                    'end_minutes' => 720,    // 12:00
                    'work_unit' => 'Tim Kerja Kekarantinaan & Surveilans Epidemiologi',
                    'status' => EventStatus::APPROVED,
                    'qr_token' => 'bbkk-priok-demo-2026',
                    'created_by' => $creator->id,
                ]
            );

            Event::firstOrCreate(
                ['qr_token' => '5393ca2d-7aaf-4394-b555-1d115e066fd4'],
                [
                    'id' => 'a2f7f7cf-86d3-42eb-90aa-2253163a3219',
                    'title' => 'Rapat PNBP',
                    'room_id' => $room->id,
                    'event_date' => Carbon::tomorrow(),
                    'start_minutes' => 540,
                    'end_minutes' => 720,
                    'work_unit' => 'Tim Kerja Kekarantinaan & Surveilans Epidemiologi',
                    'status' => EventStatus::APPROVED,
                    'created_by' => $creator->id,
                ]
            );
        }
    }
}
