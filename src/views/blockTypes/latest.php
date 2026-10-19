<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

use Besnovatyj\Blocks\render\BlockViewContext;
use Besnovatyj\Blog\entities\Post;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * Блок «Последние посты» (тип `Blog:latest` модуля блоков), шаблон пакета.
 *
 * Нейтральная разметка Bootstrap: заголовок, сетка карточек, ссылка «все новости». Тема перекрывает
 * файл: `themes/{тема}/modules/Blog/views/blockTypes/latest.php`.
 *
 * Маршрут поста абсолютный (`/Blog/post/view`): блок выводится на чужой странице (главная — это
 * модуль Page), и относительный `view` указал бы на контроллёр страницы.
 *
 * Постов нет — блок не выводится вовсе, вместе с заголовком.
 */

/** @var View $this */
/** @var array{heading:string, columns:string, more:array, moreLabel:string} $data */
/** @var BlockViewContext $block */
/** @var Post[] $posts от {@see \Besnovatyj\Blog\blocks\LatestPostsData} */

if ($posts === []) {
    return;
}

$moreUrl = $block->linkUrl($data['more']);
?>
<section class="block-blog-latest">
    <?php if ($data['heading'] !== ''): ?>
        <h2><?= Html::encode($data['heading']) ?></h2>
    <?php endif; ?>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-<?= (int)$data['columns'] ?> g-4">
        <?php foreach ($posts as $post): ?>
            <div class="col">
                <div class="card h-100">
                    <img src="<?= Html::encode($post->getThumbUrl('photo', 'blog_list')) ?>" class="card-img-top"
                         alt="<?= Html::encode($post->title) ?>" loading="lazy">
                    <div class="card-body">
                        <h3 class="h5 card-title">
                            <a href="<?= Url::to(['/Blog/post/view', 'id' => $post->id]) ?>" class="stretched-link">
                                <?= Html::encode($post->title) ?>
                            </a>
                        </h3>
                        <p class="card-text small text-secondary"><?= Yii::$app->formatter->asDate($post->created_at, 'long') ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if ($moreUrl !== '' && $data['moreLabel'] !== ''): ?>
        <p class="mt-3"><?= Html::a(Html::encode($data['moreLabel']), $moreUrl) ?></p>
    <?php endif; ?>
</section>
