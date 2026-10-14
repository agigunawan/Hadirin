<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

final class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = [
            ['name' => 'Ruang Rapat Utama (Lantai 2)', 'capacity' => 40],
            ['name' => 'Ruang Rapat Malahayati', 'capacity' => 25],
            ['name' => 'Ruang Rapat Samudera', 'capacity' => 20],
            ['name' => 'Aula Serbaguna Gedung BBKK Priok', 'capacity' => 120],
            ['name' => 'Ruang Rapat Tim Kerja Pengendalian Karantina', 'capacity' => 15],
        ];

        foreach ($rooms as $room) {
            Room::firstOrCreate(['name' => $room['name']], [
                'capacity' => $room['capacity'],
                'is_active' => true,
            ]);
        }
    }
}
