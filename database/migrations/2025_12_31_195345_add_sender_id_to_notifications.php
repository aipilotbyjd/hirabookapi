<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('sender_id')->nullable()->after('id');
            $table->string('receiver_id')->nullable()->after('sender_id');
            $table->string('type')->nullable()->comment('like, comment, follow, message, post, comment_reply, like_reply')->after('receiver_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('sender_id');
            $table->dropColumn('receiver_id');
            $table->dropColumn('type');
        });
    }
};
