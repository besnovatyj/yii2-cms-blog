<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\entities\taxonomy;

use Besnovatyj\Blog\entities\Post;
use Besnovatyj\Blog\entities\queries\TaxonomyQuery;
use Besnovatyj\Meta\MetaBehavior;
use Besnovatyj\Meta\Meta;
use Besnovatyj\TreeManager\Manager\entities\Node;
use yii\db\ActiveQuery;

/**
 * @property integer $id
 * @property integer $lft
 * @property integer $rgt
 * @property integer $depth
 * @property integer $tree
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property integer $status
 * @property int $sort_order - Порядок сортировки корневых узлов
 *
 * @property Meta $meta
 *
 * @mixin MetaBehavior
 */
class Taxonomy extends Node
{
    /** Раздел скрыт: ни он сам, ни его посты не доступны на фронте. */
    public const int STATUS_INACTIVE = 0;

    /** Раздел опубликован. */
    public const int STATUS_ACTIVE = 1;

    public $meta;

    public static function create($name, $slug, $description, Meta $meta): self
    {
        $taxonomy = new static();
        $taxonomy->name = $name;
        $taxonomy->slug = $slug;
        $taxonomy->description = $description;
        $taxonomy->meta = $meta;
        return $taxonomy;
    }

    public function edit($name, $slug, $description, Meta $meta): void
    {
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->meta = $meta;
    }

    public function getSeoTitle(): string
    {
        return $this->meta->title ?: $this->name;
    }

    public function changeStatus(): void
    {
        $this->status = $this->isActive() ? self::STATUS_INACTIVE : self::STATUS_ACTIVE;
    }

    /**
     * Активен ли сам раздел (без учёта предков — их проверяет {@see TaxonomyQuery::visible()}).
     */
    public function isActive(): bool
    {
        return (int)$this->status === self::STATUS_ACTIVE;
    }

    public function countPostsByMainTaxonomy()
    {
        return $this->getPosts()->count();
    }

    public function getPosts(): ActiveQuery
    {
        return $this->hasMany(Post::class, ['taxonomy_id' => 'id']);
    }

    public function countPostsByOtherTaxonomy()
    {
        return $this->getPostsByOtherTaxonomy()->count();
    }

    public function getTaxonomyAssignments(): ActiveQuery
    {
        return $this->hasMany(TaxonomyAssignment::class, ['taxonomy_id' => 'id']);
    }

    public function getPostsByOtherTaxonomy(): ActiveQuery
    {
        return $this->hasMany(Post::class, ['id' => 'post_id'])->via('taxonomyAssignments');
    }

    public static function tableName(): string
    {
        return '{{%blog_taxonomy}}';
    }

    public function behaviors(): array
    {
        return [
            MetaBehavior::class,
            ...parent::behaviors()
        ];
    }

    public function transactions(): array
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }

    public static function find(): TaxonomyQuery
    {
        return new TaxonomyQuery(static::class);
    }
}
