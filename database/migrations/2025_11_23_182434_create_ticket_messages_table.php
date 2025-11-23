<?php

use App\Models\Helpdesk\Ticket;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->longText('content');
            $table->json('attachments')->nullable(); // Chemins vers les fichiers joints
            $table->boolean('is_internal_note')->default(false); // Note entre agent
            $table->foreignIdFor(Ticket::class);
            $table->foreignIdFor(User::class);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_messages');
    }
};
