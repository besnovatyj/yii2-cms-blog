<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $post \Besnovatyj\Blog\entities\Post */
/* @var $frontendUrl string */
/* @var $absoluteFrontendUrl string */

$urlManager = Yii::$app->get('frontendUrlManager');

?>

<div class="btn-toolbar mb-2">
    <div class="btn-group me-2">
        <?php if ($post->isActive()): ?>
            <?= Html::a('Active', ['draft', 'id' => $post->id], ['class' => 'btn btn-success', 'data-method' => 'post']) ?>
        <?php else: ?>
            <?= Html::a('Draft', ['activate', 'id' => $post->id], ['class' => 'btn btn-secondary', 'data-method' => 'post']) ?>
        <?php endif; ?>
        <?php if ($post->isPinned()): ?>
            <?= Html::a('Pinned', ['pin-toggle', 'id' => $post->id], ['class' => 'btn btn-warning', 'data-method' => 'post']) ?>
        <?php else: ?>
            <?= Html::a('Unpinned', ['pin-toggle', 'id' => $post->id], ['class' => 'btn btn-secondary', 'data-method' => 'post']) ?>
        <?php endif; ?>

        <?= Html::a('Update', ['update', 'id' => $post->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $post->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure?',
                'method' => 'post',
            ],
        ]) ?>

        <?= \Besnovatyj\Menu\widgets\add\AddItemWidget::widget([
            'endpoint' => \yii\helpers\Url::to('/Menu/tree/create-node', true),
            'link' => $frontendUrl,
            'name' => $post->title,
        ]) ?>

        <a class="btn btn-orange" target="_blank"
           href="<?= $absoluteFrontendUrl; ?>">
            <i class="bi bi-eye"></i>
        </a>
    </div>
</div>

