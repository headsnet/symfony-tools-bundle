<?php

namespace Headsnet\SymfonyToolsBundle;

use Headsnet\SymfonyToolsBundle\Form\Extension\FormAttributesExtension;
use Headsnet\SymfonyToolsBundle\Form\Extension\TextTypeDefaultStringExtension;
use Headsnet\SymfonyToolsBundle\RateLimiting\RateLimitingCompilerPass;
use Headsnet\SymfonyToolsBundle\Twig\TemplateDirectoryCompilerPass;
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
                        ->booleanNode('default_empty_string')->defaultFalse()->end()
                        ->booleanNode('disable_autocomplete')->defaultFalse()->end()
                        ->booleanNode('disable_validation')->defaultFalse()->end()
                    ->end()
                ->end() // End forms
                ->arrayNode('twig')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('import_feature_dirs')
                            ->canBeEnabled()
                            ->children()
                                ->scalarNode('base_dir')->defaultValue('')->end()
                                ->scalarNode('separator')->defaultValue('->')->end()
                                ->scalarNode('tpl_dir_name')->defaultValue('tpl')->end()
                            ->end()
                        ->end() // End import_feature_dirs
                    ->end()
                ->end() // End twig
            ->end()
        ;
    }

    /**
     * @param array{
     *     root_namespace: string,
     *     forms: array{
     *         default_empty_string: bool,
     *         disable_autocomplete: bool,
     *         disable_validation: bool,
     *     },
     *     rate_limiting: array{
     *         use_headers: bool
     *     },
     *     twig: array{
     *         import_feature_dirs: array{
     *             base_dir: string,
     *             separator: string,
     *             tpl_dir_name: string,
     *         }
     *     }
     * } $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        $container->parameters()
            ->set('headsnet_symfony_tools.root_namespace', $config['root_namespace'])
            ->set('headsnet_symfony_tools.rate_limiting.use_headers', $config['rate_limiting']['use_headers'])
            ->set('headsnet_symfony_tools.twig.import_feature_dirs.base_dir', $config['twig']['import_feature_dirs']['base_dir'])
            ->set('headsnet_symfony_tools.twig.import_feature_dirs.separator', $config['twig']['import_feature_dirs']['separator'])
            ->set('headsnet_symfony_tools.twig.import_feature_dirs.tpl_dir_name', $config['twig']['import_feature_dirs']['tpl_dir_name'])
        ;

        if ($config['forms']['default_empty_string']) {
            $container->services()
                ->set('headsnet_symfony_tools.forms.default_empty_string_extension')
                ->class(TextTypeDefaultStringExtension::class)
                ->tag('form.type_extension')
                ->public()
            ;
        }

        if ($config['forms']['disable_validation'] || $config['forms']['disable_autocomplete']) {
            $container->services()
                ->set('headsnet_symfony_tools.forms.form_attributes_extension')
                ->class(FormAttributesExtension::class)
                ->tag('form.type_extension')
                ->arg('$disableAutoComplete', $config['forms']['disable_autocomplete'])
                ->arg('$disableValidation', $config['forms']['disable_validation'])
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

        $container->addCompilerPass(
            new TemplateDirectoryCompilerPass()
        );
    }
}
