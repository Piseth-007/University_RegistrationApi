<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles =[
            ['name'=>'admin','description'=>'System administrator'],
            ['name'=>'staff','description'=>'University Staff'],
            ['name'=> 'student','description'=>'University Studnet'],
        ];

        foreach($roles as $role){
            Role::firstOrCreate(
                ['name' => $role['name']],
                ['description'=>$role['description']]
            );
        }
    }
}