<?php

namespace App\Services\DMS;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TextCleanerService
{
    protected string $text;
    protected Collection $tokens;
    protected Collection $keywords;
    protected Collection $customStopWords;

    public function __construct(string $text)
    {
        $this->text = $text;
        $this->tokens = collect();
        $this->keywords = collect();
        $this->customStopWords = collect();

        $this->clean();
    }

    protected function clean(): void
    {
        if ($this->text === '') {
            return;
        }
        $normalized = Str::of($this->text)
            ->lower()->replace(["’s", "'s"], ' ')
            ->replaceMatches('/[^\p{L}\p{N}\'’]+/u', ' ') // keep letters, numbers, apostrophes
            ->__toString();

        $this->tokens = collect(preg_split('/\s+/', $normalized, -1, PREG_SPLIT_NO_EMPTY))
            ->filter(fn ($token) => Str::length($token) > 2);

        $combined = $this->stopwords()->merge($this->customStopWords)->unique();

        $this->keywords = $this->tokens->filter(function ($word) use ($combined) {
            return ! $combined->contains($word) && ! $this->punctuations()->contains($word);
        })->unique()->values();
    }

    protected function stopwords(): Collection
    {
        $file = storage_path('stopwords.txt');
        if (file_exists($file)) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            return collect($lines)->map(fn ($line) => Str::lower(trim($line)));
        }

        return collect();
    }

    /**
     * Punctuation characters (for completeness; already removed in normalization)
     */
    protected function punctuations(): Collection
    {
        return collect([
            '.', ',', ';', ':', '!', '?', '(', ')', '[', ']', '{', '}', '-', '—', '–', '_',
            '"', "'", '“', '”', '‘', '’', '`', '~', '/', '\\', '|', '*', '&', '^', '%', '$', '#', '@', '<', '>', '=', '+',
        ]);
    }

    public function addCustomWords(array $words): self
    {
        $this->customStopWords = $this->customStopWords->merge(
            collect($words)->map(fn ($w) => Str::lower($w))
        );

        $this->clean();

        return $this;
    }

    public function getShuffledText(): string
    {
        return $this->keywords->shuffle()->implode(' ');
    }

    public function getKeywords(): array
    {
        return $this->keywords->toArray();
    }
}
