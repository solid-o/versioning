<?php

declare(strict_types=1);

namespace Solido\Versioning;

use Symfony\Component\HttpFoundation\Request;

class CustomHeaderVersionGuesser implements VersionGuesserInterface
{
    private string $headerName;

    public function __construct(string $headerName = 'X-API-Version')
    {
        $this->headerName = $headerName;
    }

    public function guess(Request $request, ?string $default): ?string
    {
        if (! $request->headers->has($this->headerName)) {
            return $default;
        }

        return $request->headers->get($this->headerName) ?? $default;
    }
}
