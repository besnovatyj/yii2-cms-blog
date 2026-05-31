<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/* @var $this yii\web\View */
/* @var $model \Besnovatyj\Blog\forms\backend\PostForm */

$this->title = 'Создать Post';
$this->params['breadcrumbs'][] = ['label' => 'Posts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="post-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
