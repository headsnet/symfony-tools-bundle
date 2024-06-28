<?php
declare(strict_types=1);

namespace Headsnet\SymfonyToolsBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use function array_key_exists;

final class FormAttributesExtension extends AbstractTypeExtension
{
    public function __construct(
        private readonly bool $disableAutoComplete,
        private readonly bool $disableValidation,
    ) {
    }

    #[\Override]
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if ($this->disableAutoComplete) {
            $this->addAutocompleteAttribute($view);
        }

        if ($this->disableValidation) {
            $this->addNoValidateAttribute($view);
        }
    }

    private function addNoValidateAttribute(FormView $view): void
    {
        if (!array_key_exists('novalidate', $view->vars['attr'])) {
            $view->vars['attr']['novalidate'] = 'novalidate';
        }
    }

    private function addAutocompleteAttribute(FormView $view): void
    {
        if (!array_key_exists('autocomplete', $view->vars['attr'])) {
            $view->vars['attr']['autocomplete'] = 'off';
        }
    }
}
