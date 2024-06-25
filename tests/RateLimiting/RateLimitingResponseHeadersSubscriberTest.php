<?php
declare(strict_types=1);

namespace Headsnet\SymfonyToolsBundle\Tests\RateLimiting;

use Headsnet\SymfonyToolsBundle\RateLimiting\RateLimitingResponseHeadersSubscriber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimit;

#[CoversClass(RateLimitingResponseHeadersSubscriber::class)]
class RateLimitingResponseHeadersSubscriberTest extends TestCase
{
    #[Test]
    public function get_subscribed_events_returns_correct_event_and_priority(): void
    {
        $expected = [
            KernelEvents::RESPONSE => ['onKernelResponse', 1024],
        ];

        $this->assertSame($expected, RateLimitingResponseHeadersSubscriber::getSubscribedEvents());
    }

    #[Test]
    public function does_nothing_when_headers_not_enabled(): void
    {
        $response = new Response();
        $event = $this->createEvent($response);
        $sut = new RateLimitingResponseHeadersSubscriber(false);

        $sut->onKernelResponse($event);

        $this->assertFalse($response->headers->has('Rate-Limit-Remaining'));
        $this->assertFalse($response->headers->has('Rate-Limit-Reset'));
        $this->assertFalse($response->headers->has('Rate-Limit-Limit'));
    }

    #[Test]
    public function does_nothing_when_rate_limit_not_present(): void
    {
        $response = new Response();
        $event = $this->createEvent($response);
        $sut = new RateLimitingResponseHeadersSubscriber(true);

        $sut->onKernelResponse($event);

        $this->assertFalse($response->headers->has('Rate-Limit-Remaining'));
        $this->assertFalse($response->headers->has('Rate-Limit-Reset'));
        $this->assertFalse($response->headers->has('Rate-Limit-Limit'));
    }

    #[Test]
    public function adds_headers_when_rate_limit_present(): void
    {
        $rateLimit = $this->createMock(RateLimit::class);
        $rateLimit->method('getRemainingTokens')->willReturn(5);
        $rateLimit->method('getRetryAfter')->willReturn(new \DateTimeImmutable('+60 seconds'));
        $rateLimit->method('getLimit')->willReturn(10);
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();
        $response = new Response();
        $request->attributes->set('rate_limit', $rateLimit);
        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
        $sut = new RateLimitingResponseHeadersSubscriber(true);

        $sut->onKernelResponse($event);

        $this->assertSame(5, (int) $response->headers->get('Rate-Limit-Remaining'));
        $this->assertSame(-60, (int) $response->headers->get('Rate-Limit-Reset'));
        $this->assertSame(10, (int) $response->headers->get('Rate-Limit-Limit'));
    }

    private function createEvent(Response $response): ResponseEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();

        return new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
    }
}
