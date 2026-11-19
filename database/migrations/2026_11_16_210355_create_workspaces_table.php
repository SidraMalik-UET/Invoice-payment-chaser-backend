
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspaces', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();

            $table->foreignId('owner_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('currency', 3);
            $table->string('timezone');
            $table->unsignedTinyInteger('reminder_hour')->default(9);

            $table->string('logo_path')->nullable();
            $table->string('brand_color')->nullable();
            $table->string('reply_to_email')->nullable();
            $table->string('stripe_account_id')->nullable();
            $table->string('plan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspaces');
    }
};
