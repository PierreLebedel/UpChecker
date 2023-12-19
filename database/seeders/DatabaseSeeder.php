<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Endpoint;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)
            ->create()
            ->each(function($user){

                Project::factory( mt_rand(1,5) )
                    ->create([
                        'user_id' => $user->id
                    ])
                    ->each(function($project) use ($user){
                        Endpoint::factory( mt_rand(1,3) )
                            ->create([
                                'project_id' => $project->id
                            ]);
                    });

            });

        User::orderBy('id')->first()
            ->update([
                'name' => "Dev",
                'email' => 'dev@test.com',
            ]);


    }
}
