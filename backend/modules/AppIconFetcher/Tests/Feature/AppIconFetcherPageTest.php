<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class AppIconFetcherPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected_from_app_icon_fetcher_page(): void
    {
        $response = $this->get(route('app-icon-fetcher.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_open_app_icon_fetcher_page(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('app-icon-fetcher.index'));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('AppIconFetcher/Index'));
    }
}
