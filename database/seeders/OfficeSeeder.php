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
            ['name' => 'Mir Telecom Ltd.', 'office_type_id' => $headOffice->id, 'order_number' => 1],
            ['name' => 'Bangla Telecom Ltd.', 'office_type_id' => $branchOffice->id, 'order_number' => 2],
            ['name' => 'Coloasia Ltd.', 'office_type_id' => $branchOffice->id, 'order_number' => 3],
            ['name' => 'BTS Communications (BD) Ltd.', 'office_type_id' => $branchOffice->id, 'order_number' => 4],
        ];

        foreach ($offices as $officeData) {
            $office = Office::updateOrCreate(
                ['name' => $officeData['name']], 
                $officeData
            );

            // Fill address, phone, email if they are empty
            if (empty($office->address) || empty($office->phone) || empty($office->email)) {
                $office->update([
                    'address' => $office->address ?: fake()->address(),
                    'phone' => $office->phone ?: fake()->phoneNumber(),
                    'email' => $office->email ?: fake()->unique()->companyEmail(),
                ]);
            }
        }
    }
}
