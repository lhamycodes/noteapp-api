<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'uuid' => Str::orderedUuid(),
            'fullname' => 'Super Admin',
            'email' => 'superadmin@tradewithkene.com',
            'password' => '12345678',
            'email_verified_at' => Carbon::now(),
        ]);

        $role = Role::where('name', 'super-admin')->first();

        $user->assignRole('super-admin');
    }
}
