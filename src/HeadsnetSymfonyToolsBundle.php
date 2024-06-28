<?php

namespace Headsnet\SymfonyToolsBundle;

use Headsnet\SymfonyToolsBundle\Form\Extension\TextTypeDefaultStringExtension;
use Headsnet\SymfonyToolsBundle\RateLimiting\RateLimitingCompilerPass;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class HeadsnetSymfonyToolsBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('root_namespace')->cannotBeEmpty()->end()
                ->arrayNode('rate_limiting')
                    ->children()
                        ->booleanNode('use_headers')->end()
                    ->end()
                ->end() // End rate_limiting
            ->arrayNode('forms')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('default_empty_string')
                        ->canBeEnabled()
                    ->end() // End default_empty_string
                ->end()
            ->end() // End forms
            ->end()
        ;
    }

    /**
     * @param array{
     *     root_namespace: string,
     *     forms: array{
     *         default_empty_string: array{
     *             enabled: bool
     *         }
     *     },
     *     rate_limiting: array{use_headers: bool}
     * } $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        $container->parameters()
            ->set('headsnet_symfony_tools.root_namespace', $config['root_namespace'])
            ->set('headsnet_symfony_tools.rate_limiting.use_headers', $config['rate_limiting']['use_headers'])
        ;

        if ($config['forms']['default_empty_string']['enabled']) {
            $container->services()
                ->set('headsnet_symfony_tools.forms.default_empty_string_extension')
                ->class(TextTypeDefaultStringExtension::class)
                ->tag('form.type_extension')
                ->public()
            ;
        }
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(
            new RateLimitingCompilerPass()
        );
    }
}
