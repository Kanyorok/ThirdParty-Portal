<?php

namespace App\Repositories\ThirdParty;

use App\Enums\ThirdParty\ThirdPartyTypeEnum;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyType;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Repositories\ThirdParty\Contracts\ThirdPartyRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ThirdPartyRepository implements ThirdPartyRepositoryInterface
{
    protected const CACHE_PREFIX = 'third_party';
    protected const CACHE_TTL = 3600;

    public function findById(int $id): ?ThirdParties
    {
        return Cache::remember(
            $this->getCacheKey($id),
            self::CACHE_TTL,
            fn() => ThirdParties::find($id)
        );
    }

    public function findByIdWithRelations(int $id, array $relations = []): ?ThirdParties
    {
        $cacheKey = $this->getCacheKey($id, 'with_' . md5(json_encode($relations)));

        return Cache::remember(
            $cacheKey,
            self::CACHE_TTL,
            fn() => ThirdParties::with($relations)->find($id)
        );
    }

    public function findByUserId(int $userId): ?ThirdParties
    {
        return Cache::remember(
            $this->getCacheKey('user_' . $userId),
            self::CACHE_TTL,
            function () use ($userId) {
                $user = ThirdPartyUser::find($userId);
                return $user?->ThirdPartyId ? ThirdParties::find($user->ThirdPartyId) : null;
            }
        );
    }

    public function create(array $data): ThirdParties
    {
        $thirdParty = ThirdParties::create($data);
        $this->clearCache($thirdParty->Id);

        return $thirdParty;
    }

    public function update(ThirdParties $thirdParty, array $data): ThirdParties
    {
        $thirdParty->update($data);
        $this->clearCache($thirdParty->Id);

        return $thirdParty->fresh();
    }

    public function delete(ThirdParties $thirdParty): bool
    {
        $result = $thirdParty->delete();
        $this->clearCache($thirdParty->Id);

        return $result;
    }

    public function hasProfileType(ThirdParties $thirdParty, ThirdPartyTypeEnum $type): bool
    {
        return $thirdParty->types()
            ->where('Code', 'like', $type->getCode() . '%')
            ->exists();
    }

    public function getProfileTypes(ThirdParties $thirdParty): Collection
    {
        return $thirdParty->types;
    }

    public function attachProfileType(
        ThirdParties $thirdParty,
        ThirdPartyTypeEnum $type,
        int $partyId,
        int $userId
    ): void {
        $thirdPartyType = ThirdPartyType::where('Code', 'like', $type->getCode() . '%')->first();

        if ($thirdPartyType) {
            $thirdParty->types()->syncWithoutDetaching([
                $thirdPartyType->TypeId => [
                    'PartyType' => $type->getPrimaryKeyName(),
                    'PartyID' => $partyId,
                    'CreatedBy' => $userId,
                    'ModifiedBy' => $userId,
                ]
            ]);

            $this->clearCache($thirdParty->Id);
        }
    }

    public function detachProfileType(ThirdParties $thirdParty, ThirdPartyTypeEnum $type): void
    {
        $thirdPartyType = ThirdPartyType::where('Code', 'like', $type->getCode() . '%')->first();

        if ($thirdPartyType) {
            $thirdParty->types()->detach($thirdPartyType->TypeId);
            $this->clearCache($thirdParty->Id);
        }
    }

    public function getUserProfiles(ThirdPartyUser $user): ?ThirdParties
    {
        if (!$user->ThirdPartyId) {
            return null;
        }

        return $this->findByIdWithRelations(
            $user->ThirdPartyId,
            ['types', 'supplierMaster', 'tenants']
        );
    }

    protected function getCacheKey(int|string $id, string $suffix = ''): string
    {
        return self::CACHE_PREFIX . ":{$id}" . ($suffix ? ":{$suffix}" : '');
    }

    protected function clearCache(int $id): void
    {
        Cache::forget($this->getCacheKey($id));
    }
}
