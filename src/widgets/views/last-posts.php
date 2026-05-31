<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/** @var $posts Besnovatyj\Blog\entities\Post[] */

use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;
?>

<div class="row">
    <?php foreach ($posts as $post): ?>
        <?php $url = Url::to(['/Blog/backend/post/post', 'id' =>$post->id]); ?>
        <div class="product-layout col-lg-3 col-md-3 col-sm-6 col-xs-12">
            <div class=" transition">
                <?php if ($post->photo): ?>
                    <div class="image">
                        <a href="<?= Html::encode($url) ?>">
                            <img src="<?= Html::encode($post->getThumbUrl('photo', 'widget_list')) ?>" alt="" class="img-responsive thumbnail" />
                        </a>
                    </div>
                <?php endif; ?>
                <div>
                    <div class="caption">
                        <h4><a href="<?= Html::encode($url) ?>"><?= Html::encode($post->title) ?></a></h4>
                        <p><?= Html::encode(StringHelper::truncateWords(strip_tags($post->description), 20)) ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
