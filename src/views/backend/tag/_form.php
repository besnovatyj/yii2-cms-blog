<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Blog\forms\backend\TagForm;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\web\View;

/* @var $this View */
/* @var $model TagForm */
/* @var $form ActiveForm */
?>

<?php $form = ActiveForm::begin(); ?>
<div class="card">
    <div class="card-header"><?= $this->title ?></div>
    <div class="card-body">
        <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'class'=>'form-control']) ?>
        <?= $form->field($model, 'slug')->textInput(['maxlength' => true, 'disabled' => true]) ?>
    </div>
    <div class="card-footer clearfix">
        <div class="form-group">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>

