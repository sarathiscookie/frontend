<?php

use Illuminate\Database\Seeder;

use App\Ordernumber;
use Carbon\Carbon;

class OrdernumberTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [[
            'number' => 100000
        ]];

        Ordernumber::insert($data);
    }
}
