<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\repositories;

use Besnovatyj\Blog\entities\Comment;
use RuntimeException;

class CommentRepository
{
    public function get(int $id): Comment
    {
        if (!$comment = Comment::findOne($id)) {
            throw new NotFoundException('Comment is not found.');
        }
        return $comment;
    }

    public function getByPostAndId(int $postId, int $id): Comment
    {
        $comment = Comment::find()
            ->andWhere(['id' => $id, 'post_id' => $postId])
            ->one();

        if (!$comment) {
            throw new NotFoundException('Comment is not found.');
        }
        return $comment;
    }

    public function save(Comment $comment): void
    {
        if (!$comment->save()) {
            throw new RuntimeException('Saving error.');
        }
    }

    public function remove(Comment $comment): void
    {
        if (!$comment->delete()) {
            throw new RuntimeException('Removing error.');
        }
    }
}
