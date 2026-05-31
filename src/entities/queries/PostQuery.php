<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\entities\queries;

use Besnovatyj\Blog\entities\Post;
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
}
