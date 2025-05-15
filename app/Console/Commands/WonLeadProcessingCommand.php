<?php

namespace App\Console\Commands;

use App\Enums\LeadStatusEnum;
use App\Helpers\SystemHelper;
use App\Models\BR\Client;
use App\Models\CRM\Lead;
use App\Services\BR\ClientService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WonLeadProcessingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:won-lead-processing-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $actor = SystemHelper::user();
        $leads = Lead::query()->where('t_Leads.Status', LeadStatusEnum::Won->value)->whereNull(['t_Leads.ArchivedOn', 't_Leads.ArchivedBy'])->get();
        foreach ($leads as $lead) {
            if (!$lead instanceof Lead) {
                continue;
            }

            $client = Client::query()->where(function (Builder $query) use ($lead) {
                return ClientService::search($query, $lead->Phone);
            })->first();

            if (!$client instanceof Client) {
                continue;
            }

            try {
                DB::transaction(static function () use ($actor, $lead, $client) {
                    $lead->update([
                                   'ArchivedOn'    => now(),
                                   'ArchivedBy'    => $actor->Id,
                                   'ApplicationID' => $client->ClientID,
                                  ]);
                    //change all configurations
                    $lead->calls()->update([
                                            "Party"   => Client::getPrimaryKey(),
                                            "PartyID" => $client->ClientID,
                                           ]);
                    $lead->crmmails()->update([
                                               "Party"   => Client::getPrimaryKey(),
                                               "PartyID" => $client->ClientID,
                                              ]);
                    $lead->crmsms()->update([
                                             "Party"   => Client::getPrimaryKey(),
                                             "PartyID" => $client->ClientID,
                                            ]);
                    $lead->activities()->update([
                                                 "Party"   => Client::getPrimaryKey(),
                                                 "PartyID" => $client->ClientID,
                                                ]);
                    $lead->tickets()->update([
                                              "Party"   => Client::getPrimaryKey(),
                                              "PartyID" => $client->ClientID,
                                             ]);
                    $lead->tasks()->update([
                                            "Party"   => Client::getPrimaryKey(),
                                            "PartyID" => $client->ClientID,
                                           ]);
                    $lead->discussions()->update([
                                                  "Party"   => Client::getPrimaryKey(),
                                                  "PartyID" => $client->ClientID,
                                                 ]);
                });
            } catch (Exception | \Throwable $e) {
                Log::error('Could not migrate contacts details lead (' . $lead->LeadID . ') to client (' . $client->ClientID . ')');
                Log::error($e);
            }
        }
    }
}
