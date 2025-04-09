<?php

namespace App\Services\Feedback;

use App\Enums\TonalityEnum;
use App\Models\BR\Account;
use App\Models\BR\Branch;
use App\Models\BR\Client;
use App\Models\Lead;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Str;
use Sentiment\Analyzer;

class ReviewService
{

    public function __construct(public Review $review)
    {
    }

    public static function client(string $ClientID, int $rate, string $content, string $source, User $actor, string $sourceID = null, string $branch = null): ReviewService
    {
        if (is_null($branch)) {
            $ac = Account::query()->where('ClientID', $ClientID)->latest('AccountID')->first(['OurBranchID', 'AccountID']);
            if ($ac instanceof Account) {
                $branch = $ac->OurBranchID;
            }
        }
        return self::_create($rate, $content, Client::getPrimaryKey(), $source, $actor, $ClientID, $sourceID, $branch);
    }

    private static function _create(int $rate, string $content, string $party, string $source, User $actor, string $partyID = null, string $sourceID = null, string $branch = null): ReviewService
    {
        $review = new Review();
        $review->fill([
            'BranchID' => $branch,
            'Party' => $party,
            'PartyID' => $partyID,
            'Source' => $source,
            'SourceID' => $sourceID,
            'Rating' => $rate,
            'Tonality' => TonalityEnum::Unknown->value,
            'Content' => $content,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ])->save();

        $service = new self($review);
        if (is_null($branch)) {
            $service->fixBranch();
        }
        return $service->sentiment();
    }

    public function sentiment(): static
    {
        if ($this->review->Tonality->value === TonalityEnum::Unknown->value && !empty($this->review->Content)) {
            try {
                $output_text = (new Analyzer())->getSentiment($this->review->Content);
                $sentiment = null;
                if ($output_text['neg'] > 0.49) {
                    $sentiment = TonalityEnum::Negative;
                }

                if ($output_text['neu'] > 0.49) {
                    $sentiment = TonalityEnum::Neutral;
                }
                if ($output_text['pos'] > 0.49) {
                    $sentiment = TonalityEnum::Positive;
                }

                if (!is_null($sentiment)) {
                    $this->review->update([
                        'Tonality' => $sentiment,
                    ]);
                }
            } catch (\Exception) {
            }
        }

        return $this;
    }

    public static function lead(string $LeadId, int $rate, string $content, string $source, User $actor, string $sourceID = null, string $branch = null): ReviewService
    {
        return self::_create($rate, $content, Lead::getPrimaryKey(), $source, $actor, $LeadId, $sourceID, $branch);
    }

    public static function anonymous(string $name, int $rate, string $content, string $source, User $actor, string $sourceID = null, string $branch = null): ReviewService
    {
        return self::_create($rate, $content, $name, source: $source, actor: $actor, sourceID: $sourceID, branch: $branch);
    }

    public function getRate(string $attr): string
    {
        return '<img src="' . asset('assets/img/mood/' . $this->review->Rating) . '.png" ' . $attr . ' title="' . $this->review->Rating . '/5" />';
    }

    private function fixBranch(): void
    {
        if (empty($this->review->Content)) {
            return;
        }

        if (!empty($this->review->BranchID)) {
            return;
        }

        $str = Str::of($this->review->Content);
        foreach (Branch::all(['BranchName', 'OurBranchID']) as $branch) {
            if ($str->contains([$branch->BranchName, explode(' ', $branch->BranchName)[0]], true)) {
                $this->review->update([
                    'BranchID' => $branch->OurBranchID,
                ]);
                break;
            }
        }
    }
}
