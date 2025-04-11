<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vm_logs', function (Blueprint $table) {
            $table->float('disk_used')->nullable();
            $table->float('disk_available')->nullable();
            $table->float('disk_total')->nullable();
            $table->float('disk_percent')->nullable();
            $table->float('bandwidth_down')->nullable();
            $table->float('bandwidth_up')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('vm_logs', function (Blueprint $table) {
            $table->dropColumn([
                'disk_used', 'disk_available', 'disk_total', 'disk_percent',
                'bandwidth_down', 'bandwidth_up'
            ]);
        });
    }
};
