<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use yii\bootstrap5\LinkPager;
use yii\data\DataProviderInterface;
use yii\web\View;

/* @var $this View */
/* @var $dataProvider DataProviderInterface */

?>

<?= LinkPager::widget([
    'pagination' => $dataProvider->getPagination(),
    'options' => [
        'class' => 'pagination',
    ],
    'linkOptions' => ['class' => 'page-link'],
]) ?>
