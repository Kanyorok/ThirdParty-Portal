<?php

namespace App\Traits\Controller;

use App\Enums\TicketPriorityEnum;
use App\Enums\TicketSourceEnum;
use App\Exceptions\ErroredException;
use App\Models\BR\Client;
use App\Models\CodeDetail;
use App\Models\Lead;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

trait TicketsTrait
{
    /**
     * @throws Exception
     */
    public function tickets(MorphMany|Builder $query, array $with = ['category']): JsonResponse
    {
        return TicketService::dt($query, $with);
    }

    /**
     * @throws ErroredException
     * @throws Throwable
     */
    public function save(
        Model $model, CodeDetail $category, string $title, string $description, User $actor, TicketSourceEnum|string $Source,
        TicketPriorityEnum $priority, Carbon $start = null, Carbon $end = null, string $SourceTicketID = null, string $SourceID = '0'
    ): TicketService
    {
        if (!$model instanceof Lead && !$model instanceof Client && !$model instanceof User) {
            throw new ErroredException('unknown party given');
        }
        return DB::transaction(static function () use ($SourceTicketID, $priority, $model, $category, $title, $description, $actor, $Source, $SourceID, $start, $end) {
            if ($model instanceof Lead) {
                return TicketService::lead($model, $category, $title, $description, $actor, ($Source instanceof TicketSourceEnum)?$Source->value:$Source, $priority, SourceID: $SourceID, start: $start, end: $end);
            }

            if ($model instanceof Client) {
                return TicketService::client($model, $category, $title, $description, $actor, ($Source instanceof TicketSourceEnum)?$Source->value:$Source, $priority,SourceID: $SourceID, start: $start, end: $end, SourceTicketID: $SourceTicketID);
            }

            return TicketService::user($category, $title, $description, $actor,($Source instanceof TicketSourceEnum)?$Source->value:$Source, $priority, SourceID: $SourceID, start: $start, end: $end);
        });
    }

    public function service(Ticket $ticket): TicketService
    {
        return new TicketService($ticket);
    }

}
