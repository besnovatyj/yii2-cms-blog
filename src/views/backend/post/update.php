<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use yii\web\View;
use Besnovatyj\Blog\entities\Post;
use Besnovatyj\Blog\forms\backend\PostForm;

/* @var View $this */
/* @var Post $post */
/* @var PostForm $model */

$this->title = 'Update Post: ' . $post->title;
$this->params['breadcrumbs'][] = ['label' => 'Posts', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $post->title, 'url' => ['view', 'id' => $post->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<?= $this->render('_form', [
    'model' => $model,
    'post' => $post,
]) ?>
