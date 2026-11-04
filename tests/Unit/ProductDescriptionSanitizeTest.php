<?php

namespace Tests\Unit;

use App\Models\Product;
use PHPUnit\Framework\TestCase;

class ProductDescriptionSanitizeTest extends TestCase
{
    public function test_allowed_tags_lose_every_attribute(): void
    {
        $product = new Product([
            'description_ru' => '<p onclick="alert(1)" class="x">Текст</p><script>alert(1)</script><img src=x onerror=alert(1)>',
        ]);

        $this->assertSame('<p>Текст</p>', $product->descriptionHtml('ru'));
    }

    public function test_allowed_formatting_is_kept_and_br_is_normalised(): void
    {
        $product = new Product([
            'description_ru' => '<p><strong>Жирный</strong><br/><em>курсив</em></p><ul><li>Пункт</li></ul>',
        ]);

        $this->assertSame(
            '<p><strong>Жирный</strong><br><em>курсив</em></p><ul><li>Пункт</li></ul>',
            $product->descriptionHtml('ru'),
        );
    }
}
