<?php

namespace Database\Factories;

use App\Modules\Academic\Models\Siswa;
use Illuminate\Database\Eloquent\Factories\Factory;

class SiswaFactory extends Factory
{
    protected $model = Siswa::class;

    public function definition(): array
    {
        return [
            'nis' => $this->faker->unique()->numerify('##########'),
            'nisn' => $this->faker->optional()->numerify('##########'),
            'nama' => $this->faker->name(),
            'jenis_kelamin' => $this->faker->randomElement(['L', 'P']),
            'tanggal_lahir' => $this->faker->dateTimeBetween('-15 years', '-10 years')->format('Y-m-d'),
            'telepon' => $this->faker->phoneNumber(),
            'agama' => 'Islam',
            'status' => 'aktif',
        ];
    }
}
