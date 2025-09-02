<?php

use App\Enums\TicketStatusEnum;
use App\Models\Core\CodeDetail;
use App\Models\CRM\Ticket;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('t_Tickets', static function (Blueprint $table) {
            $table->foreignId('StatusId')->nullable()->after('StatusId')->constrained('t_CodeDetails', 'ID');
        });

        Ticket::query()->where('Status', TicketStatusEnum::Active)
            ->update(['StatusId' => CodeDetail::query()->where('CodeID', 'TicketStatus')
                ->where('Value', TicketStatusEnum::Active)->first('ID')->ID]);

        Ticket::query()->where('Status', TicketStatusEnum::Cancelled)
            ->update(['StatusId' => CodeDetail::query()->where('CodeID', 'TicketStatus')
                ->where('Value', TicketStatusEnum::Cancelled)->first('ID')->ID]);

        Ticket::query()->where('Status', TicketStatusEnum::Resolved)
            ->update(['StatusId' => CodeDetail::query()->where('CodeID', 'TicketStatus')
                ->where('Value', TicketStatusEnum::Resolved)->first('ID')->ID]);

        Ticket::query()->where('Status', TicketStatusEnum::Approval)
            ->update(['StatusId' => CodeDetail::query()->where('CodeID', 'TicketStatus')
                ->where('Value', TicketStatusEnum::Approval)->first('ID')->ID]);

        Schema::table('t_Tickets', static function (Blueprint $table) {
            $table->unsignedBigInteger('StatusId')->nullable(false)->change();
            $table->dropColumn('Status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('t_Tickets', static function (Blueprint $table) {
            $table->char('Status', 1)->nullable();
        });
        Ticket::query()->where('StatusId', CodeDetail::query()->where('CodeID', 'TicketStatus')
            ->where('Value', TicketStatusEnum::Active)->first('ID')->ID)
            ->update(['Status' => TicketStatusEnum::Active]);

        Ticket::query()->where('StatusId', CodeDetail::query()->where('CodeID', 'TicketStatus')
            ->where('Value', TicketStatusEnum::Cancelled)->first('ID')->ID)
            ->update(['Status' => TicketStatusEnum::Cancelled]);

        Ticket::query()->where('StatusId', CodeDetail::query()->where('CodeID', 'TicketStatus')
            ->where('Value', TicketStatusEnum::Resolved)->first('ID')->ID)
            ->update(['Status' => TicketStatusEnum::Resolved]);

        Ticket::query()->where('StatusId', CodeDetail::query()->where('CodeID', 'TicketStatus')
            ->where('Value', TicketStatusEnum::Approval)->first('ID')->ID)
            ->update(['Status' => TicketStatusEnum::Approval]);

        Schema::table('t_Tickets', static function (Blueprint $table) {
            $table->char('Status', 1)->nullable(false)->change();
            $table->dropConstrainedForeignId('StatusId');
        });
    }
};
