<?php
declare(strict_types=1);

namespace Headsnet\SymfonyToolsBundle\Tests\RateLimiting;

use Headsnet\SymfonyToolsBundle\Attributes\RateLimiting;
use Headsnet\SymfonyToolsBundle\RateLimiting\ApplyRateLimitingSubscriber;
use Headsnet\SymfonyToolsBundle\RateLimiting\RateLimitingCompilerPass;
use Headsnet\SymfonyToolsBundle\Tests\RateLimiting\Fixtures\FakeController;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\RateLimiter\RateLimiterFactory;

#[CoversClass(RateLimiting::class)]
#[CoversClass(RateLimitingCompilerPass::class)]
class RateLimitingCompilerPassTest extends TestCase
{
    #[Test]
    public function process_throws_logic_exception_when_subscriber_service_is_missing(): void
    {
        $container = new ContainerBuilder();
        $sut = new RateLimitingCompilerPass();

        $this->expectException(LogicException::class);

        $sut->process($container);
    }

    #[Test]
    public function process_configures_rate_limiting_class_map_correctly(): void
    {
        $expectedRateLimiterClassMap = [FakeController::class . '::__invoke'];
        $container = new ContainerBuilder();
        // Define subscriber and add to container
        $subscriberDefinition = new Definition();
        $container->setDefinition(ApplyRateLimitingSubscriber::class, $subscriberDefinition);
        // Define rate limiter and add to container
        $rateLimiterDefinition = new Definition();
        $rateLimiterServiceId = 'limiter.test';
        $container->setDefinition($rateLimiterServiceId, $rateLimiterDefinition);
        // Define controller and add to container
        $controllerClass = FakeController::class;
        $controllerDefinition = new Definition($controllerClass);
        $controllerDefinition->addTag('controller.service_arguments');
        $container->setDefinition('app.controller.fake', $controllerDefinition);
        $sut = new RateLimitingCompilerPass();

        $sut->process($container);

        /** @var array<string, RateLimiterFactory> $calculatedClassMap */
        $calculatedClassMap = $subscriberDefinition->getArgument('$rateLimiterClassMap');
        $this->assertEquals($expectedRateLimiterClassMap, array_keys($calculatedClassMap));
    }

    #[Test]
    public function process_throws_runtime_exception_when_rate_limiter_service_is_missing(): void
    {
        $container = new ContainerBuilder();
        $container->setDefinition(ApplyRateLimitingSubscriber::class, new Definition());
        $controllerDefinition = new Definition(FakeController::class);
        $controllerDefinition->addTag('controller.service_arguments');
        $container->setDefinition('app.controller.fake', $controllerDefinition);
        $sut = new RateLimitingCompilerPass();

        $this->expectException(RuntimeException::class);

        $sut->process($container);
    }
}
