<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\widgets;

use Besnovatyj\Blog\entities\taxonomy\Taxonomy;
use Besnovatyj\Blog\readModels\TaxonomyReadRepository;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * Виджет со списком категорий для sidebar of frontend
 */
class TaxonomiesWidget extends Widget
{
    /** @var \Besnovatyj\Blog\entities\taxonomy\Taxonomy|null */
    public $active;
    private $categories;

    public function __construct(TaxonomyReadRepository $categories, $config = [])
    {
        parent::__construct($config);
        $this->categories = $categories;
    }

    public function run(): string
    {
        return Html::tag('ul', implode(PHP_EOL, array_map(function (Taxonomy $taxonomy) {
            $active = $this->active && ($this->active->id == $taxonomy->id);
            $class = $active ? 'list-group-item active' : 'list-group-item';
            return '<li class="' . $class . '">' . Html::a(
                Html::encode($taxonomy->name),
                ['/Blog/post/taxonomy', 'slug' => $taxonomy->slug]
            ) . '<small>' . $taxonomy->countPostsByMainTaxonomy() . '</small></li>';
        }, $this->categories->getAll())), [
            'class' => 'list-group list-group-flush',
        ]);
    }
}
