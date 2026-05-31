<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\entities;

use Besnovatyj\Blog\entities\TagAssignment;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property integer $id
 * @property string $name
 * @property string $slug
 *
 * @property TagAssignment $tagAssignments
 */
class Tag extends ActiveRecord
{
    public static function create($name, $slug): self
    {
        $tag = new static();
        $tag->name = $name;
        $tag->slug = $slug;
        return $tag;
    }

    public function edit($name, $slug): void
    {
        $this->name = $name;
        $this->slug = $slug;
    }

    public function getTagAssignments(): ActiveQuery
    {
        return $this->hasMany(TagAssignment::class, ['tag_id' => 'id']);
    }

    public static function tableName(): string
    {
        return '{{%blog_tags}}';
    }

}
