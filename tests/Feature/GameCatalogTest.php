<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameRegion;
use App\Models\Language;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_games_have_localized_names_and_active_ordered_scope(): void
    {
        Game::query()->forceCreate([
            'name' => ['en' => 'Inactive Game', 'ar' => 'لعبة غير فعالة'],
            'slug' => 'inactive-game',
            'is_active' => false,
            'sort_order' => 1,
        ]);

        $second = Game::query()->forceCreate([
            'name' => ['en' => 'Second Game', 'ar' => 'اللعبة الثانية'],
            'slug' => 'second-game',
            'is_active' => true,
            'sort_order' => 20,
        ]);

        $first = Game::query()->forceCreate([
            'name' => ['en' => 'First Game', 'ar' => 'اللعبة الأولى'],
            'slug' => 'first-game',
            'is_active' => true,
            'sort_order' => 10,
        ]);

        $games = Game::query()->active()->ordered()->get();

        $this->assertTrue($games->contains($first));
        $this->assertTrue($games->contains($second));
        $this->assertSame([$first->id, $second->id], $games->pluck('id')->all());
        $this->assertSame('اللعبة الأولى', $first->getName('ar'));
        $this->assertSame('First Game', $first->getName('en'));
    }

    public function test_game_regions_are_normalized_and_active_regions_filter_inactive_records(): void
    {
        $game = Game::query()->forceCreate([
            'name' => ['en' => 'PUBG MOBILE', 'ar' => 'PUBG MOBILE'],
            'slug' => 'pubg-mobile',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $activeRegion = GameRegion::query()->forceCreate([
            'name' => ['en' => 'Middle East', 'ar' => 'الشرق الأوسط'],
            'code' => 'middle east',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $inactiveRegion = GameRegion::query()->forceCreate([
            'name' => ['en' => 'Europe', 'ar' => 'أوروبا'],
            'code' => 'europe',
            'is_active' => false,
            'sort_order' => 2,
        ]);

        $game->regions()->attach($activeRegion->id, ['is_active' => true, 'sort_order' => 1]);
        $game->regions()->attach($inactiveRegion->id, ['is_active' => true, 'sort_order' => 2]);

        $this->assertSame('MIDDLE_EAST', $activeRegion->fresh()->code);
        $this->assertSame('EUROPE', $inactiveRegion->fresh()->code);
        $this->assertSame([$activeRegion->id], $game->activeRegions()->pluck('game_regions.id')->all());
        $this->assertSame('الشرق الأوسط', $activeRegion->getName('ar'));
    }

    public function test_inactive_pivot_region_is_not_returned_for_game_selection(): void
    {
        $game = Game::query()->forceCreate([
            'name' => ['en' => 'Honor of Kings'],
            'slug' => 'honor-of-kings',
            'is_active' => true,
        ]);

        $region = GameRegion::query()->forceCreate([
            'name' => ['en' => 'Global'],
            'code' => 'global',
            'is_active' => true,
        ]);

        $game->regions()->attach($region->id, ['is_active' => false]);

        $this->assertCount(1, $game->regions);
        $this->assertCount(0, $game->activeRegions);
    }

    public function test_admin_games_and_regions_pages_render_without_label_errors(): void
    {
        Language::query()->forceCreate([
            'name' => 'Arabic',
            'native_name' => 'العربية',
            'code' => 'ar',
            'direction' => 'rtl',
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        Game::query()->forceCreate([
            'name' => ['ar' => 'ببجي', 'en' => 'PUBG MOBILE'],
            'slug' => 'pubg-mobile',
            'is_active' => true,
        ]);

        GameRegion::query()->forceCreate([
            'name' => ['ar' => 'الشرق الأوسط', 'en' => 'Middle East'],
            'code' => 'middle-east',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin/games')
            ->assertOk()
            ->assertSee('ببجي');

        $this->actingAs($admin)
            ->get('/admin/game-regions')
            ->assertOk()
            ->assertSee('MIDDLE_EAST');
    }
}
