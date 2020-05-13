<?php

declare(strict_types=1);

namespace Solido\Versioning\Negotiation;

final class Match
{
    public float $quality;
    public int $score;

    /** @var string|int */
    public $index;

    /** @var string|int */
    public $headerIndex = null;

    /**
     * @param string|int $index
     */
    public function __construct(float $quality, int $score, $index)
    {
        $this->quality = $quality;
        $this->score   = $score;
        $this->index   = $index;
    }

    public static function compare(Match $a, Match $b): int
    {
        if ($a->quality !== $b->quality) {
            return $a->quality > $b->quality ? -1 : 1;
        }

        if ($a->index !== $b->index) {
            return $a->index > $b->index ? 1 : -1;
        }

        return 0;
    }

    /**
     * @param Match[] $carry reduced array
     * @param Match $match match to be reduced
     *
     * @return Match[]
     */
    public static function reduce(array $carry, Match $match): array
    {
        if (! isset($carry[$match->index]) || $carry[$match->index]->score < $match->score) {
            $carry[$match->index] = $match;
        }

        return $carry;
    }
}
