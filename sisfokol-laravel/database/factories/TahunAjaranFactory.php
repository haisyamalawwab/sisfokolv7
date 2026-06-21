<?php

namespace Database\Factories;

use App\Modules\Academic\Models\TahunAjaran;
use Illuminate\Database\Eloquent\Factories\Factory;

class TahunAjaranFactory extends Factory
{
    protected $model = TahunAjaran::class;

    public function definition(): array
    {
        $year = $this->faker->unique()->numberBetween(2025, 2045);
        $next = $year + 1;
        return [
            'nama' => "{$year}/{$next}",
            'tanggal_mulai' => "{$year}-07-01",
            'tanggal_selesai' => "{$next}-06-30",
            'aktif' => false,
        ];
    }
}
