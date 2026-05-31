<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use yii\helpers\Html;
use yii\helpers\Url;

/** @var $posts \Besnovatyj\Blog\entities\Post[] */
?>

<ul>
    <?php foreach ($posts as $post): ?>
        <li class="flex-w m-b-25">
            <?php if ($post->photo): ?>
                <div class="size16 bo-rad-10 wrap-pic-w of-hidden m-r-18">
                    <a href="<?= Url::to(['/Blog/backend/post/post', 'id' => $post->id]); ?>">
                        <img src="<?= $post->getThumbUrl('photo', 'popular') ?>" alt="">
                    </a>
                </div>
                <div class="size28">
                    <a href="<?= Url::to(['/Blog/backend/post/post', 'id' => $post->id]); ?>" class="dis-block txt28 m-b-8">
                        <?= Html::encode($post->title) ?>
                    </a>

                    <span class="txt14"><?= Yii::$app->formatter->asDate($post->created_at) ?></span>
                </div>
            <?php else: ?>
                <div class="size39">
                    <a href="<?= Url::to(['/Blog/backend/post/post', 'id' => $post->id]); ?>" class="dis-block txt28 m-b-8">
                        <?= Html::encode($post->title) ?>
                    </a>

                    <span class="txt14"><?= Yii::$app->formatter->asDate($post->created_at) ?></span>
                </div>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>
