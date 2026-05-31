<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\helpers;

use Exception;
use Besnovatyj\Blog\entities\Post;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

class  PostHelper
{
    public static function statusList(): array
    {
        return [
            Post::STATUS_DRAFT => 'OFF',
            Post::STATUS_ACTIVE => 'ON',
        ];
    }

    public static function pinList(): array
    {
        return [
            Post::PINNED => 'Pinned',
            Post::UNPINNED => 'Unpinned',
        ];
    }

    public static function commentStatusList(): array
    {
        return [
            Post::COMMENTS_ALLOWED => 'Вкл',
            Post::COMMENTS_DISABLED => 'Выкл',
        ];
    }

    /**
     * @throws Exception
     */
    public static function statusName($status): string
    {
        return ArrayHelper::getValue(self::statusList(), $status);
    }

    /**
     * @throws Exception
     */
    public static function statusLabel($model): string
    {
        switch ($model->status) {
            case Post::STATUS_DRAFT:
                $class = 'badge rounded-pill text-bg-secondary float-right';
                $action = 'activate';
                break;
            case Post::STATUS_ACTIVE:
                $class = 'badge rounded-pill text-bg-success float-right';
                $action = 'draft';
                break;
            default:
                $class = 'badge rounded-pill text-bg-light float-right';
                $action = 'activate';
        }

        $text = Html::tag('span', ArrayHelper::getValue(self::statusList(), $model->status), [
            'class' => $class,
        ]);
        $url = Url::to(['/Blog/backend/post/' . $action, 'id' => $model->id]);
        return Html::a($text, $url, [
            'data' => [
                'confirm' => "Сменить статус?",
                'method' => 'post',
            ],
        ]);

    }

    /**
     * @throws Exception
     */
    public static function commentStatusLabel($model): string
    {
        $class = match ($model->comments_allowed) {
            Post::COMMENTS_DISABLED => 'badge bg-secondary',
            Post::COMMENTS_ALLOWED => 'badge bg-success',
            default => 'badge badge-light',
        };

        return Html::tag('span', ArrayHelper::getValue(self::commentStatusList(), $model->comments_allowed), [
            'class' => $class,
        ]);

    }

    /**
     * @throws Exception
     */
    public static function pinName($pinStatus): string
    {
        return ArrayHelper::getValue(self::pinList(), $pinStatus);
    }
}
