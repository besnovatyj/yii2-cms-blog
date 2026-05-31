<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\forms\backend\elasticSearch;

use Besnovatyj\Blog\entities\taxonomy\Taxonomy;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class SearchForm extends Model
{
    public $text;
    public $taxonomy;

    public function rules(): array
    {
        return [
            [['text'], 'string'],
        ];
    }

    public function taxonomiesList(): array
    {
        return ArrayHelper::map(Taxonomy::find()->orderBy('lft')->asArray()->all(), 'id', static function (array $taxonomies) {
            return ($taxonomies['depth'] > 1 ? str_repeat('-- ', $taxonomies['depth'] - 1) . ' ' : '') . $taxonomies['name'];
        });
    }

    public function formName(): string
    {
        return '';
    }

}
