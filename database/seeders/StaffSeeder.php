<?php

namespace Database\Seeders;

use App\Models\Staff;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    public function run()
    {
        Staff::create([
            'name' => 'John Doe',
            'email' => 'nenokostov@gmail.com',
            'role' => 'Front Desk',
        ]);
    }
}

