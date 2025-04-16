<?php

namespace App\Services;

use App\Helpers\SystemHelper;
use App\Models\BR\Account;
use App\Models\BR\Client;
use App\Models\BR\DebtProduct;
use App\Models\Lead;
use App\Models\Team;
use App\Models\User;
use App\Services\BR\ClientService;
use Illuminate\Database\Eloquent\Model;

class PartyService
{
    public function __construct(public ?Model $party)
    {
    }

    public function getImage(string $attr = 'class="img-thumbnail me-2" width="40" height="40"'): string
    {
        if ($this->party instanceof Client) {
            return $this->party->getImage($attr);
        }
        if ($this->party instanceof Lead) {
            return $this->party->getImage($attr);
        }
        if ($this->party instanceof User) {
            return $this->party->getImage($attr);
        }

        if ($this->party instanceof Team) {
            return '<img src="https://placehold.co/200x200?font=roboto&text=TEAM" ' . $attr . '/>';
        }

        /*if ($this->party instanceof Account) {

        }*/

        return (new Client())->getImage($attr);
    }

    public function getDTRow(string $Unknown = ''): string
    {
        if ($this->party instanceof Client) {
            return '<a href="javascript:void(0)" data-click_url="' . route('clients.summary', $this->party->ClientID) . '" data-summary_title="member summary" class="click-summary-data">' . $this->getImage('class="img-thumbnail me-2" width="40" height="40"') . ' ' . $this->party->Name . ' (M)</a>';
        }
        if ($this->party instanceof Lead) {
            return '<a href="javascript:void(0)" data-click_url="' . route('leads.summary', $this->party->LeadID) . '" data-summary_title="lead summary" class="click-summary-data">' . $this->getImage('class="img-thumbnail me-2" width="40" height="40"') . ' ' . $this->party->Name . ' ' . ($this->party->OtherNames) ?? ' ' . ' (L)</a>';
        }
        if ($this->party instanceof User) {
            return '<a href="javascript:void(0)" >' . $this->getImage('class="img-thumbnail me-2" width="40" height="40"') . ' ' . $this->party->Name . ' (E)</a>';
        }

        if ($this->party instanceof Team) {
            return '<a href="javascript:void(0)" > ' . $this->party->Name . ' (Team)</a>';
        }

        if ($this->party instanceof Account) {
            return '<details><summary>' . $this->party->Name . '</summary>
                <p>Member No <a href="javascript:void(0)" data-click_url="' . route('clients.summary', $this->party->ClientID) . '" data-summary_title="member summary" class="click-summary-data">' . $this->party->ClientID . '</a></p>
                <p>AC No <a href="javascript:void(0)" data-click_url="' . route('accounts.summary', $this->party->AccountID) . '" data-summary_title="account summary" class="click-summary-data">' . $this->party->AccountID . '</a></p>
            </details>';
        }

        if ($this->party instanceof DebtProduct) {
            return '<details><summary>' . $this->party->AccountName . '</summary>
                <p><b>Member No</b>: <a href="javascript:void(0)" data-click_url="' . route('clients.summary', $this->party->ClientID) . '" data-summary_title="member summary" class="click-summary-data">' . $this->party->ClientID . '</a><br>
                   <b>Loan Type</b>: ' . $this->party->ProductName . ' (' . $this->party->ProductID . ')<br>
                   <b>Loan No</b>: <a href="javascript:void(0)" data-click_url="' . route('accounts.summary', $this->party->AccountID) . '" data-summary_title="Loan summary" class="click-summary-data">' . $this->party->AccountID . '</a></p>
            </details>';
        }

        return $this->getImage('class="img-thumbnail me-2" width="40" height="40"') . ' Unknown ' . $Unknown;
    }

    public function getName(bool $simpleRole = false): string
    {
        if ($this->party instanceof Client) {
            return $this->party->Name . ' ' . $this->getRole($simpleRole);
        }
        if ($this->party instanceof Lead) {
            return $this->party->Name . ' ' . $this->party->OtherNames . ' ' . $this->getRole($simpleRole);
        }
        if ($this->party instanceof User) {
            return $this->party->Name . ' ' . $this->getRole($simpleRole);
        }

        if ($this->party instanceof Team) {
            return $this->party->Name . ' ' . $this->getRole($simpleRole);
        }

        if ($this->party instanceof Account) {
            return $this->party->AccountID . ' ' . $this->getRole($simpleRole);
        }

        return ' Unknown';
    }

    public function getRole(bool $simpleRole = false): string
    {
        if ($this->party instanceof Client) {
            return ($simpleRole) ? '(M)' : ' (Member)';
        }
        if ($this->party instanceof Lead) {
            return ($simpleRole) ? '(L)' : ' (Lead)';
        }
        if ($this->party instanceof User) {
            return ($simpleRole) ? '(E)' : ' (Employee)';
        }

        if ($this->party instanceof Team) {
            return ($simpleRole) ? '(T)' : ' (Team)';
        }

        if ($this->party instanceof Account) {
            return ($simpleRole) ? '(Ac)' : ' (Account)';
        }

        return ' Unknown';
    }

    public function simplified(bool $simpleRole = false, bool $summary = false, string $unknown = 'unknown'): string
    {
        if ($this->party instanceof Client) {
            return ($summary)
                ? '<a href="javascript:void(0)" data-click_url="' . route('clients.summary', $this->party->ClientID) . '" data-summary_title="member summary" class="click-summary-data">' . $this->getName($simpleRole) . '</a>'
                : '<a href="' . route('clients.show', $this->party->ClientID) . '">' . $this->getName($simpleRole) . '</a>';
        }
        if ($this->party instanceof Lead) {
            return ($summary)
                ? '<a href="javascript:void(0)" data-click_url="' . route('leads.summary', $this->party->LeadID) . '" data-summary_title="lead summary" class="click-summary-data">' . $this->getName($simpleRole) . '</a>'
                : '<a href="' . route('leads.show', $this->party->LeadID) . '">' . $this->getName($simpleRole) . ' </a>';
        }
        if ($this->party instanceof User) {
            return '<a href="javascript:void(0)" >' . $this->getName($simpleRole) . '</a>';
        }

        if ($this->party instanceof Team) {
            return ($summary)
                ? '<a href="javascript:void(0)" >' . $this->getName($simpleRole) . '</a>'
                : '<a href="' . route('teams.show', [$this->party->TeamID]) . '" > ' . $this->getName($simpleRole) . '</a>';
        }

        if ($this->party instanceof Account) {
            return ($summary)
                ? '<a href="javascript:void(0)" data-click_url="' . route('accounts.summary', [$this->party->AccountID]) . '" data-summary_title="account summary" class="click-summary-data">' . $this->getName($simpleRole) . '</a>'
                : '<a href="' . route('accounts.show', [$this->party->AccountID]) . '" > ' . $this->getName($simpleRole) . '</a>';
        }

        return $unknown;
    }

    public function sendEmail(string $subject, string $body): void
    {
        if ($this->party instanceof Client) {
            (new ClientService($this->party))->sendEmail($subject, $body, SystemHelper::user());
            return;
        }
        if ($this->party instanceof Lead) {
            (new LeadService($this->party))->sendEmail($subject, $body, SystemHelper::user());
        }
        if ($this->party instanceof User) {
            (new UserService($this->party))->sendEmail($subject, $body);
            return;
        }

        if ($this->party instanceof Team) {
            (new TeamService($this->party))->sendEmail($subject, $body, SystemHelper::user());
            return;
        }
    }
}
