<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\repositories;

use Besnovatyj\Blog\entities\taxonomy\Taxonomy;
use Throwable;
use yii\db\Exception;
use yii\db\StaleObjectException;

class TaxonomyRepository
{

    public function get($id): Taxonomy
    {
        if (!$taxonomy = Taxonomy::findOne($id)) {
            throw new NotFoundException('Taxonomy is not found.');
        }
        return $taxonomy;
    }

    /**
     * @throws Exception
     */
    public function save(Taxonomy $taxonomy): void
    {
        if (!$taxonomy->save()) {
            throw new \RuntimeException('Saving error.');
        }
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function remove(Taxonomy $taxonomy): void
    {
        if (!$taxonomy->delete()) {
            throw new \RuntimeException('Removing error.');
        }
    }
}
