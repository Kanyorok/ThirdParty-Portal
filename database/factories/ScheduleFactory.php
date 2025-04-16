<?php

namespace Database\Factories;

use App\Models\BR\Client;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Random\RandomException;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
{
    private Collection $clients;
    private Carbon $dated;
    private Carbon $today;

    public function __construct($count = null, ?\Illuminate\Support\Collection $states = null, ?\Illuminate\Support\Collection $has = null, ?\Illuminate\Support\Collection $for = null, ?\Illuminate\Support\Collection $afterMaking = null, ?\Illuminate\Support\Collection $afterCreating = null, $connection = null, ?\Illuminate\Support\Collection $recycle = null)
    {
        $this->clients = Client::query()->inRandomOrder()->limit(500)->get();
        $this->dated = Carbon::now()->subDays(10)->setTime(10, 41);
        $this->today = Carbon::now();
        parent::__construct($count, $states, $has, $for, $afterMaking, $afterCreating, $connection, $recycle);
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $client = $this->clients->random();
        $operator = 'CSADM';
        $start = $this->getDate();
        return [
                'ClientID'   => $client->ClientID,
                'Title'      => 'Call with ' . $client->Name . ' (' . $client->ClientID . ')',
                'Notes'      => fake()->realTextBetween(70, 500),
                'OperatorID' => $operator,
                'AcceptedOn' => $this->today,
                'StartOn'    => $start,
                'EndOn'      => $start->copy()->addMinutes($this->_randomMinutes()),
                'CreatedBy'  => $operator,
                'CreatedOn'  => $this->today,
                'ModifiedOn' => $this->today,
                'ModifiedBy' => $operator,
               ];
    }

    private function getDate(bool $loop = false): Carbon
    {
        $this->dated->addMinutes($this->_randomMinutes(20));
        //lottery
        try {
            if (random_int(1, 4) === 3) {
                $this->dated->addDay()->setTime(8, 0);
            }
        } catch (RandomException) {
        }


        $startDate = $this->dated->copy()->setTime(8, 0);
        $endDate = $this->dated->copy()->setTime(16, 30);

        if ($this->dated->copy()->between($startDate, $endDate)) {
            return $this->dated;
        }

        $this->dated->setTime(8, 0)->addDay();

        if ($this->dated->dayOfWeek === 0) {
            $this->dated->addDay()->setTime(8, 10);
            return $this->dated;
        }

        if ($loop) {
            return $this->dated->setTime(8, 0);
        }
        return $this->getDate(true);
    }

    private function _randomMinutes(int $add = 0): int
    {
        try {
            $min = random_int(10, 31);
        } catch (RandomException) {
            $min = 25;
        }
        return $add + $min;
    }
}
