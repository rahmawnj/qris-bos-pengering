<?php

use App\Models\Device;
use App\Models\ServiceType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('device_service_type', function (Blueprint $table) {
            $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
            $table->foreignId('service_type_id')->constrained('service_types')->onDelete('cascade');
            $table->integer('price')->default(0);
            $table->unique(['device_id', 'service_type_id']);
        });

        $serviceTypes = ServiceType::all();
        $allDevices = Device::all();

        $fixedDevices = $allDevices->take(2);
        $remainingDevices = $allDevices->skip(2)->shuffle()->take(floor($allDevices->count() / 2));
        $selectedDevices = $fixedDevices->merge($remainingDevices);

        foreach ($selectedDevices as $device) {
        $randomServiceTypes = $serviceTypes->random(rand(1, min(3, $serviceTypes->count())));

        foreach ($randomServiceTypes as $serviceType) {
                DB::table('device_service_type')->updateOrInsert(
                    [
                        'device_id' => $device->id,
                        'service_type_id' => $serviceType->id,
                    ],
                    [
                        'price' => rand(10000, 50000),
                    ]
                );
            }
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_service_type');
    }
};
