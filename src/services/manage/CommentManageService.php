<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\services\manage;

use DomainException;
use Besnovatyj\Blog\entities\Comment;
use Besnovatyj\Blog\forms\backend\CommentEditForm;
use Besnovatyj\Blog\repositories\CommentRepository;
use Besnovatyj\Blog\repositories\PostRepository;

class CommentManageService
{
    private PostRepository $posts;
    private CommentRepository $comments;

    public function __construct(PostRepository $posts, CommentRepository $comments)
    {
        $this->posts = $posts;
        $this->comments = $comments;
    }

    public function edit(int $postId, int $id, CommentEditForm $form): void
    {
        $comment = $this->comments->getByPostAndId($postId, $id);

        if ($form->parentId) {
            $parent = $this->comments->getByPostAndId($postId, $form->parentId);
            if (!$parent->isActive()) {
                throw new DomainException('Cannot reply to inactive comment.');
            }
        }

        $comment->edit($form->parentId, $form->text);
        $this->comments->save($comment);
    }

    public function activate(int $postId, int $id): void
    {
        $comment = $this->comments->getByPostAndId($postId, $id);
        $comment->activate();
        $this->comments->save($comment);

        $this->updatePostCommentsCount($postId);
    }

    public function draft(int $postId, int $id): void
    {
        $comment = $this->comments->getByPostAndId($postId, $id);
        $comment->draft();
        $this->comments->save($comment);

        $this->updatePostCommentsCount($postId);
    }

    public function remove(int $postId, int $id): void
    {
        $comment = $this->comments->getByPostAndId($postId, $id);
        $comment->remove();
        $this->comments->save($comment);

        $this->updatePostCommentsCount($postId);
    }

    private function updatePostCommentsCount(int $postId): void
    {
        $post = $this->posts->get($postId);
        $post->updateCommentsCount();
        $this->posts->save($post);
    }
}
