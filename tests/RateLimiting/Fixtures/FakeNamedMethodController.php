<?php
declare(strict_types=1);

namespace Headsnet\SymfonyToolsBundle\Tests\RateLimiting\Fixtures;

use Headsnet\SymfonyToolsBundle\Attributes\RateLimiting;

class FakeNamedMethodController
{
    #[RateLimiting(configuration: 'test')]
    public function create(): void
    {
        // Action logic here
    }
}
