<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\entities\queries;

use Besnovatyj\Blog\entities\Post;
use Besnovatyj\Blog\entities\taxonomy\Taxonomy;
use yii\db\ActiveQuery;

class PostQuery extends ActiveQuery
{
    /**
     * @param null $alias
     * @return $this
     */
    public function active($alias = null): static
    {
        return $this->andWhere([
            ($alias ? $alias . '.' : '') . 'status' => Post::STATUS_ACTIVE,
        ]);
    }

    /**
     * Пост доступен анонимному посетителю: опубликован сам И лежит в видимом разделе.
     *
     * Одной публикации поста мало: скрытый раздел не должен «протекать» на фронт своими постами
     * ни через ленту, ни через виджеты, ни через прямую ссылку. Раздел проверяется целиком —
     * вместе с предками (см. {@see TaxonomyQuery::visible()}).
     *
     * Пост без раздела (`taxonomy_id` NULL) виден: скрывать его не за что.
     *
     * @param string|null $alias алиас таблицы постов, если запрос строится с `alias()`
     */
    public function visible(?string $alias = null): static
    {
        $column = ($alias ? $alias . '.' : '') . 'taxonomy_id';

        return $this->active($alias)->andWhere([
            'or',
            [$column => null],
            [$column => Taxonomy::find()->visible()->select('id')],
        ]);
    }
}
