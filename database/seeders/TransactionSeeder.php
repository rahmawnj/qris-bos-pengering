<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\Transaction;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Ambil semua outlets sekali untuk efisiensi
        $outlets = Outlet::all();

        // Jika tidak ada outlet, hentikan seeder
        if ($outlets->isEmpty()) {
            $this->command->warn('No outlets found. Please seed outlets first.');
            return;
        }

        $paymentTypes = ['manual', 'qris', 'member'];
        $serviceTypes = ['washer', 'dryer'];
        $timezones = ['wib', 'wita', 'wit'];

        // Tentukan berapa banyak transaksi yang ingin Anda buat
        $numberOfTransactions = 50000; // Misalnya, 50.000 transaksi

        // Ambil ID outlet secara acak dari semua outlet yang tersedia
        $outletIds = $outlets->pluck('id')->toArray();
        $ownerIds = $outlets->pluck('owner_id', 'id')->toArray(); // Map outlet_id to owner_id

        for ($i = 0; $i < $numberOfTransactions; $i++) {
            // Pilih outlet secara acak
            $randomOutletId = $outletIds[array_rand($outletIds)];
            $ownerId = $ownerIds[$randomOutletId];

            $now = Carbon::now();
            $randomDateTime = null;

            $dateStrategy = $faker->numberBetween(1, 10);

            if ($dateStrategy <= 5) {
                $randomDateTime = $faker->dateTimeBetween($now->copy()->subDays(7), $now);
            } elseif ($dateStrategy <= 8) {
                $randomDateTime = $faker->dateTimeBetween($now->copy()->subDays(30), $now->copy()->subDays(7));
            } else {
                $randomDateTime = $faker->dateTimeBetween($now->copy()->subYear(), $now->copy()->subDays(30));
            }

            if (is_null($randomDateTime)) {
                $randomDateTime = $faker->dateTimeBetween($now->copy()->subYear(), $now);
            }

            Transaction::create([
                'owner_id'     => $ownerId,
                'outlet_id'    => $randomOutletId,
                'device_code'  => 'DEV-' . strtoupper(Str::random(6)),
                'order_id'     => 'ORD-' . strtoupper(Str::random(8)),
                'amount'       => $faker->numberBetween(10000, 500000),
                'timezone'     => $timezones[array_rand($timezones)],
                'date'         => $randomDateTime->format('Y-m-d'),
                'time'         => $randomDateTime->format('H:i:s'),
                'type'         => $paymentTypes[array_rand($paymentTypes)],
                'service_type' => $serviceTypes[array_rand($serviceTypes)],
                'status'       => 'success',
                'created_at'   => $randomDateTime,
                'updated_at'   => $randomDateTime,
            ]);
        }
    }
}