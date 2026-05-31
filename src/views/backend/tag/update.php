<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/* @var $this yii\web\View */
/* @var $tag Besnovatyj\Blog\entities\Tag */
/* @var $model Besnovatyj\Blog\forms\backend\TagForm */

$this->title = 'Edit tag: ' . $tag->name;
$this->params['breadcrumbs'][] = ['label' => 'Tags', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $tag->name, 'url' => ['view', 'id' => $tag->id]];
$this->params['breadcrumbs'][] = 'Edit';
?>
<div class="tag-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
