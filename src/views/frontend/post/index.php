<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use yii\base\Module;
use yii\data\DataProviderInterface;
use yii\web\View;

/* @var $this View */
/* @var $dataProvider DataProviderInterface */

$this->title = 'Блог';

$this->params['og:title'] = $this->title;

$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->getModule('Config') instanceof Module) {
    $this->registerMetaTag(['name' => 'keywords', 'content' => \Yii::$app->getModule('Config')->params['frontend']['app']['keywords']]);
    $this->registerMetaTag(['name' => 'description', 'content' => \Yii::$app->getModule('Config')->params['frontend']['app']['description']]);
    $this->registerMetaTag(['name' => 'author', 'content' => \Yii::$app->getModule('Config')->params['frontend']['app']['name']]);
}

?>

<section class="container mt-3 mb-5">
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($dataProvider->getModels() as $model): ?>
            <div class="col">
                <?= $this->render('_post', [
                    'model' => $model,
                ]) ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-4 text-center">
        <?= $this->render("_pagination", ['dataProvider' => $dataProvider]) ?>
    </div>
</section>

