<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Blog;

use Besnovatyj\Blog\entities\taxonomy\Taxonomy;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;

/**
 * Bootstrap блога: инвалидация кэша ЧПУ-путей таксономий при изменении дерева.
 *
 * {@see \Besnovatyj\Blog\urls\TaxonomyUrlRule} кэширует соответствие «slug-путь ↔ id» с тегом
 * `blog_taxonomies`. Таксономии пишутся через tree-manager (NestedSetsRepository) обычным AR
 * `->save()`, поэтому ловим AR-события `Taxonomy` и сбрасываем весь тег — «крупный помол» покрывает
 * и переименование слага, и перемещение (у потомков путь тоже меняется).
 *
 * Bootstrap глобальный (L2, гейт modman): выполняется во всех приложениях; правки идут из backend,
 * кэш `apcu` общий, поэтому фронт получает свежие ЧПУ.
 */
final class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app): void
    {
        $invalidate = static function (): void {
            if (Yii::$app->has('cache')) {
                TagDependency::invalidate(Yii::$app->cache, ['blog_taxonomies']);
            }
        };

        Event::on(Taxonomy::class, ActiveRecord::EVENT_AFTER_INSERT, $invalidate);
        Event::on(Taxonomy::class, ActiveRecord::EVENT_AFTER_UPDATE, $invalidate);
        Event::on(Taxonomy::class, ActiveRecord::EVENT_AFTER_DELETE, $invalidate);
    }
}
