<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\services;

use DomainException;
use Besnovatyj\Blog\entities\Comment;
use Besnovatyj\Blog\forms\frontend\CommentForm;
use Besnovatyj\Blog\repositories\CommentRepository;
use Besnovatyj\Blog\repositories\PostRepository;
use modules\user\repositories\UserRepository;

class CommentService
{
    private PostRepository $posts;
    private UserRepository $users;
    private CommentRepository $comments;

    public function __construct(
        PostRepository $posts,
        UserRepository $users,
        CommentRepository $comments
    ) {
        $this->posts = $posts;
        $this->users = $users;
        $this->comments = $comments;
    }

    public function create(int $postId, int $userId, CommentForm $form): Comment
    {
        $post = $this->posts->get($postId);
        $user = $this->users->get($userId);

        if ($form->parentId) {
            $parent = $this->comments->getByPostAndId($postId, $form->parentId);
            if (!$parent->isActive()) {
                throw new DomainException('Нельзя отвечать на неактивный комментарий.');
            }
        }

        $comment = Comment::create($user->id, $form->parentId, $form->text);
        $comment->post_id = $post->id;

        $this->comments->save($comment);

        $post->updateCommentsCount();
        $this->posts->save($post);

        return $comment;
    }
}
