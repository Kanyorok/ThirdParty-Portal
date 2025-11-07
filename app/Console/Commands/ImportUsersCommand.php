<?php

namespace App\Console\Commands;

use App\Enums\Core\ExtensionsEnum;
use App\Enums\Employee\GenderEnum;
use App\Helpers\SystemHelper;
use App\Models\Auth\Team;
use App\Models\Auth\User;
use App\Models\BR\Branch;
use App\Models\BR\Client;
use App\Services\HRM\UserService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Str;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ImportUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-users-command
    {file : Where is the csv with user list}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("The file $file does not exist.");
            return CommandAlias::FAILURE;
        }

        if (mime_content_type($file) !== ExtensionsEnum::Csv->getMimeType()) {
            $this->error("The file $file is not a csv file.");
            return CommandAlias::FAILURE;
        }

        $data = array_map('str_getcsv', file($file));
        $headers = array_shift($data);

        $requiredHeaders = [
                            "Name",
                            "Title",
                            "Department",
                            "Email Address",
                            "Phone Number",
                            "Member Number",
                            "USERNAME",
                           ];

        if (array_diff($requiredHeaders, $headers)) {
            $this->error("The provided CSV file is missing some required fields: " . implode(', ', array_diff($requiredHeaders, $headers)));
            return 1;
        }

        $branch = Branch::first();
        $actor = SystemHelper::user();
        $Role = Role::query()->createOrFirst(['name' => 'Default'], [
                                                                     'CreatedBy'  => $actor->Id,
                                                                     'ModifiedBy' => $actor->Id,
                                                                    ]);
        $success = 0;
        $failed = 0;

        $sendEmails = $this->choice(
            'Do you want to send welcome email?',
            [
             'YES',
             'NO',
            ],
            'NO'
        ) === "YES";

        foreach ($data as $row) {
            $userData = array_combine($headers, $row);

            if (!filter_var($userData['Email Address'], FILTER_VALIDATE_EMAIL)) {
                $this->warn('Invalid email address: ' . $userData['Email Address'] . ', Skipping. ' . $userData['Name']);
                $failed++;
                continue;
            }
            $memberNo = Str::padLeft($userData['Member Number'], 7, '0');
            if (!Client::query()->where('ClientID', $memberNo)->exists()) {
                $this->warn('Invalid member No : ' . $memberNo . ', Skipping. ' . $userData['Name']);
                $failed++;
                continue;
            }

            $user = User::query()->withTrashed()
                ->where('Email', Str::lower($userData['Email Address']))
                ->orWhere('Phone', $userData['Phone Number'])
                ->orWhere('UserID', $userData['USERNAME'])
                ->orWhere('ClientID', $memberNo)
                ->first();
            if ($user instanceof User) {
                $this->warn("User with email {$userData['Email Address']} or phone number {$userData['Phone Number']} or username {$userData['USERNAME']} or memberNo $memberNo already exists. Skipping.");
                $failed++;
                continue;
            }

            // Create or find the department as a new team
            $service = UserService::create(
                $branch,
                $userData['USERNAME'],
                $userData['Name'],
                Str::lower($userData['Email Address']),
                $userData['Phone Number'],
                GenderEnum::Other,
                $actor,
                'Imported from File',
                '<p>Best Regards<br>' . $userData['Name'] . '<br>Imarisha Sacco, ' . $userData['Title'] . '<br></p>'
            )->setRole($Role)->syncBR(true);

            $service->user->update(['ClientID' => $memberNo]);

            if ($sendEmails) {
                $service->welcomeEmail();
            }

            $team = Team::firstOrCreate(
                ['Name' => $userData['Department']],
                [
                 'Email'      => Str::lower($userData['Email Address']),
                 'Notes'      => "{$userData['Department']}",
                 'CreatedBy'  => $actor->Id,
                 'ModifiedBy' => $actor->Id,
                ]
            );


            DB::table('t_TeamUser')->insert([
                                             'TeamId'     => $team->TeamID,
                                             'UserId'     => $service->user->Id,
                                             'CreatedBy'  => $actor->Id,
                                             'ModifiedBy' => $actor->Id,
                                             'CreatedOn'  => now(),
                                             'ModifiedOn' => now(),
                                            ]);
            $this->info("User {$userData['Name']} has been successfully imported.");
            $success++;
        }

        // Display the analytics
        $this->info("Import Process Complete.");
        $this->info("Total Success: " . number_format($success));
        $this->info("Total Failed: " . number_format($failed));
        return CommandAlias::SUCCESS;
    }
}
