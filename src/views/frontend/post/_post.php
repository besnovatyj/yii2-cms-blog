<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Blog\entities\Post;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $model Post */

?>

<div class="card h-100 shadow-sm">
    <img src="<?= Html::encode($model->getThumbUrl('photo', 'blog_list')) ?>"
         class="card-img-top" alt="<?= Html::encode($model->title) ?>"
         title="<?= Html::encode($model->title) ?>">
    <div class="card-body d-flex flex-column">
        <h2 class="h5 card-title">
            <?= Html::encode(StringHelper::truncateWords($model->title, 8, '...', true)) ?>
        </h2>
        <p class="card-text"><?= Html::encode(StringHelper::truncate($model->description, 100)) ?></p>

        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <?php if ($model->isPinned()): ?>
                <span class="badge text-bg-warning">Закреплено</span>
            <?php endif; ?>
            <?php if ($model->comments_count > 0): ?>
                <span class="badge text-bg-light border">
                    Комментариев: <?= Html::encode($model->comments_count) ?>
                </span>
            <?php endif; ?>
            <span class="text-secondary small">
                <?= Yii::$app->formatter->asRelativeTime($model->created_at, time()) ?>
            </span>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-3">
            <?php if (isset($model->taxonomy->name)): ?>
                <a href="<?= Url::to(['taxonomy', 'slug' => $model->taxonomy->slug]) ?>"
                   class="badge text-bg-info text-decoration-none">
                    <?= Html::encode($model->taxonomy->name) ?>
                </a>
            <?php endif; ?>
            <?php foreach ($model->tags as $tag): ?>
                <a href="<?= Url::to(['tag', 'slug' => $tag->slug]) ?>"
                   class="badge text-bg-secondary text-decoration-none">
                    <?= Html::encode($tag->name) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <a href="<?= Url::to(['view', 'id' => $model->id]) ?>" class="btn btn-primary mt-auto align-self-start">
            Читать
        </a>
    </div>
</div>
