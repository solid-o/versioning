<?php declare(strict_types=1);

namespace Solido\Versioning\Tests;

use PHPUnit\Framework\TestCase;
use Solido\Versioning\CustomHeaderVersionGuesser;
use Symfony\Component\HttpFoundation\Request;

class CustomHeaderVersionGuesserTest extends TestCase
{
    public function testShouldReturnDefaultIfAcceptHeaderIsNotPreset(): void
    {
        $guesser = new CustomHeaderVersionGuesser();

        self::assertNull($guesser->guess(new Request(), null));
        self::assertEquals('20200514', $guesser->guess(new Request(), '20200514'));
        self::assertEquals('1.0', $guesser->guess(new Request(), '1.0'));
    }

    public function testShouldReturnTheVersionInCustomHeader(): void
    {
        $guesser = new CustomHeaderVersionGuesser();

        $request = new Request();
        $request->headers->set('x-api-version', '20200514');

        self::assertEquals('20200514', $guesser->guess($request, null));
    }
}
