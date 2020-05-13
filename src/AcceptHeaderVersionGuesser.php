<?php

declare(strict_types=1);

namespace Solido\Versioning;

use Negotiation\Exception\InvalidMediaType;
use Solido\Versioning\Negotiation\VersionAwareNegotiator;
use Symfony\Component\HttpFoundation\Request;
use function assert;
use function is_string;

class AcceptHeaderVersionGuesser implements VersionGuesserInterface
{
    /** @var string[] */
    private array $priorities;

    /**
     * @param string[] $priorities
     */
    public function __construct(array $priorities = ['*/*'])
    {
        $this->priorities = (static fn (string ...$v) => $v)(...$priorities);
    }

    public function guess(Request $request, ?string $default): ?string
    {
        if (! $request->headers->has('Accept')) {
            return $default;
        }

        $requestHeader = $request->headers->get('Accept', '');
        assert(is_string($requestHeader));

        $negotiator = new VersionAwareNegotiator();
        try {
            $header = $negotiator->getBest($requestHeader, $this->priorities);
        } catch (InvalidMediaType $exception) {
            return $default;
        }

        if ($header === null) {
            return $default;
        }

        return $header->getVersion() ?? $default;
    }
}
