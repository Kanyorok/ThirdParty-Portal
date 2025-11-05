<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_Socials', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('SocialID')->unique();
            $table->string('RemoteId');
            $table->char('Type', 3);
            $table->text('Content');
            $table->bigInteger('LikesCount');
            $table->bigInteger('CommentsCount');
            $table->dateTime('Published_at')->nullable();
            $table->dateTime('Scheduled_at')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->text('Response')->nullable();
            $table->bigInteger('ViewsCount')->default(0);

            $table->primary(['Id'], 'pk__t_social__3214ec0710b0bcc5');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Socials');
    }
};
