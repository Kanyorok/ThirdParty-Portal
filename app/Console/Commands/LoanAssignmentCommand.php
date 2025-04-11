<?php

namespace App\Console\Commands;

use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Models\BR\DebtProduct;
use App\Models\DebtRecovery\LoanAssignment;
use App\Models\User;
use App\Services\DebtCollection\LoanService;
use App\Services\UserService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class LoanAssignmentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:loan-assignment-command';

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
        try {
            $dated = Carbon::parse(DebtProduct::query()->max('processdate'));
        } catch (Exception $exception) {
            $dated = null;
        }

        if (!$dated instanceof Carbon) {
            Log::error('Loan Assignment Error: No Debt Products found Date ISSUE');
            return;
        }

        $actor = SystemHelper::user();
        $loans = DebtProduct::query()->where('processDate', $dated)
            ->whereNotIn('AccountID', LoanAssignment::query()->whereNull('EndOn')->select('AccountID'))
            ->where('Classification', '!=', 'PERFORMING')->get();

        foreach ($loans as $loan) {
            $user = LoanService::getAssignUser($loan->AccountID);
            if (!$user instanceof User) {
                break;
            }

            try {
                (new LoanService($loan))->assign($user, $actor, false);
            } catch (ErroredException $e) {
            }
        }

        if ($loans->count() > 0) {
            $this->notifyUsers();
        }
    }

    public function notifyUsers(): void
    {
        $usersWithLoansToday = User::query()->whereHas('loansAssigned', function (Builder $query) {
                $query->whereBetween('t_LoanAssignments.StartOn', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()]);
        })->withCount([
                       'loansAssigned' => function ($builder) {
                                $builder->whereBetween('t_LoanAssignments.StartOn', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()]);
                       },
                      ])->get();
        foreach ($usersWithLoansToday as $user) {
            $loans = $user->loansAssigned()->whereBetween('t_LoanAssignments.StartOn', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()])
                ->select('AccountID')->get('AccountID')->pluck('AccountID')->toArray();

            if (count($loans) > 0) {
                (new UserService($user))->sendEmail(
                    subject: 'Loans Assignment ' . number_format(count($loans)) . ' Loan(s) Assigned',
                    body: '<p>You have new loans has been assigned to you for collection handling. Please find the details below:</p>
                   <p>Assignment Date: ' . now()->format('M d, Y') . ' <br>
                    Loans: ' . implode(', ', $loans) . ' </p>
                   <p>Please review this assignment and take necessary actions according to our collection procedures.</p>
                   <p>This is an automated message. Please do not reply to this email.</p>',
                    immediate: true,
                );
            }
        }
    }
}
