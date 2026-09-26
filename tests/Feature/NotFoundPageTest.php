<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotFoundPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_url_renders_custom_gallery_page(): void
    {
        $this->get('/no-such-page')
            ->assertNotFound()
            ->assertSee(__('site.e404_title'))
            ->assertSee('css/error.css', false)
            ->assertSee('noindex, nofollow', false)
            ->assertSee('glyph__text', false);
    }

    public function test_missing_product_renders_the_same_page(): void
    {
        $this->get('/product/does-not-exist')
            ->assertNotFound()
            ->assertSee(__('site.e404_plaque_title'));
    }
}
