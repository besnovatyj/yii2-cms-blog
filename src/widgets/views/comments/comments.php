<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Blog\entities\Post;
use Besnovatyj\Blog\forms\frontend\CommentForm;
use Besnovatyj\Blog\widgets\CommentView;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $post Post */
/* @var $items CommentView[] */
/* @var $count integer */
/* @var $commentForm CommentForm */
?>

<div id="comments" class="inner-bottom-xs">
    <h2>Комментарии:</h2>
    <?php foreach ($items as $item): ?>
        <?= $this->render('_comment', ['item' => $item]) ?>
    <?php endforeach; ?>
</div>
<div id="reply-block" class="leave-reply">
    <?php $form = ActiveForm::begin([
        'action' => ['comment', 'id' => $post->id],
    ]); ?>

    <?= Html::activeHiddenInput($commentForm, 'parentId') ?>
    <?= $form->field($commentForm, 'text')->textarea(['rows' => 5, 'class'=>'form-control']) ?>

    <div class="form-group">
        <?= Html::submitButton('Отправить комментарий', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
