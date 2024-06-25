<?php
declare(strict_types=1);

namespace Headsnet\SymfonyToolsBundle\Tests\RateLimiting\Fixtures;

use Headsnet\SymfonyToolsBundle\Attributes\RateLimiting;

class FakeInvokableController
{
    #[RateLimiting(configuration: 'test')]
    public function __invoke(): void
    {
        // Action logic here
    }
}
