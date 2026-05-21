<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->string('service_name')->nullable()->after('service_id');
            $table->decimal('service_price', 10, 2)->nullable()->after('service_name');
            $table->string('service_currency', 5)->nullable()->after('service_price');
        });

        DB::table('service_requests')
            ->select('id', 'service_id')
            ->orderBy('id')
            ->chunkById(100, function ($requests): void {
                $services = DB::table('services')
                    ->whereIn('id', collect($requests)->pluck('service_id')->all())
                    ->get(['id', 'name', 'price', 'currency'])
                    ->keyBy('id');

                foreach ($requests as $request) {
                    $service = $services->get($request->service_id);

                    if (!$service) {
                        continue;
                    }

                    DB::table('service_requests')
                        ->where('id', $request->id)
                        ->update([
                            'service_name' => $service->name,
                            'service_price' => $service->price,
                            'service_currency' => strtoupper($service->currency ?: 'USD'),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn(['service_name', 'service_price', 'service_currency']);
        });
    }
};
