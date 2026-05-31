<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\readModels;

use Besnovatyj\Blog\entities\taxonomy\Taxonomy;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;
use yii\helpers\ArrayHelper;

class TaxonomyReadRepository
{
    private TreeQueryScope $treeScope;

    public function __construct()
    {
        $this->treeScope = new TreeQueryScope(Taxonomy::class);
    }

    /**
     * @return Taxonomy[]
     */
    public function getAll(): array
    {
        return Taxonomy::find()->orderBy('lft')->all();
    }

    public function getAllAsArray(): array
    {
        return Taxonomy::find()->orderBy('lft')->asArray()->all();
    }

    public function find(int $id): ?Taxonomy
    {
        return Taxonomy::find()->andWhere(['id' => $id])->one();
    }

    /**
     * @param string $slug
     * @return Taxonomy|null
     */
    public function findBySlug(string $slug): ?Taxonomy
    {
        return Taxonomy::find()->andWhere(['slug' => $slug])->one();
    }

    public function getTreeWithSubsOf(Taxonomy $taxonomy = null): array
    { // TODO - JOIN - blog_posts - count()
        $query = Taxonomy::find()->orderBy('lft');
        if ($taxonomy) {
            $parents = $this->treeScope->parentsQuery($taxonomy)->all();
            $criteria = ['or', ['depth' => 2]];
            foreach (ArrayHelper::merge([$taxonomy], $parents) as $item) {
                $criteria[] = ['and', ['>', 'lft', $item->lft], ['<', 'rgt', $item->rgt], ['depth' => $item->depth + 1]];
            }
            $query->andWhere($criteria);
        } else {
            $query->andWhere(['depth' => 1]);
        }

        return $query->all();
    }
}
