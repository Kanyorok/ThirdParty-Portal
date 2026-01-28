<?php

namespace App\Console\Commands;

use App\Helpers\SystemHelper;
use App\Models\Auth\User;
use App\Models\BR\DebtProduct;
use App\Models\CRM\DebtRecovery\LoanAssignment;
use App\Services\HRM\UserService;
use Carbon\Exceptions\InvalidFormatException;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class LoanReAssignmentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:loan-re-assignment-command';

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
            if (! $dated instanceof Carbon) {
                throw new Exception('No Debt Products found Date ISSUE');
            }
        } catch (Exception | InvalidFormatException) {
            Log::error('Loan Re-Assignment Error: No Debt Products found Date ISSUE');

            return;
        }
        $actor = SystemHelper::user();

        //Loans that were Non PERFORMING now PERFORMING, End Assignment and Send Email.
        $loans = DebtProduct::query()->where('processDate', $dated)
            ->whereIn('AccountID', LoanAssignment::query()->whereNull('EndOn')->select('AccountID'))
            ->where('Classification', 'PERFORMING')->get(['AccountID']);

        foreach ($loans as $loan) {
            $loan->assignment()->whereNull('EndOn')->update(['EndOn' => Carbon::now(), 'ModifiedBy' => $actor->Id, 'Notes' => 'PERFORMING']);
        }

        //check non-extent and end assignment. Closed Loans
        $assignments = LoanAssignment::query()->whereNull('EndOn')->whereNotIn('AccountID', DebtProduct::query()->where('processDate', $dated)->select('AccountID'))->get();
        foreach ($assignments as $assignment) {
            if ($assignment instanceof LoanAssignment) {
                $assignment->update(['EndOn' => Carbon::now(), 'ModifiedBy' => $actor->Id, 'Notes' => 'Closed']);
            }
        }

        if ($loans->count() > 0 || $assignments->count() > 0) {
            $this->notifyUsers();
        }
    }

    public function notifyUsers(): void
    {
        $usersWithLoansToday = User::query()->whereHas('loansAssigned', function (Builder $query) {
            $query->whereBetween('t_LoanAssignments.EndOn', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()]);
        })->get();

        foreach ($usersWithLoansToday as $user) {
            $loans = $user->loansAssigned()->whereBetween('t_LoanAssignments.EndOn', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()])
                ->select('AccountID')->get('AccountID')->pluck('AccountID')->toArray();

            (new UserService($user))->sendEmail(
                subject: 'Loans Removed from Assignment ' . number_format(count($loans)) . ' Loan(s)',
                body: '<p>The following loans have been removed from your assignment:</p>
                   <p>  <strong>Removal Date:</strong> ' . now()->format('M d, Y') . '<br>
                     <strong>Loans:</strong> ' . implode(', ', $loans) . ' </p>
                    <p>This change has been processed due to either:</p>
                    <ul>
                        <li>The loan status changing to PERFORMING, or</li>
                        <li>The loan being closed</li>
                    </ul>
                    <p><em>This is an automated message. Please do not reply to this email.</em></p>',
                immediate: true,
            );
        }
    }
}
