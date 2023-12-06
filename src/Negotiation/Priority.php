<?php

declare(strict_types=1);

namespace Solido\Versioning\Negotiation;

use Negotiation\AcceptHeader;
use Negotiation\BaseAccept;
use Negotiation\Exception\InvalidMediaType;

use function count;
use function explode;

class Priority extends BaseAccept implements AcceptHeader
{
    private string $basePart;
    private string $subPart;
    private string|null $version;

    public function __construct(string $value)
    {
        parent::__construct($value);

        $parts = explode('/', $this->type);

        if (count($parts) !== 2 || ! $parts[0] || ! $parts[1]) {
            throw new InvalidMediaType();
        }

        $this->basePart = $parts[0];
        $this->subPart = $parts[1];
    }

    public function getBasePart(): string
    {
        return $this->basePart;
    }

    public function getSubPart(): string
    {
        return $this->subPart;
    }

    public function setVersion(string|null $version): void
    {
        $this->version = $version;
    }

    public function getVersion(): string|null
    {
        return $this->version;
    }
}
