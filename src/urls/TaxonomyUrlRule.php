<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Blog\urls;

use Besnovatyj\Blog\readModels\TaxonomyReadRepository;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidArgumentException;
use yii\caching\CacheInterface;
use yii\caching\TagDependency;
use yii\web\UrlNormalizerRedirectException;
use yii\web\UrlRuleInterface;

/**
 * ЧПУ-правило дерева таксономий блога (аналог {@see \Besnovatyj\Catalog\urls\CategoryUrlRule}).
 *
 * Двунаправленно: `blog/<slug-предка>/.../<slug>` ↔ внутренний роут `Blog/post/taxonomy` c `id`.
 * Поддерживает вложенные таксономии (nested sets), кэширует slug-путь (инвалидация по тегу
 * `blog_taxonomies`), при неканоническом пути — 301 на канонический адрес.
 *
 * Подключается как `['class' => TaxonomyUrlRule::class]` в `components.frontendUrlManager.rules`
 * (группа `common`); {@see TaxonomyReadRepository} внедряет контейнер. Требует
 * `frontendUrlManager.cache = false` (объект-правило с сервисами не сериализуется в кэш правил).
 */
final class TaxonomyUrlRule extends BaseObject implements UrlRuleInterface
{
    /** Префикс ЧПУ (первый сегмент URL); после него — путь слагов дерева таксономий. */
    public string $prefix = 'blog';

    /** Внутренний роут с id таксономии (капитализирован под id модуля 'Blog'). */
    public string $route = 'Blog/post/taxonomy';

    private TaxonomyReadRepository $repository;
    private CacheInterface $cache;

    public function __construct(TaxonomyReadRepository $repository, $config = [])
    {
        parent::__construct($config);
        $this->repository = $repository;
        $this->cache = Yii::$app->cache;
    }

    public function parseRequest($manager, $request): array|false
    {
        if (!preg_match('#^' . $this->prefix . '/(.*[a-z])$#is', $request->pathInfo, $matches)) {
            return false;
        }
        $path = $matches[1];

        $result = $this->cache->getOrSet(['taxonomy_route', 'path' => $path], function () use ($path) {
            if (!$taxonomy = $this->repository->findBySlug($this->leafSlug($path))) {
                return ['id' => null, 'path' => null];
            }
            return ['id' => $taxonomy->id, 'path' => $this->repository->pathTo($taxonomy)];
        }, null, new TagDependency(['tags' => ['blog_taxonomies']]));

        if (empty($result['id'])) {
            return false;
        }

        if ($path !== $result['path']) {
            throw new UrlNormalizerRedirectException([$this->route, 'id' => $result['id']], 301);
        }

        return [$this->route, ['id' => $result['id']]];
    }

    public function createUrl($manager, $route, $params): string|false
    {
        if ($route !== $this->route) {
            return false;
        }
        if (empty($params['id'])) {
            throw new InvalidArgumentException('Empty id.');
        }
        $id = $params['id'];

        $path = $this->cache->getOrSet(['taxonomy_route', 'id' => $id], function () use ($id) {
            return ($taxonomy = $this->repository->find((int)$id)) ? $this->repository->pathTo($taxonomy) : null;
        }, null, new TagDependency(['tags' => ['blog_taxonomies']]));

        if (!$path) {
            throw new InvalidArgumentException('Undefined id.');
        }

        $url = $this->prefix . '/' . $path;
        unset($params['id']);
        if ($params !== [] && ($query = http_build_query($params)) !== '') {
            $url .= '?' . $query;
        }

        return $url;
    }

    private function leafSlug(string $path): string
    {
        $chunks = explode('/', $path);
        return end($chunks);
    }
}
