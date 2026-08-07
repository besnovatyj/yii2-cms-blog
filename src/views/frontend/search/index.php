<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Blog\forms\frontend\search\SearchForm;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\LinkPager;
use yii\data\DataProviderInterface;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * Базовая вью фронтового поиска по блогу (пакетный фолбэк; тема может переопределить).
 *
 * @var View $this
 * @var DataProviderInterface $dataProvider
 * @var SearchForm $searchForm
 */

$this->title = 'Поиск по блогу';

$this->params['og:title'] = $this->title;

$this->params['breadcrumbs'][] = ['label' => 'Блог', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerMetaTag(['name' => 'title', 'content' => $this->title]);
$this->registerMetaTag(['name' => 'author', 'content' => Yii::$app->getModule('Config')->params['frontend']['app']['name']]);

$posts = $dataProvider->getModels();
?>
<div class="container py-4">
    <h1 class="h3 mb-3"><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
        'method' => 'get',
        'action' => ['/Blog/search/index'],
        'options' => ['class' => 'mb-4'],
    ]); ?>
    <?= $form->field($searchForm, 'text')->textInput([
        'placeholder' => 'Что ищем?',
        'maxlength' => true,
    ]) ?>
    <?= Html::submitButton('Найти', ['class' => 'btn btn-primary']) ?>
    <?php ActiveForm::end(); ?>

    <?php if ($posts === []): ?>
        <p class="text-muted mb-0">Ничего не найдено.</p>
    <?php else: ?>
        <div class="list-group mb-3">
            <?php foreach ($posts as $post): ?>
                <a class="list-group-item list-group-item-action"
                   href="<?= Html::encode(Url::to(['/Blog/post/view', 'id' => $post->id])) ?>">
                    <span class="fw-semibold"><?= Html::encode($post->title) ?></span>
                    <?php if ($post->description !== null && $post->description !== ''): ?>
                        <span class="d-block small text-muted"><?= Html::encode($post->description) ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?= LinkPager::widget([
            'pagination' => $dataProvider->getPagination(),
        ]) ?>
    <?php endif; ?>
</div>
