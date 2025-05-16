<?php

namespace Database\Factories;

use App\Models\StampCorrectionRequest;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class StampCorrectionRequestFactory extends Factory
{
    protected $model = StampCorrectionRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $date = $this->faker->date();
        return [
            'user_id' => User::factory(),
            'attendance_id' => Attendance::factory(),
        ];
    }
}
