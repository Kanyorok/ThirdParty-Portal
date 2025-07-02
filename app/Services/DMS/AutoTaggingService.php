<?php

namespace App\Services\DMS;

use App\Enums\Core\VisibilityEnum;
use App\Enums\DMS\ContentEnum;
use App\Enums\DMS\StringComparisonEnum;
use App\Models\Auth\User;
use App\Models\DMS\DMSTags;
use App\Models\DMS\Document;
use App\Models\DMS\DocumentTaggingRules;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AutoTaggingService
{
    public function __construct(protected Document $document, protected string $content)
    {
    }

    public function tag(User $actor, ContentEnum $contentEnum): static
    {
        $tags = DocumentTaggingRules::query()
            ->where('Content', $contentEnum->value)
            ->whereIn('TagId', DMSTags::query()->where('Visibility', VisibilityEnum::Public->value)
                ->orWhere(function (Builder $query) use ($actor) {
                    $query->where('Visibility', VisibilityEnum::Private->value)
                        ->where('t_DMSTags.CreatedBy', $actor->Id);
                })->select('t_DMSTags.Id'))
            ->with('tag')
            ->get();

        $matchingTagIds = collect();
        $description = 'auto added tags: ';
        $date = now();
        $documentTags = (new DocumentService($this->document))->tags($actor)->select('t_DMSTags.Id')->pluck('Id')->toArray();
        foreach ($tags as $rule) {
            if (in_array($rule->TagId, $documentTags, true)) {
                continue;
            }

            if ($this->contentMatchesRule($this->content, $rule)) {
                $matchingTagIds->add([
                    'DocId' => $this->document->Id,
                    'TagId' => $rule->TagId,
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                    'CreatedOn' => $date,
                    'ModifiedOn' => $date
                ]);
                $description .= $rule->tag->Name . ', ';
            }
        }

        if ($matchingTagIds->isNotEmpty()) {
            Str::of($description)->trim()->trim(',')->trim()->append('.');

            DB::table('t_DocumentTags')->insert($matchingTagIds->toArray());

            activity()->causedBy($actor)->performedOn($this->document)->event('create')->log($description);
        }

        return $this;
    }

    private function contentMatchesRule(string $content, DocumentTaggingRules $rule): bool
    {
        $comparison = $rule->Comparison;
        $value = $rule->Value;

        return match ($comparison) {
            StringComparisonEnum::Exact => $content === $value,
            StringComparisonEnum::NotExact => $content !== $value,
            StringComparisonEnum::Contains => str_contains($content, $value),
            StringComparisonEnum::NotContains => !str_contains($content, $value),
            StringComparisonEnum::StartsWith => str_starts_with($content, $value),
            StringComparisonEnum::EndsWith => str_ends_with($content, $value),
        };
    }
}
