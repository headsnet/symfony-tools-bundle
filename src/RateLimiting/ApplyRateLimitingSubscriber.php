<?php
declare(strict_types=1);

namespace Headsnet\SymfonyToolsBundle\RateLimiting;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

readonly class ApplyRateLimitingSubscriber implements EventSubscriberInterface
{
    public function __construct(
        /** @var RateLimiterFactory[] */
        private array $rateLimiterClassMap,
        private bool $isRateLimiterEnabled = true,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', 1024],
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$this->isRateLimiterEnabled || !$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        /** @var string $controllerClass */
        $controllerClass = $request->attributes->get('_controller');

        $rateLimiter = $this->rateLimiterClassMap[$controllerClass] ?? null;
        if (null === $rateLimiter) {
            return; // No rate limit service was assigned for this controller
        }

        $this->ensureRateLimiting($request, $rateLimiter);
    }

    private function ensureRateLimiting(Request $request, RateLimiterFactory $rateLimiter): void
    {
        $limiterKey = sprintf('rate_limit_ip_%s', $request->getClientIp());
        $limit = $rateLimiter->create($limiterKey)->consume();
        $request->attributes->set('rate_limit', $limit);

        if (false === $limit->isAccepted()) {
            throw new TooManyRequestsHttpException(
                $limit->getRetryAfter()->format(\DateTimeInterface::RFC7231),
                'Too many requests'
            );
        }
    }
}
