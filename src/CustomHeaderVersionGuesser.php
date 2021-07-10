<?php

declare(strict_types=1);

namespace Solido\Versioning;

use Solido\Common\AdapterFactory;
use Solido\Common\AdapterFactoryInterface;
use Solido\Common\Exception\UnsupportedRequestObjectException;

class CustomHeaderVersionGuesser implements VersionGuesserInterface
{
    private string $headerName;
    private AdapterFactoryInterface $adapterFactory;

    public function __construct(string $headerName = 'X-API-Version', ?AdapterFactoryInterface $adapterFactory = null)
    {
        $this->headerName = $headerName;
        $this->adapterFactory = $adapterFactory ?? new AdapterFactory();
    }

    public function guess(object $request, ?string $default): ?string
    {
        try {
            $adapter = $this->adapterFactory->createRequestAdapter($request);
        } catch (UnsupportedRequestObjectException $e) {
            return $default;
        }

        $header = $adapter->getHeader($this->headerName)[0] ?? null;

        return $header ?? $default;
    }
}
