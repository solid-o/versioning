<?php

declare(strict_types=1);

namespace Solido\Versioning;

interface VersionGuesserInterface
{
    /**
     * Guess the api version from the given request.
     */
    public function guess(object $request, string|null $default): string|null;
}
