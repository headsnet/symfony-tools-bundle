<?php
declare(strict_types=1);

namespace Headsnet\SymfonyToolsBundle\Tests\Form\Extension;

use Headsnet\SymfonyToolsBundle\Form\Extension\TextTypeDefaultStringExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\OptionsResolver\OptionsResolver;

#[CoversClass(TextTypeDefaultStringExtension::class)]
final class TextTypeDefaultStringExtensionTest extends TestCase
{
    #[Test]
    public function applies_to_all_textual_form_types(): void
    {
        $result = TextTypeDefaultStringExtension::getExtendedTypes();

        $this->assertEquals([TextType::class, EmailType::class, UrlType::class, TextareaType::class], $result);
    }

    #[Test]
    public function validation_is_disabled(): void
    {
        $optionsResolver = new OptionsResolver();
        $sut = new TextTypeDefaultStringExtension();

        $sut->configureOptions($optionsResolver);

        $this->assertTrue($optionsResolver->isDefined('empty_data'));
        $this->assertEquals(
            [
                'empty_data' => '',
            ],
            $optionsResolver->resolve()
        );
    }
}
