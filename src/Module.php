<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog;

use Besnovatyj\Kernel\module\CmsModule;
use Besnovatyj\Contracts\module\DeclaresModule;
use Besnovatyj\Contracts\module\ProvidesAdminMenu;
use Besnovatyj\Contracts\module\ProvidesBootstrap;
use Besnovatyj\Contracts\module\ProvidesDependencies;
use Besnovatyj\Contracts\module\ProvidesDirectories;
use Besnovatyj\Contracts\module\ProvidesMigrations;
use Besnovatyj\Contracts\module\ProvidesOptions;
use Besnovatyj\Contracts\menu\MenuTarget;
use Besnovatyj\Contracts\menu\MenuTargetProvider;
use Besnovatyj\Blog\readModels\TaxonomyReadRepository;
use Yii;

class Module extends CmsModule implements
    DeclaresModule, ProvidesAdminMenu, ProvidesBootstrap,
    ProvidesDependencies, ProvidesDirectories,
    ProvidesMigrations, ProvidesOptions, MenuTargetProvider
{
    public const bool EDITABLE = true;
    public const string VERSION = '1.0.0';
    public const string MODULE_ID = 'Blog';

    public function init(): void
    {
        parent::init();

        if (!isset(Yii::$app->i18n->translations['Blog'])) {
            Yii::$app->i18n->translations['Blog'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en',
                'basePath' => '@Besnovatyj/Blog/messages'
            ];
        }
    }

    public static function moduleId(): string { return self::MODULE_ID; }
    public static function moduleVersion(): string { return self::VERSION; }
    public static function isEditable(): bool { return self::EDITABLE; }
    public static function adminMenu(): array { return require __DIR__ . '/config/adminMenu.php'; }
    public static function moduleConfig(): array { return require __DIR__ . '/config/config.php'; }
    public static function options(): array { return require __DIR__ . '/config/options.php'; }
    public static function dependencies(): array { return require __DIR__ . '/config/dependencies.php'; }
    public static function directories(): array { return ['@static/origin/Blog', '@static/cache/Blog']; }
    public static function migrationPath(): string       { return __DIR__.'/migrations'; }
    public static function migrationNamespace(): ?string { return __NAMESPACE__.'\\migrations'; }
    public static function bootstrapClasses(): array     { return [Bootstrap::class]; }

    /**
     * Цели для построения пунктов меню. Реализация {@see MenuTargetProvider};
     * вызывается только модулем меню, если он установлен.
     *
     * @return MenuTarget[]
     */
    public function menuTargets(): array
    {
        return [
            new MenuTarget('/Blog/post/taxonomy', 'Таксономия блога', 'slug'),
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string,string>
     */
    public function menuCandidates(string $route): array
    {
        return match (ltrim($route, '/')) {
            'Blog/post/taxonomy' => $this->taxonomySlugMap(),
            default => [],
        };
    }

    /**
     * Карта `slug => подпись` (с отступом по глубине дерева) для таксономий блога.
     *
     * @return array<string,string>
     */
    private function taxonomySlugMap(): array
    {
        $map = [];
        foreach ((new TaxonomyReadRepository())->getAll() as $taxonomy) {
            $prefix = $taxonomy->depth > 0 ? str_repeat('— ', (int)$taxonomy->depth) : '';
            $map[$taxonomy->slug] = $prefix . $taxonomy->name;
        }
        return $map;
    }

}
