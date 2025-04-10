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
        Schema::create('t_Socials', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('SocialID')->unique();
            $table->string('RemoteId');
            $table->char('Type', 3);
            $table->longText('Content');
            $table->unsignedBigInteger('LikesCount');
            $table->unsignedBigInteger('CommentsCount');
            $table->dateTime('Published_at')->nullable();
            $table->dateTime('Scheduled_at')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
            $table->json('Response')->nullable();
        });

        Schema::create('t_SocialImage', static function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('SocialId')->constrained('t_Socials', 'Id');
            $table->foreignId('ImageId')->constrained('t_CRMImages', 'ImageID');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_SocialImage');
        Schema::dropIfExists('t_Socials');
    }
};
