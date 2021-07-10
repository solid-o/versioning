<?php declare(strict_types=1);

namespace Solido\Versioning\Tests;

use Nyholm\Psr7\ServerRequest;
use Solido\Versioning\AcceptHeaderVersionGuesser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class AcceptHeaderVersionGuesserTest extends TestCase
{
    public function testShouldReturnDefaultIfAcceptHeaderIsNotPreset(): void
    {
        $guesser = new AcceptHeaderVersionGuesser();

        self::assertNull($guesser->guess(new Request(), null));
        self::assertEquals('20200514', $guesser->guess(new Request(), '20200514'));
        self::assertEquals('1.0', $guesser->guess(new Request(), '1.0'));

        self::assertNull($guesser->guess(new ServerRequest('GET', '/'), null));
        self::assertEquals('20200514', $guesser->guess(new ServerRequest('GET', '/'), '20200514'));
        self::assertEquals('1.0', $guesser->guess(new ServerRequest('GET', '/'), '1.0'));
    }

    public function testShouldReturnDefaultIfRequestObjectIsNotSupported(): void
    {
        $guesser = new AcceptHeaderVersionGuesser();

        self::assertNull($guesser->guess(new \stdClass(), null));
        self::assertEquals('20200514', $guesser->guess(new \stdClass(), '20200514'));
        self::assertEquals('1.0', $guesser->guess(new \stdClass(), '1.0'));
    }

    public function testShouldReturnTheVersionInAcceptHeader(): void
    {
        $guesser = new AcceptHeaderVersionGuesser();

        $request = new Request();
        $request->headers->set('Accept', 'application/json; version=20200514');

        self::assertEquals('20200514', $guesser->guess($request, null));

        $request = (new ServerRequest('GET', '/'))
            ->withHeader('Accept', 'application/json; version=20200514');

        self::assertEquals('20200514', $guesser->guess($request, null));
    }

    public function testShouldReturnTheBestVersion(): void
    {
        $guesser = new AcceptHeaderVersionGuesser();

        $request = new Request();
        $request->headers->set('Accept', 'application/json; version=20200514; q=0.4, text/xml; version=1.4; q=0.9');

        self::assertEquals('1.4', $guesser->guess($request, null));

        $request = (new ServerRequest('GET', '/'))
            ->withHeader('Accept', 'application/json; version=20200514; q=0.4, text/xml; version=1.4; q=0.9');

        self::assertEquals('1.4', $guesser->guess($request, null));
    }

    public function testShouldReturnDefaultValueOnInvalidAccept(): void
    {
        $guesser = new AcceptHeaderVersionGuesser();

        $request = new Request();
        $request->headers->set('Accept', 'barbar foo_bar-xxxx');

        self::assertEquals('1.8', $guesser->guess($request, '1.8'));

        $request = (new ServerRequest('GET', '/'))
            ->withHeader('Accept', 'barbar foo_bar-xxxx');

        self::assertEquals('1.8', $guesser->guess($request, '1.8'));
    }
}
