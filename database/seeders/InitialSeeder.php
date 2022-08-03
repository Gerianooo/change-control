<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $su = User::create([
            'name' => 'root',
            'username' => 'su',
            'email' => 'root@local',
            'password' => $password = Hash::make('password'),
        ]);

        $su->email_verified_at = now();
        $su->save();

        $user = User::create([
            'name' => 'user',
            'username' => 'user',
            'email' => 'user',
            'password' => $password,
        ]);

        $user->email_verified_at = now();
        $user->save();

        Role::create([
            'name' => 'superuser',
            'guard_name' => 'web',
        ]);

        $su->assignRole('superuser');

        $mr = Role::create([
            'name' => 'mr',
            'guard_name' => 'web',
        ]);

        $mr1 = User::create([
            'name' => 'mr1',
            'email' => 'mr1@batch.record',
            'username' => 'mr1',
            'password' => $password,
        ]);

        $mr1->assignRole('mr');

        collect(['user', 'permission', 'role', 'menu', 'document', 'revision', 'content'])->each(function ($name) {
            collect(['create', 'read', 'update', 'delete'])->each(function ($ability) use ($name) {
                Permission::create([
                    'name' => sprintf('%s %s', $ability, $name),
                    'guard_name' => 'web',
                ]);
            });
        });

        collect(['document', 'revision'])->each(function ($name) {
            collect(['set %s approver', 'request %s approval'])->each(function ($ability) use ($name) {
                Permission::create([
                    'name' => sprintf($ability, $name),
                    'guard_name' => 'web',
                ]);
            });
        });

        $mr->givePermissionTo([
            'create document',
            'read document',
            'update document',
            'delete document',
            'set document approver',
            'request document approval',
            
            'create revision',
            'read revision',
            'update revision',
            'delete revision',
            'set revision approver',
            'request revision approval',
        ]);
    }
}
