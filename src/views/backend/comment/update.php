<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Blog\entities\Comment;
use Besnovatyj\Blog\entities\Post;
use Besnovatyj\Blog\forms\backend\CommentEditForm;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/* @var $this yii\web\View */
/* @var $post Post */
/* @var $comment Comment */
/* @var $model CommentEditForm */

$this->title = 'Update comment #' . $comment->id;
$this->params['breadcrumbs'][] = ['label' => 'Blog', 'url' => ['/Blog/backend/post/index']];
$this->params['breadcrumbs'][] = ['label' => $post->title, 'url' => ['/Blog/backend/post/view', 'id' => $post->id]];
$this->params['breadcrumbs'][] = ['label' => '#' . $comment->id, 'url' => ['/Blog/backend/comment/view', 'id' => $comment->id, 'post_id' => $comment->post_id]];
$this->params['breadcrumbs'][] = 'update';
?>
<div class="card">
    <div class="card-header"><?= $this->title ?></div>
    <!-- /.card-header -->
    <div class="card-body">
        <?php $form = ActiveForm::begin([
            'options' => ['enctype' => 'multipart/form-data']
        ]); ?>
        <?= $form->field($model, 'parentId')->textInput(['class'=>'form-control']) ?>
        <?= $form->field($model, 'text')->textarea(['rows' => 5, 'class'=>'form-control']) ?>
        <?php ActiveForm::end(); ?>
    </div>
    <!-- /.card-body -->
    <div class="card-footer clearfix">
        <div class="form-group">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
        </div>
    </div>
</div>
<!-- /.card -->
