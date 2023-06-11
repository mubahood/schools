<?php

namespace Database\Factories;

use App\Models\AcademicClass;
use App\Models\TheologyClass;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $email = $this->faker->unique()->safeEmail;
        $phone_number_1 = $this->faker->unique()->phoneNumber();
        $phone_number_2 = $this->faker->unique()->phoneNumber();
        $mother_phone = $this->faker->unique()->phoneNumber();
        $classes = AcademicClass::where(
            'enterprise_id',
            config('config.demo_ent')
        )->get()->pluck('id');
        $classes_2 = TheologyClass::where(
            'enterprise_id',
            config('config.demo_ent')
        )->get()->pluck('id');
        $current_class_id = $classes[rand(0, (count($classes) - 1))];
        $current_theology_class_id = 0;
        if (count($classes_2) > 1) {
            $current_theology_class_id = $classes_2[rand(0, (count($classes_2) - 1))];
        }

        return [
            'enterprise_id' => config('config.demo_ent'),
            'demo_id' => config('config.demo_ent'),
            'username' => $email,
            'email' => $email,
            'password' => password_hash('4321', PASSWORD_DEFAULT),
            'name' => $this->faker->name,
            'father_name' => $this->faker->name,
            'mother_phone' => $mother_phone,
            'mother_name' => $this->faker->name,
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'place_of_birth' => $this->faker->address(),
            'home_address' => $this->faker->address(),
            'sex' => ['Male', 'Female'][rand(0, 1)],
            'phone_number_1' => $phone_number_1,
            'phone_number_2' => $phone_number_2,
            'date_of_birth' => Carbon::now()->subDays(((365) * (rand(12, 23)))),
            'avatar' => rand(1, 50) . ".jpg",
            'remember_token' => time() . rand(10, 1000),
            'verification' => 1,
            'status' => 1,
            'current_class_id' => $current_class_id,
            'current_theology_class_id' => $current_theology_class_id,
            'user_type' => 'student',
            'primary_school_name' => time() . rand(10000, 100000),

        ];
    }
}
