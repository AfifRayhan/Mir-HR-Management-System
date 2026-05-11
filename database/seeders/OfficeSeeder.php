<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\OfficeType;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Corporate Head Office', 'description' => 'Main office', 'order_number' => 1],
            ['name' => 'Branch Office', 'description' => 'Regional office', 'order_number' => 2],
        ];

        foreach ($types as $type) {
            OfficeType::firstOrCreate(['name' => $type['name']], $type);
        }

        $headOffice = OfficeType::where('name', 'Corporate Head Office')->first();
        $branchOffice = OfficeType::where('name', 'Branch Office')->first();

        $offices = [
            ['name' => 'Mir Telecom Ltd.', 'office_type_id' => $headOffice->id, 'address' => 'House- 04, Road- 21, Gulshan-1, Dhaka-1212, Bangladesh', 'phone' => '+88 09601501500, +88 02222299837', 'email' => 'info@mirtelecom-bd.com', 'logo' => 'images/MIRTEL.jpeg', 'order_number' => 1],
            ['name' => 'Bangla Telecom Ltd.', 'office_type_id' => $branchOffice->id, 'address' => 'Red Crescent Borak Tower-2 (Lavel-10) 71-72 Old Elephent Road Eskaton Garden, Dhaka 1000 Bangladesh', 'phone' => '+8809601-501500, 029354812, 029354821', 'email' => 'info@bticx.net', 'logo' => 'images/bangla_telecom_square.png', 'order_number' => 2],
            ['name' => 'Coloasia Ltd.', 'office_type_id' => $branchOffice->id, 'address' => 'House- 04, Road- 21, Gulshan-1, Dhaka-1212, Bangladesh', 'phone' => '+88 09601501500, +88 01313446644', 'email' => 'sales@coloasiabd.com, info@coloasiabd.com', 'logo' => 'images/coloasia_square.png', 'order_number' => 3],
            ['name' => 'BTS Communications (BD) Ltd.', 'office_type_id' => $branchOffice->id, 'address' => 'House# 39, Road# 13/15, Block# D, Banani, Dhaka-1213, Bangladesh.', 'phone' => '+88 09601501511, +8801730060200', 'email' => 'cnoc@mirnet.com.bd', 'logo' => 'images/btslogo.png', 'order_number' => 4],
        ];

        foreach ($offices as $officeData) {
            Office::updateOrCreate(
                ['name' => $officeData['name']],
                $officeData
            );
        }
    }
}
