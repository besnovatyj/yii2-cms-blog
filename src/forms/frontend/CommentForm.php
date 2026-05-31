<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\forms\frontend;

use Besnovatyj\Helpers\StringHelper;
use yii\base\Model;

class CommentForm extends Model
{
    public $parentId;
    public $text;

    public function beforeValidate(): bool
    {
        $this->text = StringHelper::spaceReplace($this->text);
        return parent::beforeValidate();
    }

    public function rules(): array
    {
        return [
            [['text'], 'required'],
            ['text', 'string'],
            ['parentId', 'integer'],
        ];
    }

      public function attributeLabels(): array
      {
        return [
            'text' => 'Текст комментария',
        ];
    }

}
