<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\forms\backend\elasticSearch;

use yii\base\Model;

/**
 * @property integer $id
 */
class TagForm extends Model
{

    public $text;

    public function rules(): array
    {
        return [
            ['text', 'string'],
        ];
    }

    public function isFilled(): bool
    {
        return !empty($this->text);
    }

    public function formName(): string
    {
        return 't';
    }
}
