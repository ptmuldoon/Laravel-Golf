<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $players = [
            ['first_name' => 'John', 'last_name' => 'Anderson', 'email' => 'john.anderson@email.com', 'phone_number' => '555-0101'],
            ['first_name' => 'Sarah', 'last_name' => 'Mitchell', 'email' => 'sarah.mitchell@email.com', 'phone_number' => '555-0102'],
            ['first_name' => 'Michael', 'last_name' => 'Thompson', 'email' => 'michael.thompson@email.com', 'phone_number' => '555-0103'],
            ['first_name' => 'Emily', 'last_name' => 'Rodriguez', 'email' => 'emily.rodriguez@email.com', 'phone_number' => '555-0104'],
            ['first_name' => 'David', 'last_name' => 'Williams', 'email' => 'david.williams@email.com', 'phone_number' => '555-0105'],
            ['first_name' => 'Jessica', 'last_name' => 'Martinez', 'email' => 'jessica.martinez@email.com', 'phone_number' => '555-0106'],
            ['first_name' => 'Robert', 'last_name' => 'Johnson', 'email' => 'robert.johnson@email.com', 'phone_number' => '555-0107'],
            ['first_name' => 'Amanda', 'last_name' => 'Brown', 'email' => 'amanda.brown@email.com', 'phone_number' => '555-0108'],
            ['first_name' => 'Christopher', 'last_name' => 'Davis', 'email' => 'christopher.davis@email.com', 'phone_number' => '555-0109'],
            ['first_name' => 'Jennifer', 'last_name' => 'Garcia', 'email' => 'jennifer.garcia@email.com', 'phone_number' => '555-0110'],
            ['first_name' => 'James', 'last_name' => 'Wilson', 'email' => 'james.wilson@email.com', 'phone_number' => '555-0111'],
            ['first_name' => 'Michelle', 'last_name' => 'Taylor', 'email' => 'michelle.taylor@email.com', 'phone_number' => '555-0112'],
            ['first_name' => 'Daniel', 'last_name' => 'Moore', 'email' => 'daniel.moore@email.com', 'phone_number' => '555-0113'],
            ['first_name' => 'Lisa', 'last_name' => 'Jackson', 'email' => 'lisa.jackson@email.com', 'phone_number' => '555-0114'],
            ['first_name' => 'Matthew', 'last_name' => 'White', 'email' => 'matthew.white@email.com', 'phone_number' => '555-0115'],
            ['first_name' => 'Ashley', 'last_name' => 'Harris', 'email' => 'ashley.harris@email.com', 'phone_number' => '555-0116'],
            ['first_name' => 'Joshua', 'last_name' => 'Martin', 'email' => 'joshua.martin@email.com', 'phone_number' => '555-0117'],
            ['first_name' => 'Lauren', 'last_name' => 'Thompson', 'email' => 'lauren.thompson@email.com', 'phone_number' => '555-0118'],
            ['first_name' => 'Ryan', 'last_name' => 'Garcia', 'email' => 'ryan.garcia@email.com', 'phone_number' => '555-0119'],
            ['first_name' => 'Nicole', 'last_name' => 'Robinson', 'email' => 'nicole.robinson@email.com', 'phone_number' => '555-0120'],
            ['first_name' => 'Andrew', 'last_name' => 'Clark', 'email' => 'andrew.clark@email.com', 'phone_number' => '555-0121'],
            ['first_name' => 'Melissa', 'last_name' => 'Lewis', 'email' => 'melissa.lewis@email.com', 'phone_number' => '555-0122'],
            ['first_name' => 'Kevin', 'last_name' => 'Walker', 'email' => 'kevin.walker@email.com', 'phone_number' => '555-0123'],
            ['first_name' => 'Stephanie', 'last_name' => 'Hall', 'email' => 'stephanie.hall@email.com', 'phone_number' => '555-0124'],
            ['first_name' => 'Brian', 'last_name' => 'Allen', 'email' => 'brian.allen@email.com', 'phone_number' => '555-0125'],
            ['first_name' => 'Rachel', 'last_name' => 'Young', 'email' => 'rachel.young@email.com', 'phone_number' => '555-0126'],
            ['first_name' => 'Thomas', 'last_name' => 'King', 'email' => 'thomas.king@email.com', 'phone_number' => '555-0127'],
            ['first_name' => 'Rebecca', 'last_name' => 'Wright', 'email' => 'rebecca.wright@email.com', 'phone_number' => '555-0128'],
            ['first_name' => 'Steven', 'last_name' => 'Lopez', 'email' => 'steven.lopez@email.com', 'phone_number' => '555-0129'],
            ['first_name' => 'Christina', 'last_name' => 'Hill', 'email' => 'christina.hill@email.com', 'phone_number' => '555-0130'],
            ['first_name' => 'Paul', 'last_name' => 'Muldoon', 'email' => 'paul.muldoon@email.com', 'phone_number' => '555-0131'],
            ['first_name' => 'Kevin', 'last_name' => 'Johnson', 'email' => 'kevin.johnson@email.com', 'phone_number' => '555-0132'],
        ];

        foreach ($players as $player) {
            DB::table('players')->insert([
                'first_name' => $player['first_name'],
                'last_name' => $player['last_name'],
                'email' => $player['email'],
                'phone_number' => $player['phone_number'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
