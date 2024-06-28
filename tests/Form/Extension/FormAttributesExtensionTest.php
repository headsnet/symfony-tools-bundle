<?php
declare(strict_types=1);

namespace Headsnet\SymfonyToolsBundle\Tests\Form\Extension;

use Headsnet\SymfonyToolsBundle\Form\Extension\FormAttributesExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

#[CoversClass(FormAttributesExtension::class)]
final class FormAttributesExtensionTest extends TestCase
{
    #[Test]
    public function applies_to_parent_form_type(): void
    {
        $result = FormAttributesExtension::getExtendedTypes();

        $this->assertEquals([FormType::class], $result);
    }

    #[Test]
    public function validation_is_disabled(): void
    {
        $view = new FormView();
        $form = $this->createMock(FormInterface::class);
        $sut = new FormAttributesExtension(true, true);

        $sut->buildView($view, $form, []);

        $this->assertArrayHasKey('novalidate', $view->vars['attr']);
        $this->assertEquals('novalidate', $view->vars['attr']['novalidate']);
    }

    #[Test]
    public function autocomplete_is_disabled(): void
    {
        $view = new FormView();
        $form = $this->createMock(FormInterface::class);
        $sut = new FormAttributesExtension(true, true);

        $sut->buildView($view, $form, []);

        $this->assertArrayHasKey('autocomplete', $view->vars['attr']);
        $this->assertEquals('off', $view->vars['attr']['autocomplete']);
    }
}
