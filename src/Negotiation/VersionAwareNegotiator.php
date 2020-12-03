<?php

declare(strict_types=1);

namespace Solido\Versioning\Negotiation;

use Negotiation\Accept;
use Negotiation\Exception\InvalidArgument;
use Negotiation\Exception\InvalidHeader;
use function array_filter;
use function array_intersect_assoc;
use function array_map;
use function array_reduce;
use function array_shift;
use function array_values;
use function assert;
use function count;
use function Safe\preg_match_all;
use function Safe\sprintf;
use function Safe\usort;
use function strcasecmp;

class VersionAwareNegotiator
{
    /**
     * Build an Accept object for header.
     */
    protected function acceptFactory(string $header): Accept
    {
        return new Accept($header);
    }

    /**
     * Build a Priority object for priority.
     */
    public function priorityFactory(string $priority): Priority
    {
        $p = new Priority($priority);

        $params = $p->getParameters();
        if (isset($params['version'])) {
            throw new InvalidHeader("Priority cannot have a 'version' parameter");
        }

        return $p;
    }

    /**
     * @param string $header     a string containing an `Accept|Accept-*` header
     * @param string[] $priorities a set of server priorities
     *
     * @return Priority|null best matching type
     */
    public function getBest(string $header, array $priorities): ?Priority
    {
        if (empty($priorities)) {
            throw new InvalidArgument('A set of server priorities should be given.');
        }

        if (! $header) {
            throw new InvalidArgument('The header string should not be empty.');
        }

        $headers = $this->parseHeader($header);
        $headers = array_map([$this, 'acceptFactory'], $headers);
        $priorities = array_map([$this, 'priorityFactory'], $priorities);

        $matches = $this->findMatches($headers, $priorities);
        $specificMatches = array_reduce($matches, AcceptMatch::class . '::reduce', []);

        usort($specificMatches, AcceptMatch::class . '::compare');

        $match = array_shift($specificMatches);

        if ($match === null) {
            return null;
        }

        $priority = $priorities[$match->index];
        assert($priority instanceof Priority);
        $priority->setVersion($headers[$match->headerIndex]->getParameter('version'));

        return $priority;
    }

    /**
     * @param string|int $index
     * @param string|int $headerIndex
     */
    protected function match(Accept $accept, Priority $priority, $index, $headerIndex): ?AcceptMatch
    {
        $ab = $accept->getBasePart();
        $pb = $priority->getBasePart();

        $as = $accept->getSubPart();
        $ps = $priority->getSubPart();

        $acceptParams = $accept->getParameters();
        unset($acceptParams['version']);

        $intersection = array_intersect_assoc($acceptParams, $priority->getParameters());

        $baseEqual = $pb === '*' || strcasecmp($ab, $pb) === 0;
        $subEqual = $ps === '*' || strcasecmp($as, $ps) === 0;

        if (($ab === '*' || $baseEqual) && ($as === '*' || $subEqual) && count($intersection) === count($acceptParams)) {
            $score = 100 * $baseEqual + 10 * $subEqual + count($intersection);
            if ($pb === '*' && $ps === '*') {
                $score += (int) ($accept->getQuality() * 100);
            }

            $match = new AcceptMatch($accept->getQuality(), $score, $index);
            $match->headerIndex = $headerIndex;

            return $match;
        }

        return null;
    }

    /**
     * @param string $header a string that contains an `Accept*` header
     *
     * @return string[]
     */
    private function parseHeader(string $header): array
    {
        $res = preg_match_all('/(?:[^,"]++(?:"[^"]*+")?)+[^,"]*+/', $header, $matches);
        if (! $res) {
            throw new InvalidHeader(sprintf('Failed to parse accept header: "%s"', $header));
        }

        return array_values(array_filter(array_map('trim', $matches[0])));
    }

    /**
     * @param Accept[] $headerParts
     * @param Priority[] $priorities  Configured priorities
     *
     * @return AcceptMatch[] Headers matched
     */
    private function findMatches(array $headerParts, array $priorities): array
    {
        $matches = [];
        foreach ($priorities as $index => $p) {
            foreach ($headerParts as $hIndex => $h) {
                $match = $this->match($h, $p, $index, $hIndex);
                if ($match === null) {
                    continue;
                }

                $matches[] = $match;
            }
        }

        return $matches;
    }
}
