<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents; // No model events will be dispatched when running seeder

    public function run(): void
    {
       // php artisan make:seeder UserSeeder
       // A seeder class only contains one method by default: run, which executes when db:seed called.
       // We can use query builder or eloquent factories(for large amount data) to insert data.
       // Can type-hint any dependency in run method.
       DB::table('users')->insert([
            'name' => Str::random(10),
            // updateOrInsert() etc.
       ]);  // Or,
       User::create(['name' => 'Dhaka 1', 'division_id' => 1]); // or,
       User::factory()->count(50)->hasPosts(1)->create();  // Or,
       // See UserFactory class.

       $territories = [['name' => '1',], ['name' => '2']];
       foreach($territories as $t){
            User::create(array_merge($t, ['status' => 1]));
       }

       // We can call multiple seeders in DatabaseSeeder:
       $this->call([UserSeeder::class, PostSeeder::class],);
    }

    //* Running Seeders:
    // Run DatabaseSeeder: php artisan db:seed
    // Run specific Seeeder: php artisan db:seed --class=UserSeeder
    // php artisan migrate:fresh --seed
    // php artisan migrate:fresh --seed --seeder=UserSeeder
    // php artisan db:seed --force
}
