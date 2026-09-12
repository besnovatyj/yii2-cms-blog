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
use Besnovatyj\Contracts\search\SearchableProvider;
use Besnovatyj\Contracts\search\SearchSource;
use Besnovatyj\Contracts\sitemap\ChangeFrequency;
use Besnovatyj\Contracts\sitemap\SitemapFreshness;
use Besnovatyj\Contracts\sitemap\SitemapProvider;
use Besnovatyj\Contracts\sitemap\SitemapSection;
use Besnovatyj\Contracts\sitemap\SitemapUrl;
use Besnovatyj\Contracts\tags\TaggableProvider;
use Besnovatyj\Contracts\tags\TagSource;
use Besnovatyj\Blog\entities\Post;
use Besnovatyj\Blog\entities\taxonomy\Taxonomy;
use Besnovatyj\Blog\readModels\PostReadRepository;
use Besnovatyj\Blog\readModels\TaxonomyReadRepository;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;
use Yii;

class Module extends CmsModule implements
    DeclaresModule, ProvidesAdminMenu, ProvidesBootstrap,
    ProvidesDependencies, ProvidesDirectories,
    ProvidesMigrations, ProvidesOptions, MenuTargetProvider, SearchableProvider,
    SitemapProvider, SitemapFreshness, TaggableProvider
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
        return (new TreeQueryScope(Taxonomy::class))->dropdownTree(keyAttribute: 'slug', indent: '— ');
    }

    /**
     * Контент модуля для сквозного поиска. Реализация {@see SearchableProvider};
     * вызывается только модулем поиска, если он установлен.
     *
     * @return SearchSource[]
     */
    public function searchSources(): array
    {
        return [
            new SearchSource('blog.post', 'Статьи блога', 1.0, 'bi bi-newspaper'),
            new SearchSource('blog.taxonomy', 'Разделы блога', 0.7, 'bi bi-diagram-3'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function searchDocuments(string $type): iterable
    {
        return match ($type) {
            'blog.post' => (new PostReadRepository())->searchDocuments(),
            'blog.taxonomy' => (new TaxonomyReadRepository())->searchDocuments(),
            default => [],
        };
    }

    /**
     * Посты — участники общего словаря тегов. Реализация {@see TaggableProvider}; вызывается модулем
     * тегов для страницы `/tag/<slug>` и облака. Ключ — тот же `blog.post`, что у поиска и карты.
     *
     * @return TagSource[]
     */
    public function tagSources(): array
    {
        return [
            new TagSource(Post::tagType(), 'Статьи блога', 'bi bi-newspaper'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function visibleTaggedIds(string $type, array $ids): array
    {
        return match ($type) {
            Post::tagType() => (new PostReadRepository())->visibleIds($ids),
            default => [],
        };
    }

    /**
     * {@inheritdoc}
     */
    public function taggedItems(string $type, array $ids): iterable
    {
        return match ($type) {
            Post::tagType() => (new PostReadRepository())->taggedItems($ids),
            default => [],
        };
    }

    /**
     * Разделы карты сайта. Реализация {@see SitemapProvider}; вызывается только модулем карты,
     * если он установлен.
     *
     * Посты объявлены «только для XML»: их сотни, и на человеческой карте они превратили бы
     * оглавление сайта в ленту. Читателю там нужны разделы блога, а роботу — все адреса.
     *
     * @return SitemapSection[]
     */
    public function sitemapSections(): array
    {
        return [
            new SitemapSection(
                key: 'blog.taxonomy',
                label: 'Блог',
                changeFrequency: ChangeFrequency::Daily,
                priority: 0.6,
                order: 30,
                icon: 'bi bi-diagram-3',
            ),
            new SitemapSection(
                key: 'blog.post',
                label: 'Статьи блога',
                changeFrequency: ChangeFrequency::Weekly,
                priority: 0.6,
                inHtmlMap: false,
                order: 40,
                icon: 'bi bi-newspaper',
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function sitemapUrls(string $section): iterable
    {
        return match ($section) {
            'blog.post' => (new PostReadRepository())->sitemapUrls(),
            'blog.taxonomy' => $this->taxonomySitemapUrls(),
            default => [],
        };
    }

    /**
     * {@inheritdoc}
     *
     * Отпечаток есть только у постов: у разделов блога нет колонок времени
     * (см. {@see TaxonomyReadRepository::sitemapUrls()}).
     */
    public function sitemapRevision(string $section): ?string
    {
        return match ($section) {
            'blog.post' => (new PostReadRepository())->sitemapRevision(),
            default => null,
        };
    }

    /**
     * Разделы блога, а перед ними — сам список блога.
     *
     * Список — корень этой ветки и для робота, и для читателя: на человеческой карте он открывает
     * блок, в XML это обычный адрес с высоким приоритетом. Отдельным разделом карты его заводить
     * незачем — раздел из одного адреса только засоряет и настройки, и индекс файлов.
     *
     * @return iterable<SitemapUrl>
     */
    private function taxonomySitemapUrls(): iterable
    {
        yield new SitemapUrl(
            route: '/Blog/post/index',
            title: 'Блог',
            changeFrequency: ChangeFrequency::Daily,
            priority: 0.9,
        );

        yield from (new TaxonomyReadRepository())->sitemapUrls();
    }
}
