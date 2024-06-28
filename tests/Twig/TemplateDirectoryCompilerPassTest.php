<?php

namespace Headsnet\SymfonyToolsBundle\Tests\Twig;

use Headsnet\SymfonyToolsBundle\Twig\TemplateDirectoryCompilerPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

#[CoversClass(TemplateDirectoryCompilerPass::class)]
class TemplateDirectoryCompilerPassTest extends TestCase
{
    private TemplateDirectoryCompilerPass $compilerPass;

    private ContainerBuilder $container;

    #[\Override]
    protected function setUp(): void
    {
        $this->compilerPass = new TemplateDirectoryCompilerPass();
        $this->container = new ContainerBuilder(new ParameterBag([
            'headsnet_symfony_tools.twig.import_feature_dirs.base_dir' => 'tests/Twig/Fixtures/',
            'headsnet_symfony_tools.twig.import_feature_dirs.separator' => '->',
            'headsnet_symfony_tools.twig.import_feature_dirs.tpl_dir_name' => 'tpl',
        ]));
        $this->container->setParameter('kernel.project_dir', '.');

        $twigFilesystemLoaderDefinition = new Definition();
        $this->container->setDefinition('twig.loader.native_filesystem', $twigFilesystemLoaderDefinition);
    }

    #[Test]
    public function process_with_duration_file_paths(): void
    {
        $this->compilerPass->process($this->container);

        $definition = $this->container->getDefinition('twig.loader.native_filesystem');
        $calls = $definition->getMethodCalls();
        $this->assertCount(2, $calls);

        $expected = [
            ['addPath', ['tests/Twig/Fixtures/FeatureA/tpl', 'FeatureA']],
            ['addPath', ['tests/Twig/Fixtures/FeatureB/tpl', 'FeatureB']],
        ];

        $this->assertSame($expected, $calls);
    }

    #[Test]
    public function process_with_nonexistent_definition(): void
    {
        $this->container->removeDefinition('twig.loader.native_filesystem');
        $this->compilerPass->process($this->container);
        $this->assertFalse($this->container->hasDefinition('twig.loader.native_filesystem'));
    }
}
