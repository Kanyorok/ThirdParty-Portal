<?php

namespace App\Services;

use App\Models\Auth\Team;
use App\Models\Auth\User;

class TeamService
{
    public const MODULE = 'TEAMS';

    public function __construct(public Team $team)
    {
    }

    public function sendEmail(string $subject, string $body, User $actor): static
    {
        CRMEmailService::createTeam($this->team, $subject, $body, $actor);

        return $this;
    }
}
