<?php

namespace App\Services;

use App\Models\Auth\User;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Services\ThirdParties\SupplierService;
use App\Services\ThirdParties\TenantService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegistrationService
{
    public function registerThirdParty(array $userData): ThirdPartyUser
    {
        return DB::transaction(function () use ($userData) {
            $systemUser = User::where('UserID', 'ERPSYS')->first();
            $systemUserId = $systemUser?->Id ?? 1;

            $defaultGender = \App\Models\Core\Approval\CodeDetail::where('CodeID', 'Gender')
                ->where('Value', 'M')
                ->first()
                ?? \App\Models\Core\Approval\CodeDetail::where('CodeID', 'Gender')->first();

            $user = ThirdPartyUser::create([
                'FirstName' => $userData['FirstName'],
                'LastName' => $userData['LastName'],
                'Email' => $userData['Email'],
                'Phone' => $userData['Phone'],
                'Password' => $userData['Password'],
                'Gender' => $defaultGender?->ID ?? 153,
                'IsActive' => false,
                'CreatedBy' => $systemUserId,
                'ModifiedBy' => $systemUserId,
            ]);

            if (! empty($userData['verification_base_url'])) {
                $user->verificationBaseUrl = $userData['verification_base_url'];
            }

            $this->sendThirdPartyVerificationEmail($user);

            return $user;
        });
    }

    public function sendThirdPartyVerificationEmail(ThirdPartyUser $user): void
    {
        $expiresTs = now()->addMinutes(config('auth.verification.expire', 60))->timestamp;
        $hash = sha1($user->getEmailForVerification());

        $baseUrl = route(
            'portal.auth.email.verify',
            ['id' => $user->getKey(), 'hash' => $hash],
            false
        );

        $signature = hash_hmac(
            'sha256',
            $baseUrl . '?expires=' . $expiresTs,
            config('app.key')
        );

        $backendVerifyUrl = $baseUrl . '?expires=' . $expiresTs . '&signature=' . $signature;

        $frontendBase = $user->verificationBaseUrl
            ?? config('app.frontend_url')
            ?? config('app.url');

        $frontendVerifyUrl = rtrim($frontendBase, '/') . '/verify-email?verify_url=' . urlencode($backendVerifyUrl);

        $body = '<p>Please click the button below to verify your email address.</p>'
            . '<p><a href="' . $frontendVerifyUrl . '" style="background-color:#2563eb;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;">Verify Email Address</a></p>'
            . '<p style="font-size:small;color:#666;margin-top:20px;">If the button above does not work, copy and paste the following link into your browser:<br>'
            . $frontendVerifyUrl . '</p>'
            . '<p>If you did not create an account, no further action is required.</p>';

        $actor = User::where('UserID', 'ERPSYS')->first() ?? User::first();

        \App\Services\CRMEmailService::createRaw(
            $actor,
            'Verify Email Address - ' . config('app.name'),
            $body,
            [['Name' => $user->fullName, 'Email' => $user->Email]],
            'EMAIL_VERIFICATION',
            (string) $user->getKey()
        )->send(true);
    }

    public function createThirdPartyForUser(ThirdPartyUser $user, array $thirdPartyData): ThirdParties
    {
        return DB::transaction(function () use ($user, $thirdPartyData) {
            $systemUser = User::where('UserID', 'ERPSYS')->first();
            $systemUserId = $systemUser?->Id ?? 1;

            $initialName = $thirdPartyData['ThirdPartyName']
                ?? ($thirdPartyData['FirstName'] . ' ' . $thirdPartyData['LastName'])
                ?? $thirdPartyData['Email'];

            $businessTypeId = $thirdPartyData['BusinessType']
                ?? \App\Models\Core\Approval\CodeDetail::where('CodeID', 'BusinessType')
                    ->where('Value', 'I')
                    ->value('ID')
                ?? \App\Models\Core\Approval\CodeDetail::where('CodeID', 'BusinessType')->value('ID')
                ?? 47;

            $countryId = $thirdPartyData['CountryId']
                ?? \App\Models\Core\Country::where('CountryCode', 'KE')->value('Id')
                ?? \App\Models\Core\Country::value('Id')
                ?? 1;

            $thirdParty = ThirdParties::create([
                'ThirdPartyName' => $initialName,
                'TradingName' => $thirdPartyData['TradingName'] ?? $initialName,
                'BusinessType' => $businessTypeId,
                'RegistrationNumber' => $thirdPartyData['RegistrationNumber'] ?? 'PENDING',
                'TaxPIN' => $thirdPartyData['TaxPIN'] ?? '',
                'VATNumber' => $thirdPartyData['VATNumber'] ?? '',
                'CountryId' => $countryId,
                'PhysicalAddress' => $thirdPartyData['PhysicalAddress'] ?? 'Pending Address',
                'Email' => $thirdPartyData['Email'] ?? null,
                'Phone' => $thirdPartyData['Phone'] ?? null,
                'Website' => $thirdPartyData['Website'] ?? null,
                'IsActive' => false,
                'ApprovalStatus' => 'P',
                'CreatedBy' => $systemUserId,
                'ModifiedBy' => $systemUserId,
            ]);

            $accountType = $thirdPartyData['accountType'] ?? 'supplier';
            $this->attachAccountType($thirdParty, $accountType, $user, $thirdPartyData);

            if (! empty($thirdPartyData['ThirdPartyType'])) {
                DB::table('t_ThirdPartyType_ThirdParties')->insert([
                    'TypeId' => $thirdPartyData['ThirdPartyType'],
                    'ThirdPartyId' => $thirdParty->Id,
                    'CreatedBy' => $systemUserId,
                    'ModifiedBy' => $systemUserId,
                    'CreatedOn' => now(),
                    'ModifiedOn' => now(),
                ]);
            }

            return $thirdParty;
        });
    }

    protected function attachAccountType(ThirdParties $thirdParty, string $accountType, ThirdPartyUser $user, array $data): void
    {
        match ($accountType) {
            'supplier' => SupplierService::createFromParty($thirdParty, $user),
            'tenant' => TenantService::createFromParty($thirdParty, $user),
            'customer' => $this->attachCustomerType($thirdParty, $user, $data),
            default => null,
        };
    }

    protected function attachCustomerType(ThirdParties $thirdParty, ThirdPartyUser $user, array $data): void
    {
        $thirdParty->types()->syncWithoutDetaching([6 => [
            'PartyType' => 'ThirdPartyId',
            'PartyID' => $thirdParty->Id,
            'CreatedBy' => $user->Id,
            'CreatedOn' => now(),
        ]]);

        $thirdParty->customerProfile()->updateOrCreate(
            ['ThirdPartyId' => $thirdParty->Id],
            [
                'Gender' => $data['Gender'] ?? null,
                'MaritalStatus' => $data['MaritalStatus'] ?? null,
                'Occupation' => $data['Occupation'] ?? null,
                'CreatedBy' => $user->Id,
                'ModifiedBy' => $user->Id,
            ]
        );
    }
}
