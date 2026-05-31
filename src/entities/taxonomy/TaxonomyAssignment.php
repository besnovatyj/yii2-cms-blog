<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\entities\taxonomy;

use yii\db\ActiveRecord;

/**
 * @property integer $post_id;
 * @property integer $taxonomy_id;
 */
class TaxonomyAssignment extends ActiveRecord
{
    public static function create($taxonomy_id): self
    {
        $assignment = new static();
        $assignment->taxonomy_id = $taxonomy_id;
        return $assignment;
    }

    public function isForTaxonomy($id): bool
    {
        return $this->taxonomy_id === $id;
    }

    public static function tableName(): string
    {
        return '{{%blog_taxonomy_asgmt}}';
    }
}
