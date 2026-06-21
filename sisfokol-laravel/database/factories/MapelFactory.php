<?php

namespace Database\Factories;

use App\Modules\Academic\Models\Mapel;
use Illuminate\Database\Eloquent\Factories\Factory;

class MapelFactory extends Factory
{
    protected $model = Mapel::class;

    public function definition(): array
    {
        return [
            'kode' => $this->faker->unique()->lexify('???-###'),
            'nama' => $this->faker->words(2, true),
            'kkm' => 75.00,
            'jenjang' => 'SMP',
        ];
    }
}
