<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\repositories\views;

use Besnovatyj\Blog\entities\taxonomy\Taxonomy;

class TaxonomyView
{
    public $taxonomy;
    public $count;

    public function __construct(Taxonomy $taxonomy, $count)
    {
        $this->taxonomy = $taxonomy;
        $this->count = $count;
    }
}
