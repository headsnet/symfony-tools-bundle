<?php
declare(strict_types=1);

namespace Headsnet\SymfonyToolsBundle\Twig;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

/**
 * Locate all "tpl" directories inside the $baseDir directory, and
 * add them to the Twig configuration as namespaced paths.
 *
 * A template such as "src/UI/Feature/Foo/Bar/tpl/test.twig.html" will
 * be added with namespace "Foo->Bar", and therefore can be referenced
 * using "@Foo->Bar/test.twig.html".
 */
final class TemplateDirectoryCompilerPass implements CompilerPassInterface
{
    #[\Override]
    public function process(ContainerBuilder $container): void
    {
        $baseDir = $this->getBundleParam($container, 'headsnet_symfony_tools.twig.import_feature_dirs.base_dir');

        if (!strlen($baseDir)) {
            return;
        }

        if ($container->hasDefinition('twig.loader.native_filesystem')) {
            $twigFilesystemLoaderDefinition = $container->getDefinition('twig.loader.native_filesystem');
            /** @var string $projectDir */
            $projectDir = $container->getParameter('kernel.project_dir');
            $separator = $this->getBundleParam($container, 'headsnet_symfony_tools.twig.import_feature_dirs.separator');
            $tplDirName = $this->getBundleParam($container, 'headsnet_symfony_tools.twig.import_feature_dirs.tpl_dir_name');
            $featureDir = sprintf('%s/%s', $projectDir, $baseDir);

            foreach ($this->tplDirPaths($featureDir, $tplDirName) as $file) {
                $tplDirToAdd = str_replace($projectDir . '/', '', $file->getPathname());

                $namespaceToAdd = str_replace(
                    [rtrim($baseDir, '/') . '/', '/' . trim($tplDirName, '/'), '/'],
                    ['', '', $separator],
                    $tplDirToAdd
                );

                $twigFilesystemLoaderDefinition->addMethodCall('addPath', [$tplDirToAdd, $namespaceToAdd]);
            }
        }
    }

    private function tplDirPaths(string $featureDir, string $tplDirName): Finder
    {
        $finder = new Finder();
        $finder->directories()
            ->in($featureDir)
            ->name($tplDirName)
            ->sortByName()
        ;

        return $finder;
    }

    /**
     * Slightly convoluted method to obtain the bundle configuration, as the parameters
     * do not seem to be ready in the container if we access them directly.
     */
    private function getBundleParam(ContainerBuilder $container, string $paramName): string
    {
        /** @var string $parameter */
        $parameter = $container->getParameterBag()->resolveValue(
            $container->getParameter($paramName)
        );

        return $parameter;
    }
}
