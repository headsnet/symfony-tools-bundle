<?php
declare(strict_types=1);

namespace Headsnet\SymfonyToolsBundle\Tests\RateLimiting;

use Headsnet\SymfonyToolsBundle\RateLimiting\ApplyRateLimitingSubscriber;
use Headsnet\SymfonyToolsBundle\Tests\RateLimiting\Fixtures\FakeController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

#[CoversClass(ApplyRateLimitingSubscriber::class)]
class ApplyRateLimitingSubscriberTest extends TestCase
{
    #[Test]
    public function get_subscribed_events_returns_correct_event_and_priority(): void
    {
        $expected = [
            KernelEvents::CONTROLLER => ['onKernelController', 1024],
        ];

        $this->assertSame($expected, ApplyRateLimitingSubscriber::getSubscribedEvents());
    }

    #[Test]
    public function does_nothing_when_rate_limiter_disabled(): void
    {
        [$request, $event] = $this->createControllerEvent();
        $sut = new ApplyRateLimitingSubscriber([], false);

        $sut->onKernelController($event);

        $this->assertNull($request->attributes->get('rate_limit'));
    }

    #[Test]
    public function does_nothing_when_not_main_request(): void
    {
        [$request, $event] = $this->createControllerEvent();
        $sut = new ApplyRateLimitingSubscriber([], true);

        $sut->onKernelController($event);

        $this->assertNull($request->attributes->get('rate_limit'));
    }

    #[Test]
    public function does_nothing_when_controller_not_in_map(): void
    {
        [$request, $event] = $this->createControllerEvent();
        $sut = new ApplyRateLimitingSubscriber([], true);

        $sut->onKernelController($event);

        $this->assertNull($request->attributes->get('rate_limit'));
    }

    #[Test]
    public function applies_rate_limiting_if_attribute_is_specified(): void
    {
        [$request, $event] = $this->createControllerEvent();
        $sut = new ApplyRateLimitingSubscriber($this->getRateLimiterClassMap());

        $sut->onKernelController($event);

        $rateLimit = $request->attributes->get('rate_limit');
        $this->assertInstanceOf(RateLimit::class, $rateLimit);
        $this->assertTrue($rateLimit->isAccepted());
    }

    #[Test]
    public function ensure_rate_limiting_sets_rate_limit_attribute(): void
    {
        [$request, $event] = $this->createControllerEvent();
        $sut = new ApplyRateLimitingSubscriber($this->getRateLimiterClassMap());

        $sut->onKernelController($event);

        $rateLimit = $request->attributes->get('rate_limit');
        $this->assertInstanceOf(RateLimit::class, $rateLimit);
        $this->assertTrue($rateLimit->isAccepted());
    }

    /**
     * @return array{Request, ControllerEvent}
     */
    private function createControllerEvent(): array
    {
        $request = new Request();
        $request->attributes->set('_controller', FakeController::class . '::__invoke');
        $event = new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            function () {},
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        return [$request, $event];
    }

    /**
     * @return array<string, RateLimiterFactory>
     */
    private function getRateLimiterClassMap(): array
    {
        $limiter = new RateLimiterFactory(
            [
                'id' => 'test',
                'policy' => 'token_bucket',
                'limit' => 10,
                'rate' => [
                    'interval' => '1 minute',
                ],
            ],
            new InMemoryStorage()
        );

        return [
            FakeController::class . '::__invoke' => $limiter,
        ];
    }
}
