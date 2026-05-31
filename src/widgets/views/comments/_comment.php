<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Blog\widgets\CommentView;

/* @var $item \Besnovatyj\Blog\widgets\CommentView */

?>

<div class="comment-item" data-id="<?= $item->comment->id ?>">
    <a id="comment_<?= $item->comment->id ?>"></a>
    <div class="panel panel-default">
        <div class="panel-body">
            <p class="comment-content">
                <?php if ($item->comment->isActive()): ?>
                    <?= Yii::$app->formatter->asNtext($item->comment->text) ?>
                <?php elseif ($item->comment->isDraft()): ?>
                    <i>Комментарий ожидает модерации.</i>
                <?php else: ?>
                    <i>Комментарий удалён.</i>
                <?php endif; ?>
            </p>
            <div>
                <div class="pull-left">
                    <?= Yii::$app->formatter->asDatetime($item->comment->created_at) ?>
                </div>
                <div class="pull-right">
                    <span class="comment-reply">Ответить</span>
                </div>
            </div>
        </div>
    </div>
    <div class="margin">
        <div class="reply-block"></div>
        <div class="comments">
            <?php foreach ($item->children as $children): ?>
                <?= $this->render('_comment', ['item' => $children]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
