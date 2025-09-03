<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();

        return [
            'employee_number' => 'EMP' . $this->faker->unique()->numberBetween(1000, 9999),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'national_id' => $this->faker->unique()->numerify('##########'), // 10-digit national ID
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'date_of_birth' => $this->faker->date('Y-m-d', '-18 years'),
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'marital_status' => $this->faker->randomElement(['Single', 'Married', 'Divorced', 'Widowed']),
            'employment_type' => $this->faker->randomElement(['Permanent', 'Contract', 'Casual']),
            'hire_date' => $this->faker->date('Y-m-d', '-2 years'),
            'is_active' => true,
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ];
    }

    /**
     * Indicate that the employee is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the employee is a manager.
     */
    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_manager' => true,
        ]);
    }
}
