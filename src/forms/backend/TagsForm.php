<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\forms\backend;

use Besnovatyj\Helpers\StringHelper;
use Besnovatyj\Blog\entities\Post;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class TagsForm extends Model
{
    public array $newTagsNames = [];

    public function __construct(?Post $post = null, $config = [])
    {
        if ($post) {
            $this->newTagsNames = ArrayHelper::map($post->tags, 'id', 'name');
        }
        parent::__construct($config);
    }

    public function beforeValidate(): bool
    {
        $this->newTagsNames = array_filter(array_map(static function ($tagName) {
            return StringHelper::spaceReplace($tagName);
        }, array_values($this->newTagsNames)
        ));

        return parent::beforeValidate();
    }

    public function rules(): array
    {
        return [
            ['newTagsNames', 'each', 'rule' => ['string', 'length' => [0, 255]]],
        ];
    }

}
