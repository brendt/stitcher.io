<?php

declare(strict_types=1);

namespace Tests\Game\Ui;

use PHPUnit\Framework\Attributes\Test;
use Tests\IntegrationTestCase;

final class GameUiControllerTest extends IntegrationTestCase
{
    #[Test]
    public function demo_route_creates_a_game_and_redirects_to_ui_page(): void
    {
        $response = $this->http->get('/game/demo');

        $response->assertRedirect();
        $location = $response->response->getHeader('location')->first();

        self::assertNotNull($location);
        self::assertStringContainsString('/game/demo-', $location);

        $page = $this->http->get($location);
        $page->assertOk();
        $page->assertSee('Rail Claim Demo');
    }
}
