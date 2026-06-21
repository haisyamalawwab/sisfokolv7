<?php

namespace Database\Factories;

use App\Modules\Academic\Models\Guru;
use App\Modules\Academic\Models\Kelas;
use Illuminate\Database\Eloquent\Factories\Factory;

class KelasFactory extends Factory
{
    protected $model = Kelas::class;

    public function definition(): array
    {
        return [
            'wali_kelas_id' => Guru::factory(),
            'nama' => $this->faker->randomElement(['7-A', '8-A', '9-A', '7-B', '8-B', '9-B']),
            'tingkat' => $this->faker->randomElement([7, 8, 9]),
            'kapasitas' => 32,
        ];
    }
}
