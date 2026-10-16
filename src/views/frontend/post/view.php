<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Blog\entities\Post;
use Besnovatyj\Blog\widgets\CommentsWidget;
use yii\base\Module;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $post Post */
/* @var $previous Post|null */
/* @var $next Post|null */

$this->title = $post->title;

$this->params['og:title'] = $this->title;

$this->params['breadcrumbs'][] = ['label' => 'Блог', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $post->taxonomy->name, 'url' => ['taxonomy', 'slug' => $post->taxonomy->slug]];
$this->params['breadcrumbs'][] = $post->title;

$this->registerMetaTag(['name' => 'title', 'content' => $post->getSeoTitle()]);
$this->registerMetaTag(['name' => 'description', 'content' => $post->meta->description]);
$this->registerMetaTag(['name' => 'keywords', 'content' => $post->meta->keywords]);

if (Yii::$app->getModule('Config') instanceof Module) {
    $this->registerMetaTag(['name' => 'author', 'content' => Yii::$app->getModule('Config')->params['frontend']['app']['name']]);
}

?>

<article class="container mt-3 mb-5">
    <header class="mb-4">
        <h1><?= Html::encode($post->title) ?></h1>
        <p class="text-secondary small mb-0">
            <?= Yii::$app->formatter->asDateTime($post->created_at, 'yyyy-MM-dd HH:mm') ?>
        </p>
    </header>

    <figure class="figure w-100 text-center mb-4">
        <img src="<?= $post->getThumbUrl('photo', 'blog_list') ?>" class="figure-img img-fluid rounded shadow-sm"
             alt="<?= Html::encode($post->title) ?>">
        <figcaption class="figure-caption"><?= Html::encode($post->title) ?></figcaption>
    </figure>

    <div class="mb-4">
        <?= $post->content ?>
    </div>

    <?php if (count($post->tags)): ?>
        <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
            <span class="text-secondary me-1">Теги:</span>
            <?php foreach ($post->tags as $tag): ?>
                <a href="<?= Url::to(['tag', 'slug' => $tag->slug]) ?>"
                   class="badge text-bg-secondary text-decoration-none">
                    <?= Html::encode($tag->name) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($previous !== null || $next !== null): ?>
        <?php // ms-sm-auto у «следующего» держит его справа, даже когда «предыдущего» нет. ?>
        <nav class="d-flex flex-column flex-sm-row gap-2 mb-4" aria-label="Соседние посты">
            <?php if ($previous !== null): ?>
                <a href="<?= Url::to(['view', 'id' => $previous->id]) ?>" rel="prev"
                   class="btn btn-outline-secondary text-start">
                    &larr; <?= Html::encode($previous->title) ?>
                </a>
            <?php endif; ?>
            <?php if ($next !== null): ?>
                <a href="<?= Url::to(['view', 'id' => $next->id]) ?>" rel="next"
                   class="btn btn-outline-secondary text-end ms-sm-auto">
                    <?= Html::encode($next->title) ?> &rarr;
                </a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>

    <?= CommentsWidget::widget([
        'post' => $post,
    ]) ?>
</article>
