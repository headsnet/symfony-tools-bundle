<?php
declare(strict_types=1);

namespace Headsnet\SymfonyToolsBundle\Tests;

use Headsnet\SymfonyToolsBundle\HeadsnetSymfonyToolsBundle;
use Nyholm\BundleTest\TestKernel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

#[CoversClass(HeadsnetSymfonyToolsBundle::class)]
class HeadsnetSymfonyToolsBundleTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    /**
     * @param array{debug?: bool, environment?: string} $options
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        /** @var TestKernel $kernel $kernel */
        $kernel = parent::createKernel($options);
        $kernel->addTestBundle(HeadsnetSymfonyToolsBundle::class);
        $kernel->addTestConfig(__DIR__ . '/Fixtures/config.yaml');
        $kernel->handleOptions($options);

        return $kernel;
    }

    #[Test]
    public function initialise_bundle(): void
    {
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();

        $this->assertTrue(
            $container->hasParameter('headsnet_symfony_tools.root_namespace')
        );

        $this->assertTrue(
            $container->hasParameter('headsnet_symfony_tools.rate_limiting.use_headers')
        );

        $this->assertNotNull(
            $container->get('headsnet_symfony_tools.forms.default_empty_string_extension')
        );

        $this->assertNotNull(
            $container->get('headsnet_symfony_tools.forms.form_attributes_extension')
        );
    }
}
