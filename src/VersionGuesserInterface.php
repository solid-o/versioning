<?php

declare(strict_types=1);

namespace Solido\Versioning;

use Symfony\Component\HttpFoundation\Request;

interface VersionGuesserInterface
{
    /**
     * Guess the api version from the given request.
     */
    public function guess(Request $request, ?string $default): ?string;
}
