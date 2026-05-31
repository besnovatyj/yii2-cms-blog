<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Blog\entities\Comment;
use Besnovatyj\Blog\entities\Post;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $post Post */
/* @var $comment Comment */

$this->title = 'Коммент#' . $comment->id;
$this->params['breadcrumbs'][] = ['label' => 'Блог', 'url' => ['/Blog/backend/post/index']];
$this->params['breadcrumbs'][] = ['label' => $post->title, 'url' => ['/Blog/backend/post/view', 'id' => $post->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<p>
    <?= Html::a('Изменить', ['update', 'post_id' => $post->id, 'id' => $comment->id], ['class' => 'btn  btn-primary']) ?>
    <?php if ($comment->isActive()): ?>
        <?= Html::a('Скрыть', ['draft', 'post_id' => $post->id, 'id' => $comment->id], [
            'class' => 'btn  btn-warning',
            'data' => [
                'method' => 'post',
            ],
        ]) ?>
    <?php endif; ?>
    <?php if (!$comment->isActive()): ?>
        <?= Html::a('Активировать', ['activate', 'post_id' => $post->id, 'id' => $comment->id], [
            'class' => 'btn  btn-success',
            'data' => [
                'method' => 'post',
            ],
        ]) ?>
    <?php endif; ?>
    <?php if (!$comment->isRemoved()): ?>
        <?= Html::a('Удалить', ['delete', 'post_id' => $post->id, 'id' => $comment->id], [
            'class' => 'btn  btn-danger',
            'data' => [
                'confirm' => 'Вы уверенны в удалении комментария?',
                'method' => 'post',
            ],
        ]) ?>
    <?php endif; ?>
</p>

<div class="card">
    <div class="card-header"><?= $this->title ?></div>
    <!-- /.card-header -->
    <div class="card-body">
        <?= DetailView::widget([
            'model' => $comment,
            'attributes' => [
                'id',
                'created_at:datetime',
                [
                    'attribute' => 'active',
                    'value' => $comment->active,
                ],
                'user_id',
                'parent_id',
                [
                    'attribute' => 'post_id',
                    'value' => $post->title,
                ],
            ],
        ]) ?>
    </div>
    <!-- /.card-body -->
    <div class="card-footer clearfix">
        <?= Yii::$app->formatter->asNtext($comment->text) ?>
    </div>
</div>
<!-- /.card -->
