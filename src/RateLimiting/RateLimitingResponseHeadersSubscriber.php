<?php
declare(strict_types=1);

namespace Headsnet\SymfonyToolsBundle\RateLimiting;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimit;

final readonly class RateLimitingResponseHeadersSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire(param: 'headsnet_symfony_tools.rate_limiting.use_headers')]
        private bool $useHeaders
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', 1024],
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$this->useHeaders) {
            return;
        }

        if (($rateLimit = $event->getRequest()->attributes->get('rate_limit')) instanceof RateLimit) {
            $event->getResponse()->headers->add([
                'Rate-Limit-Remaining' => $rateLimit->getRemainingTokens(),
                'Rate-Limit-Reset' => time() - $rateLimit->getRetryAfter()->getTimestamp(),
                'Rate-Limit-Limit' => $rateLimit->getLimit(),
            ]);
        }
    }
}
