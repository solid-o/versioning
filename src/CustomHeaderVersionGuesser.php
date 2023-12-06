<?php

declare(strict_types=1);

namespace Solido\Versioning;

use Solido\Common\AdapterFactory;
use Solido\Common\AdapterFactoryInterface;
use Solido\Common\Exception\UnsupportedRequestObjectException;

class CustomHeaderVersionGuesser implements VersionGuesserInterface
{
    private AdapterFactoryInterface $adapterFactory;

    public function __construct(private string $headerName = 'X-API-Version', AdapterFactoryInterface|null $adapterFactory = null)
    {
        $this->adapterFactory = $adapterFactory ?? new AdapterFactory();
    }

    public function guess(object $request, string|null $default): string|null
    {
        try {
            $adapter = $this->adapterFactory->createRequestAdapter($request);
        } catch (UnsupportedRequestObjectException) {
            return $default;
        }

        $header = $adapter->getHeader($this->headerName)[0] ?? null;

        return $header ?? $default;
    }
}
