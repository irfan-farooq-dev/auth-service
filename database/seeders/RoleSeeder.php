<?php
namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Create roles
        $admin  = Role::firstOrCreate(['name' => 'admin']);
        $editor = Role::firstOrCreate(['name' => 'editor']);
        $user   = Role::firstOrCreate(['name' => 'user']);

        // Create permissions
        $manageUsers = Permission::firstOrCreate(['name' => 'manage_users']);
        $createPost  = Permission::firstOrCreate(['name' => 'create_post']);
        $deletePost  = Permission::firstOrCreate(['name' => 'delete_post']);
        $viewReports = Permission::firstOrCreate(['name' => 'view_reports']);

        // Assign permissions to roles
        $admin->permissions()->sync([$manageUsers->id, $createPost->id, $deletePost->id, $viewReports->id]);
        $editor->permissions()->sync([$createPost->id, $deletePost->id]);
        $user->permissions()->sync([$createPost->id]);
    }
}
