<?php

namespace Database\Seeders;

use App\Models\Device;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $devices = [
            ['name' => 'Mir Telecom Group', 'device_uid' => '1', 'ip_address' => '220.247.165.30', 'port' => '3030'],
            ['name' => 'Orange Pie', 'device_uid' => '2', 'ip_address' => '103.234.27.83', 'port' => '7061'],
            ['name' => 'Bogra', 'device_uid' => '4', 'ip_address' => '103.234.25.66', 'port' => '4370'],
            ['name' => 'Jessore', 'device_uid' => '5', 'ip_address' => '220.247.161.10', 'port' => '4370'],
            ['name' => 'Borak Tower', 'device_uid' => '3', 'ip_address' => '220.247.162.206', 'port' => '8383'],
            ['name' => 'Sylhet', 'device_uid' => '6', 'ip_address' => '220.247.166.26', 'port' => '4370'],
            ['name' => '10th Floor Borak', 'device_uid' => '7', 'ip_address' => '220.247.165.30', 'port' => '3032'],
        ];

        foreach ($devices as $device) {
            Device::updateOrCreate(
                ['device_uid' => $device['device_uid']], // Check uniqueness by machine no (device_uid)
                [
                    'name' => $device['name'],
                    'ip_address' => $device['ip_address'],
                    'port' => $device['port'],
                    'location' => $device['name']
                ]
            );
        }
    }
}
