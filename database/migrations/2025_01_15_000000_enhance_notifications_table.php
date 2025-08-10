<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->integer('priority')->default(5)->after('data');
            $table->json('channels')->nullable()->after('priority');
            $table->timestamp('scheduled_at')->nullable()->after('channels');
            $table->timestamp('sent_at')->nullable()->after('scheduled_at');
            $table->string('level')->default('info')->after('sent_at');

            $table->index(['notifiable_type', 'notifiable_id']);
            $table->index(['type', 'created_at']);
            $table->index(['read_at']);
            $table->index(['priority', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['notifiable_type', 'notifiable_id']);
            $table->dropIndex(['type', 'created_at']);
            $table->dropIndex(['read_at']);
            $table->dropIndex(['priority', 'created_at']);

            $table->dropColumn([
                'priority',
                'channels',
                'scheduled_at',
                'sent_at',
                'level'
            ]);
        });
    }
};
