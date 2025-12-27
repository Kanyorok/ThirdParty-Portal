<?php

namespace App\Repositories\ThirdParty\Contracts;

use App\Enums\ThirdParty\ThirdPartyTypeEnum;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyUser;
use Illuminate\Database\Eloquent\Collection;

interface ThirdPartyRepositoryInterface
{
    public function findById(int $id): ?ThirdParties;

    public function findByIdWithRelations(int $id, array $relations = []): ?ThirdParties;

    public function findByUserId(int $userId): ?ThirdParties;

    public function create(array $data): ThirdParties;

    public function update(ThirdParties $thirdParty, array $data): ThirdParties;

    public function delete(ThirdParties $thirdParty): bool;

    public function hasProfileType(ThirdParties $thirdParty, ThirdPartyTypeEnum $type): bool;

    public function getProfileTypes(ThirdParties $thirdParty): Collection;

    public function attachProfileType(
        ThirdParties $thirdParty,
        ThirdPartyTypeEnum $type,
        int $partyId,
        int $userId
    ): void;

    public function detachProfileType(ThirdParties $thirdParty, ThirdPartyTypeEnum $type): void;

    public function getUserProfiles(ThirdPartyUser $user): ?ThirdParties;
}
