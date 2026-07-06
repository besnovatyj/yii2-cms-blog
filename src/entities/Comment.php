<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\entities;

use Besnovatyj\Blog\entities\Post;
use Besnovatyj\Blog\entities\queries\CommentQuery;
use Besnovatyj\User\entities\User;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $created_at
 * @property int $post_id
 * @property int $user_id
 * @property int $parent_id
 * @property string $text
 * @property int $active
 *
 * @property Post $post
 */
class Comment extends ActiveRecord
{
    public const STATUS_DRAFT = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_DELETED = 2;

    public static function create($userId, $parentId, $text): self
    {
        $review = new static();
        $review->user_id = $userId;
        $review->parent_id = $parentId;
        $review->text = $text;
        $review->created_at = time();

        //$review->active = $config->flag ? self::STATUS_DRAFT : self::STATUS_ACTIVE;
        $review->active = self::STATUS_DRAFT; //Премодерация комментариев блога. // TODO Besnovatyj move to  config component

        return $review;
    }

    public function edit($parentId, $text): void
    {
        $this->parent_id = $parentId;
        $this->text = $text;
    }

    public function activate(): void
    {
        $this->active = self::STATUS_ACTIVE;
    }

    public function draft(): void
    {
        $this->active = self::STATUS_DRAFT;
    }

    public function remove(): void
    {
        $this->active = self::STATUS_DELETED;
    }

    public function isActive(): bool
    {
        return $this->active === self::STATUS_ACTIVE;
    }

    public function isDraft(): bool
    {
        return $this->active === self::STATUS_DRAFT;
    }

    public function isRemoved(): bool
    {
        return $this->active === self::STATUS_DELETED;
    }

    public function isIdEqualTo($id): bool
    {
        return $this->id === $id;
    }

    public function isChildOf($id): bool
    {
        return $this->parent_id === $id;
    }

    public function getPost(): ActiveQuery
    {
        return $this->hasOne(Post::class, ['id' => 'post_id']);
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public static function tableName(): string
    {
        return '{{%blog_comments}}';
    }

    public static function find(): CommentQuery
    {
        return new CommentQuery(static::class);
    }
}
