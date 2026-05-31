<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\forms\backend;

use Besnovatyj\Blog\entities\taxonomy\Taxonomy;
use Besnovatyj\Blog\entities\Post;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class TaxonomiesForm extends Model
{
    public $main;
    public $others = [];

    public function __construct(Post $post = null, $config = [])
    {
        if ($post) {
            $this->main = $post->taxonomy_id;
            $this->others = ArrayHelper::getColumn($post->taxonomyAssignments, 'taxonomy_id');
        }
        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            ['main', 'required'],
            ['main', 'integer'],
            ['others', 'each', 'rule' => ['integer']],
            ['others', 'default', 'value' => []],
        ];
    }

    public function taxonomiesList(): array
    {
        $scope = new TreeQueryScope(Taxonomy::class);
        return $scope->dropdownTree();
    }

}
