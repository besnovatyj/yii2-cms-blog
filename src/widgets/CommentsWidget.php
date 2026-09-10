<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\widgets;

use Besnovatyj\Blog\entities\Comment;
use Besnovatyj\Blog\entities\Post;
use Besnovatyj\Blog\forms\frontend\CommentForm;
use Besnovatyj\Blog\widgets\CommentView;
use yii\base\InvalidConfigException;
use yii\base\Widget;

class CommentsWidget extends Widget
{
    /** @var Post */
    public $post;

    public function init(): void
    {
        if (!$this->post) {
            throw new InvalidConfigException('Уточните пост.');
        }
    }

    public function run(): string
    {
        if (!$this->post->isCommentsAllowed()) return false;

        $form = new CommentForm();

        // Только прошедшие модерацию: связь `getComments()` отдаёт все статусы, включая
        // ожидающие проверки (`STATUS_DRAFT`, он же статус по умолчанию у нового комментария)
        // и удалённые. Без этого фильтра любой отправленный текст сразу виден на странице.
        $comments = $this->post->getComments()
            ->active()
            ->orderBy(['parent_id' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        $items = $this->treeRecursive($comments, null);

        return $this->render('comments/comments', [
            'post' => $this->post,
            'items' => $items,
            'commentForm' => $form,
        ]);
    }

    /**
     * @param \Besnovatyj\Blog\entities\Comment[] $comments
     * @param integer $parentId
     * @return CommentView[]
     */
    public function treeRecursive(&$comments, $parentId): array
    {
        $items = [];
        foreach ($comments as $comment) {
            if ($comment->parent_id == $parentId) {
                $items[] = new CommentView($comment, $this->treeRecursive($comments, $comment->id));
            }
        }
        return $items;
    }
}
