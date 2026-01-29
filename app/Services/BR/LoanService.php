<?php

namespace App\Services\BR;

use App\Models\Auth\User;
use App\Models\BR\Client;
use App\Models\BR\DebtProduct;
use App\Models\BR\Guarantor;
use App\Models\Communication\BulkNotification;
use App\Services\CRMEmailService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class LoanService
{
    public const MODULE = 'DEBT';

    public function __construct(public DebtProduct $loan)
    {
    }

    public function message(string $message, User $actor, bool $immediate = false, $bulkNotification = null): array
    {
        if (! $this->loan->client instanceof Client) {
            return [];
        }
        $service = (new ClientService($this->loan->client))->sendMessage($this->placeholders($this->loan->client, $message), $actor)
            ->setSource(DebtProduct::getPrimaryKey(), $this->loan->AccountID);
        if ($bulkNotification instanceof BulkNotification) {
            $service->setBulk($bulkNotification);
        }

        return $service->send($immediate)->addActivity(now(), 'Loan (' . $this->loan->AccountID . ') Payment Reminder');
    }

    /**
     * @throws Throwable
     */
    public function guarantorMessage(Collection $guarantors, string $message, User $actor): array
    {
        return DB::transaction(function () use ($actor, $guarantors, $message) {
            $activities = collect();
            foreach ($guarantors as $guarantor) {
                $activities->add((new ClientService($guarantor->client))->sendMessage($this->placeholders($guarantor, $message), $actor)
                    ->setSource(DebtProduct::getPrimaryKey(), $this->loan->AccountID)->send()
                    ->addActivity(now(), 'Loan (' . $this->loan->AccountID . ') Guarantor Message'));
            }

            return $activities->toArray();
        });
    }

    public function placeholders(Guarantor|Client $guarantor, string $content): string
    {
        $str = ($guarantor instanceof Client)
            ? Str::of($content)->replace(['#name', '#amount'], [$this->loan->AccountName, number_format($this->loan->ArrearsAmount, 2)])
            : Str::of($content)->replace(['#name', '#amount', '#loanee'], [$guarantor->client?->Name, number_format($guarantor->GuaranteeAmount, 2), $this->loan->AccountName]);

        return $str->replace([
                              '#arrears',
                              '#account',
                              '#date',
                              '#product',
                             ], [
                                 number_format($this->loan->ArrearsDays),
                                 Str::of($this->loan->AccountID)->mask('*', 4, -4),
                                 $this->loan->processDate->format('M d, Y'),
                                 $this->loan->ProductName,
                                ])->toString();
    }

    public function guarantorMail(Collection $guarantors, string $subject, string $content, User $actor, array $cc): array
    {
        return DB::transaction(function () use ($cc, $actor, $guarantors, $subject, $content) {
            $activities = collect();
            foreach ($guarantors as $guarantor) {
                $service = (new ClientService($guarantor->client))->sendEmail($this->placeholders($guarantor, $subject), $this->placeholders($guarantor, $content), $actor, $cc);
                if ($service instanceof CRMEmailService) {
                    $activities->add($service->setSource(DebtProduct::getPrimaryKey(), $this->loan->AccountID)->send()
                        ->addActivity(now(), 'Loan (' . $this->loan->AccountID . ') Guarantor Email'));
                }
            }

            return $activities;
        });
    }
}
