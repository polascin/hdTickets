<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Assigns roles to existing users based on their current role field
     */
    public function run(): void
    {
        Log::info('Starting User Role assignment seeding');

        DB::transaction(function (): void {
            $this->assignRolesToExistingUsers();
        });

        Log::info('User Role assignment seeding completed');
    }

    /**
     * Assign roles to existing users based on their role field
     */
    private function assignRolesToExistingUsers(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // Clear existing role assignments for this user
            DB::table('user_roles')->where('user_id', $user->id)->delete();

            if (! empty($user->role)) {
                $role = Role::where('name', $user->role)->first();

                if ($role) {
                    // Assign the role to the user
                    DB::table('user_roles')->insert([
                        'user_id'     => $user->id,
                        'role_id'     => $role->id,
                        'assigned_at' => now(),
                        'assigned_by' => 1, // System assignment
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);

                    Log::info("Assigned role '{$user->role}' to user '{$user->email}' (ID: {$user->id})");
                } else {
                    Log::warning("Role '{$user->role}' not found for user '{$user->email}' (ID: {$user->id})");
                }
            } else {
                Log::info("User '{$user->email}' (ID: {$user->id}) has no role assigned");
            }
        }

        // Count assignments
        $totalAssignments = DB::table('user_roles')->count();
        Log::info("Total role assignments created: {$totalAssignments}");
    }
}
