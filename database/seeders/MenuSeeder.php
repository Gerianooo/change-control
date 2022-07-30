<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dashboard = Menu::create([
            'name' => 'dashboard',
            'icon' => 'tachometer-alt',
            'route_or_url' => 'dashboard',
            'position' => 1,
            'deleteable' => false,
        ]);

        $builtin = Menu::create([
            'name' => 'builtin',
            'icon' => 'circle',
            'position' => 2,
            'deleteable' => false,
        ]);

        $permission = $builtin->childs()->create([
            'name' => 'permission',
            'route_or_url' => 'superuser.permission.index',
            'icon' => 'key',
            'position' => 1,
            'deleteable' => false,
            'actives' => [
                'superuser.permission.*',
            ],
        ]);

        $permission->permissions()->attach(
            Permission::whereIn('name', [
                'create permission', 'read permission', 'update permission', 'delete permission',
            ])->get(['id'])
        );

        $role = $builtin->childs()->create([
            'name' => 'role',
            'route_or_url' => 'superuser.role.index',
            'icon' => 'user-cog',
            'position' => 2,
            'deleteable' => false,
            'actives' => [
                'superuser.role.*',
            ],
        ]);

        $role->permissions()->attach(
            Permission::whereIn('name', [
                'create role', 'read role', 'update role', 'delete role',
            ])->get(['id'])
        );

        $user = $builtin->childs()->create([
            'name' => 'user',
            'route_or_url' => 'superuser.user.index',
            'icon' => 'user',
            'position' => 3,
            'deleteable' => false,
            'actives' => [
                'superuser.user.*',
            ],
        ]);

        $user->permissions()->attach(
            Permission::whereIn('name', [
                'create user', 'read user', 'update user', 'delete user',
            ])->get(['id'])
        );

        $menu = $builtin->childs()->create([
            'name' => 'menu',
            'route_or_url' => 'superuser.menu.index',
            'icon' => 'bars',
            'position' => 4,
            'deleteable' => false,
            'actives' => [
                'superuser.menu.*',
            ],
        ]);

        $menu->permissions()->attach(
            Permission::whereIn('name', [
                'create menu', 'read menu', 'update menu', 'delete menu',
            ])->get(['id'])
        );

        $master = Menu::create([
            'name' => 'master',
            'icon' => 'server',
            'position' => 3,
        ]);

        $document = $master->childs()->create([
            'name' => 'document',
            'icon' => 'newspaper',
            'route_or_url' => 'document.index',
            'position' => 1,
            'actives' => [
                'document.*',
            ],
        ]);

        $document->permissions()->attach(
            Permission::whereIn('name', [
                'read document',
            ])->get()->pluck('id')
        );

        $approvals = Menu::create([
            'name' => 'approvals',
            'icon' => 'list-check',
            'position' => 4,
        ]);

        $approvals->childs()->create([
            'name' => 'document',
            'icon' => 'newspaper',
            'route_or_url' => 'approval.document.index',
            'position' => 1,
            'actives' => [
                'approval.document.*',
            ],
        ]);

        $approvals->childs()->create([
            'name' => 'revision',
            'icon' => 'list-ol',
            'route_or_url' => 'approval.revision.index',
            'position' => 2,
            'actives' => [
                'approval.revision.*',
            ],
        ]);

        $activities = Menu::create([
            'name' => 'activities',
            'icon' => 'address-card',
            'position' => 5,
            'deleteable' => false,
        ]);

        $activities->childs()->create([
            'name' => 'login',
            'route_or_url' => 'superuser.activity.login',
            'icon' => 'user-clock',
            'position' => 1,
            'deleteable' => false,
            'actives' => [
                'superuser.activity.login',
            ],
        ]);
    }
}
