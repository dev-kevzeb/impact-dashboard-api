<?php

namespace Database\Factories;

use App\Modules\CountryUserRole\Domain\CountryUserRole;
use App\Modules\InviteProgram\Domain\InviteProgram;
use App\Modules\Program\Domain\Program;
use App\Modules\ProgramCountryUserRole\Domain\ProgramCountryUserRole;
use App\Modules\Role\Domain\Role;
use App\Modules\User\Domain\User;
use App\Modules\UserRole\Domain\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InviteProgram>
 */
class InviteProgramFactory extends Factory
{
    protected $model = InviteProgram::class;

    public function definition(): array
    {
        $program = Program::factory()->create();

        $countryUserRole = CountryUserRole::factory()->create();
        $programCountryUserRole = ProgramCountryUserRole::create([
            'program_id' => $program->id,
            'country_user_role_id' => $countryUserRole->id,
        ]);

        $projectManagerRole = Role::firstOrCreate(
            ['name' => 'project-manager', 'guard_name' => 'api'],
            ['scope' => 'project-manager']
        );

        $invitedUser = User::factory()->create();
        $invitedUserRole = UserRole::create([
            'user_id' => $invitedUser->id,
            'role_id' => $projectManagerRole->id,
        ]);

        return [
            'program_country_user_role_id' => $programCountryUserRole->id,
            'invited_user_role_id' => $invitedUserRole->id,
        ];
    }
}
