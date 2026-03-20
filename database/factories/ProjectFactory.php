<?php

namespace Database\Factories;

use App\Modules\Project\Domain\Project;
use App\Modules\Contact\Domain\Contact;
use App\Modules\Beneficiary\Domain\Beneficiary;
use App\Modules\ProjectState\Domain\ProjectState;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-2 years', 'now');
        $end   = $this->faker->dateTimeBetween($start, '+2 years');

        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(3),
            'project_url' => $this->faker->url(),

            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),

            'progress' => $this->faker->numberBetween(0, 100),
            'comments' => $this->faker->text(200),

            'project_budget' => $this->faker->randomFloat(2, 1000, 50000),
            'weight' => $this->faker->randomFloat(4, 0, 1),

            'contact_id' => Contact::factory(),
            'beneficiary_id' => Beneficiary::factory(),
            'project_state_id' => ProjectState::factory(),
        ];
    }
}
