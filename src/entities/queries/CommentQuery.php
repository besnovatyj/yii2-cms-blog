<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\entities\queries;

use Besnovatyj\Blog\entities\Comment;
use yii\db\ActiveQuery;

class CommentQuery extends ActiveQuery
{
    public function draft(): CommentQuery
    {
        return $this->andWhere(['active' => Comment::STATUS_DRAFT]);
    }

    public function active(): CommentQuery
    {
        return $this->andWhere(['active' => Comment::STATUS_ACTIVE]);
    }

    public function removed(): CommentQuery
    {
        return $this->andWhere(['active' => Comment::STATUS_DELETED]);
    }

}
