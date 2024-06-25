<?php
declare(strict_types=1);

namespace Headsnet\SymfonyToolsBundle\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class RateLimiting
{
    public function __construct(
        public string $configuration,
    ) {
    }
}
