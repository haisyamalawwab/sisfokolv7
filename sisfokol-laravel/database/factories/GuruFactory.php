<?php

namespace Database\Factories;

use App\Modules\Academic\Models\Guru;
use Illuminate\Database\Eloquent\Factories\Factory;

class GuruFactory extends Factory
{
    protected $model = Guru::class;

    public function definition(): array
    {
        return [
            'nip' => $this->faker->unique()->numerify('##################'),
            'nama' => $this->faker->name(),
            'jenis_kelamin' => $this->faker->randomElement(['L', 'P']),
            'telepon' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'jabatan' => 'Guru Mata Pelajaran',
            'aktif' => true,
        ];
    }
}
