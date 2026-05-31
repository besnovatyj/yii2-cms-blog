<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Blog\forms\backend\search\PostSearch;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

/**
 * @var View $this
 * @var PostSearch $model
 * @var ActiveForm $form
 */
?>

<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
]); ?>
<div class="card">
    <div class="card-header">
        Instruments
    </div>
    <div class="card-body">
        <?= $form->field($model, 'content', [
            'inputTemplate' => '<div class="input-group mb-3">{input}<button class="btn btn-secondary" type="button" id=' . $form->id . '>
                                <i class="bi bi-search text-white"></i></button></div>',])
            ->textInput(['class' => 'form-control', 'placeholder' => 'Search in content', 'aria-label' => "Search in content", 'aria-describedby' => $form->id])
            ->label(false) ?>
    </div>
    <div class="card-footer">
        <?= Html::a('New', ['create'], ['class' => 'btn  btn-success ']) ?>
        <?= Html::a('Parse', ['backend/parse/index'], ['class' => 'btn  btn-warning']) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>
