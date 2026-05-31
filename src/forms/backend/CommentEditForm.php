<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\forms\backend;

use Besnovatyj\Helpers\StringHelper;
use Besnovatyj\Blog\entities\Comment;
use yii\base\Model;

class CommentEditForm extends Model
{
    public $parentId;
    public $text;

    public function __construct(Comment $comment, $config = [])
    {
        $this->parentId = $comment->parent_id;
        $this->text = $comment->text;
        parent::__construct($config);
    }

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
}
