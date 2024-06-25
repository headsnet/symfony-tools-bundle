<?php
declare(strict_types=1);

namespace Headsnet\SymfonyToolsBundle\RateLimiting;

use Headsnet\SymfonyToolsBundle\Attributes\RateLimiting;
use ReflectionClass;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RateLimitingCompilerPass implements CompilerPassInterface
{
    /**
     * @throws ReflectionException
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(ApplyRateLimitingSubscriber::class)) {
            throw new \LogicException(
                sprintf('Can not configure non-existent service %s', ApplyRateLimitingSubscriber::class)
            );
        }

        $taggedServices = $container->findTaggedServiceIds('controller.service_arguments');
        /** @var Definition[] $serviceDefinitions */
        $serviceDefinitions = array_map(fn (string $id) => $container->getDefinition($id), array_keys($taggedServices));

        $rateLimiterClassMap = [];

        foreach ($serviceDefinitions as $serviceDefinition) {
            $controllerClass = $serviceDefinition->getClass();
            /** @var ReflectionClass<AbstractController> $reflectionClass */
            $reflectionClass = $container->getReflectionClass($controllerClass);

            foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC | ~\ReflectionMethod::IS_STATIC) as $reflectionMethod) {
                $attributes = $reflectionMethod->getAttributes(RateLimiting::class);
                if (\count($attributes) > 0) {
                    [$attribute] = $attributes;

                    $serviceKey = sprintf('limiter.%s', $attribute->newInstance()->configuration);
                    if (!$container->hasDefinition($serviceKey)) {
                        throw new \RuntimeException(sprintf('Service %s not found', $serviceKey));
                    }

                    if ($reflectionMethod->getName() === '__invoke') {
                        $classMapKey = $serviceDefinition->getClass();
                    } else {
                        $classMapKey = sprintf('%s::%s', $serviceDefinition->getClass(), $reflectionMethod->getName());
                    }

                    $rateLimiterClassMap[$classMapKey] = $container->getDefinition($serviceKey);
                }
            }
        }

        $container
            ->getDefinition(ApplyRateLimitingSubscriber::class)
            ->setArgument('$rateLimiterClassMap', $rateLimiterClassMap)
        ;
    }
}
