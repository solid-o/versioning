<?php

declare(strict_types=1);

namespace Solido\Versioning;

use Negotiation\Exception\InvalidMediaType;
use Solido\Common\AdapterFactory;
use Solido\Common\AdapterFactoryInterface;
use Solido\Common\Exception\UnsupportedRequestObjectException;
use Solido\Versioning\Negotiation\VersionAwareNegotiator;

class AcceptHeaderVersionGuesser implements VersionGuesserInterface
{
    /** @var string[] */
    private array $priorities;
    private AdapterFactoryInterface $adapterFactory;

    /**
     * @param string[] $priorities
     */
    public function __construct(array $priorities = ['*/*'], ?AdapterFactoryInterface $adapterFactory = null)
    {
        $this->priorities = (static fn (string ...$v) => $v)(...$priorities);
        $this->adapterFactory = $adapterFactory ?? new AdapterFactory();
    }

    public function guess(object $request, ?string $default): ?string
    {
        try {
            $adapter = $this->adapterFactory->createRequestAdapter($request);
        } catch (UnsupportedRequestObjectException $e) {
            return $default;
        }

        $acceptHeader = $adapter->getHeader('Accept')[0] ?? null;
        if ($acceptHeader === null) {
            return $default;
        }

        $negotiator = new VersionAwareNegotiator();
        try {
            $header = $negotiator->getBest($acceptHeader, $this->priorities);
        } catch (InvalidMediaType $exception) {
            return $default;
        }

        if ($header === null) {
            return $default;
        }

        return $header->getVersion() ?? $default;
    }
}
