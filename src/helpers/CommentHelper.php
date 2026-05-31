<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\helpers;

use Exception;
use Besnovatyj\Blog\entities\Comment;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class CommentHelper
{
    public static function statusList(): array
    {
        return [
            Comment::STATUS_DRAFT => 'Ожидает',
            Comment::STATUS_ACTIVE => 'Активен',
            Comment::STATUS_DELETED => 'Удалён',
        ];
    }

    /**
     * @throws Exception
     */
    public static function statusLabel($model): string
    {
        $class = match ($model->active) {
            Comment::STATUS_DRAFT => 'badge badge-warning',
            Comment::STATUS_ACTIVE => 'badge bg-success',
            Comment::STATUS_DELETED => 'badge bg-secondary',
            default => 'badge badge-light',
        };

        return Html::tag('span', ArrayHelper::getValue(self::statusList(), $model->active), [
            'class' => $class,
        ]);
    }

}
