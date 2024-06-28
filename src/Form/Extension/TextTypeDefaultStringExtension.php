<?php
declare(strict_types=1);

namespace Headsnet\SymfonyToolsBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Override the default behaviour of TextType, which returns null when the field is empty.
 *
 * To keep our entities free of nulls, we want to return an empty string instead. This
 * extension simply sets the "empty_data" option to "" for all TextType fields.
 */
final class TextTypeDefaultStringExtension extends AbstractTypeExtension
{
    #[\Override]
    public static function getExtendedTypes(): iterable
    {
        return [TextType::class, EmailType::class, UrlType::class, TextareaType::class];
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('empty_data', '');
    }
}
