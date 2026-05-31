<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\entities;

use yii\db\ActiveRecord;

/**
 * @property integer $post_id;
 * @property integer $tag_id;
 */
class TagAssignment extends ActiveRecord
{
    public static function create($tagId): self
    {
        $assignment = new static();
        $assignment->tag_id = $tagId;
        return $assignment;
    }

    public function isForTag($id): bool
    {
        return $this->tag_id === $id;
    }

    public static function tableName(): string
    {
        return '{{%blog_tag_asgmt}}';
    }
}
