<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Owner;
use App\Models\Outlet;
use App\Models\Member;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OwnerOutletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');
        $fakerEn = \Faker\Factory::create('en_US');

        DB::transaction(function () use ($faker, $fakerEn) {
            $owners = collect();

            for ($i = 1; $i <= 5; $i++) {
                $brandName = ucfirst($faker->word) . ' Laundry';

                $user = User::create([
                    'name'     => $faker->name,
                    'email'    => $faker->unique()->safeEmail,
                    'password' => Hash::make('123456'),
                    'role'     => 'owner',
                ]);

                $owner = Owner::create([
                    'user_id'     => $user->id,
                    'brand_name'  => $brandName,
                    'brand_email' => $fakerEn->unique()->safeEmail,
                    'address'     => $faker->address,
                ]);

                $owners->push($owner);

                $outlets = collect();
                for ($j = 1; $j <= 10; $j++) {
                    $username = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
                    $password = Hash::make('123456');

                    $outlet = Outlet::create([
                        'outlet_name' => $faker->company,
                        'owner_id' => $owner->id,
                        'username' => $username,
                        'password' => $password,
                        'code'     => 'OUT-' . strtoupper(Str::random(6)),
                        'address'  => $faker->address,
                        'status'   => true,
                        'timezone' => 'WIB',
                    ]);

                    $outlets->push($outlet);

                    $deviceCount = rand(1, 3);
                    for ($d = 1; $d <= $deviceCount; $d++) {
                        $deviceName = "Mesin Cuci & Pengering " . $d;
                        $deviceCode = 'DEV-' . strtoupper(Str::random(6));
                        $statuses = ['off', 'washer', 'dryer'];
                        $deviceStatus = $statuses[array_rand($statuses)];

                        \App\Models\Device::create([
                            'name'          => $deviceName,
                            'code'          => $deviceCode,
                            'outlet_id'     => $outlet->id,
                            'device_status' => $deviceStatus,
                        ]);
                    }
                }

                $memberUser = User::create([
                    'name'     => $faker->name,
                    'email'    => $faker->unique()->safeEmail,
                    'password' => Hash::make('123456'),
                    'role'     => 'member',
                    'image'    => $faker->imageUrl(200, 200, 'people', true),
                ]);

                $member = Member::create([
                    'user_id' => $memberUser->id,
                ]);

                DB::table('subscription')->insert([
                    'amount'      => $faker->numberBetween(10, 500) . '000',
                    'member_id'   => $member->id,
                    'owner_id'    => $owner->id,
                    'is_verified' => (bool) rand(0, 1),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        });

        DB::table('services')->insert([
            [
                'outlet_id' => 1,
                'name' => 'Cuci Kering',
                'price' => 15000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'outlet_id' => 1,
                'name' => 'Cuci Basah',
                'price' => 12000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'outlet_id' => 1,
                'name' => 'Cuci + Setrika',
                'price' => 20000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        DB::table('addons')->insert([
            [
                'outlet_id' => 1,
                'name' => 'Pewangi Extra',
                'category' => 'Pewangi',
                'description' => 'Menambahkan wangi tahan lama.',
                'price' => 2000.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'outlet_id' => 1,
                'name' => 'Setrika Premium',
                'category' => 'Setrika',
                'description' => 'Setrika dengan uap dan rapi sempurna.',
                'price' => 5000.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'outlet_id' => 1,
                'name' => 'Detergen Hypoallergenic',
                'category' => 'Detergen',
                'description' => 'Aman untuk kulit sensitif.',
                'price' => 3000.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'outlet_id' => 1,
                'name' => 'Laundry Express',
                'category' => 'Waktu',
                'description' => 'Selesai dalam 3 jam.',
                'price' => 10000.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'outlet_id' => 1,
                'name' => 'Pewangi Anak-anak',
                'category' => 'Pewangi',
                'description' => 'Aroma lembut dan aman untuk anak.',
                'price' => 1500.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'outlet_id' => 1,
                'name' => 'Anti Bakteri',
                'category' => 'Kebersihan',
                'description' => 'Membunuh bakteri hingga 99.9%.',
                'price' => 2500.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'outlet_id' => 1,
                'name' => 'Plastik Tambahan',
                'category' => 'Packaging',
                'description' => 'Tambahan plastik untuk baju.',
                'price' => 1000.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'outlet_id' => 1,
                'name' => 'Setrika Khusus Kemeja',
                'category' => 'Setrika',
                'description' => 'Presisi untuk bahan formal.',
                'price' => 4000.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'outlet_id' => 1,
                'name' => 'Penanganan Premium',
                'category' => 'Pelayanan',
                'description' => 'Dikerjakan oleh staf senior.',
                'price' => 7000.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'outlet_id' => 1,
                'name' => 'Pewangi Lavender',
                'category' => 'Pewangi',
                'description' => 'Wangi segar dan menenangkan.',
                'price' => 2000.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $services = DB::table('services')->get(); // Semua service
        $serviceTypes = DB::table('service_types')->get(); // Semua tipe layanan

        $insertData = [];

        foreach ($services as $service) {
            foreach ($serviceTypes as $type) {
                $insertData[] = [
                    'service_id' => $service->id,
                    'service_type_id' => $type->id,
                ];
            }
        }

        // Bulk insert ke service_options
        DB::table('service_options')->insert($insertData);
    }
}