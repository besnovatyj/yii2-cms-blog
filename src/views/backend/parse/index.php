<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $model Besnovatyj\Blog\forms\backend\PostForm */

$this->title = 'Parse to post';
$this->params['breadcrumbs'][] = ['label' => 'Posts', 'url' => ['/Blog/backend/post/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs(file_get_contents(__DIR__ . '/parse_ajax.js'), View::POS_END);
?>

<style>
    .item {
        position: relative;
        padding-top: 5px;
        display: inline-block;
    }

    .item .img-size-64 {
        max-width: 64px;
    }

    .notify-badge {
        position: absolute;
        right: 5px;
        top: 9px;
        background: red;
        text-align: center;
        border-radius: 3px 3px 3px 3px;
        color: white;
        padding: 0 3px;
        font-size: 13px;
    }
</style>


<div class="row">
    <div class="col-12 col-md-6">
        <div class="card url-block">
            <div class="card-header"><?= $this->title ?></div>
            <div class="card-body">
                <?php echo Html::beginForm() ?>
                <?= Html::textInput('parse_url', '', ['class' => 'form-control source_url']) ?>
                <br/>
                <?= Html::tag('span', 'Preview', [
                    'class' => 'btn btn-success  btn-preview',
                    'action' => Url::to(['backend/parse/get-start-data'])
                ]) ?>
                <button class="text-clear btn btn-secondary " type="reset" title="Clear"
                        style=".search-box:not(:valid)~.text-clear{display: none;}">Clear
                </button>
                <?= Html::tag('a', 'Save to post', [
                    'class' => 'btn btn-success  btn-save float-right',
                    'action' => Url::to(['backend/parse/save-to-post'])
                ]) ?>
                <?php echo Html::endForm() ?>
            </div>
            <div class="card-footer">
                <a target="_blank" href="https://pikabu.ru/">pikabu.ru</a> |
                <a target="_blank" href="https://www.soundonsound.com/">www.soundonsound.com</a> |
                <a target="_blank" href="https://habr.com/ru/">habr.com</a>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card images-block">
            <div class="card-header card-title">Post images</div>
            <div class="card-body"></div>
            <div class="card-footer"></div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header parsed-title">Load...</div>
            <div class="card-body">
                <div class="parsed-content">...</div>
            </div>
            <div class="card-footer"></div>
        </div>
    </div>
</div>







