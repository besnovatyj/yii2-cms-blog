<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/**
 * Форма фильтров списка постов для оффканваса на мобилке.
 *
 * Выводится layout'ом админки (`layouts/_grid_filters.php`) через `$this->params['mobileFiltersForm']`;
 * кнопка «Применить» там привязана к форме по id `mobile-filter-form`.
 */

use Besnovatyj\Blog\entities\taxonomy\Taxonomy;
use Besnovatyj\Blog\forms\backend\search\PostSearch;
use Besnovatyj\DateTime\DateTimeRangeWidget;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;
use yii\bootstrap5\ActiveForm;
use yii\web\View;

/* @var $this View */
/* @var $model PostSearch */

?>

<?php $form = ActiveForm::begin([
    'id'     => 'mobile-filter-form',
    'action' => ['index'],
    'method' => 'get',
]); ?>

<?= $form->field($model, 'title')->textInput()->label('Title or desc') ?>

<?= $form->field($model, 'date_from')->widget(DateTimeRangeWidget::class, [
    'attributeFrom' => 'date_from',
    'attributeTo'   => 'date_to',
    'valueFormat'   => PostSearch::DATE_FORMAT,
])->label('Created') ?>

<?= $form->field($model, 'taxonomy_id')->dropDownList(
    new TreeQueryScope(Taxonomy::class)->dropdownTree(),
    ['prompt' => 'Все']
)->label('Taxonomy') ?>

<?= $form->field($model, 'status')->dropDownList(
    $model->statusList(),
    ['prompt' => 'Любой']
)->label('Status') ?>

<?php ActiveForm::end(); ?>
