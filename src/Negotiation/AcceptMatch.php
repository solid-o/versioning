<?php

declare(strict_types=1);

namespace Solido\Versioning\Negotiation;

final class AcceptMatch
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

    public static function compare(AcceptMatch $a, AcceptMatch $b): int
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
     * @param AcceptMatch[] $carry reduced array
     * @param AcceptMatch   $match match to be reduced
     *
     * @return AcceptMatch[]
     */
    public static function reduce(array $carry, AcceptMatch $match): array
    {
        if (! isset($carry[$match->index]) || $carry[$match->index]->score < $match->score) {
            $carry[$match->index] = $match;
        }

        return $carry;
    }
}
