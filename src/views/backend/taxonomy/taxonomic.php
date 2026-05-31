<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Blog\entities\Post;
use Besnovatyj\Blog\helpers\PostHelper;
use Besnovatyj\Backend\Widgets\pagination\LinkPager;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $this yii\web\View */
?>

<div class="card">
    <div class="card-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{summary}\n{items}",
            'columns' => [
                'id',
                [
                    'attribute' => 'name',
                    'value' => static function (Post $model) {
                        return Html::a($model->title, ['/Blog/post/view', 'id' => $model->id]);
                    },
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'status',
                    'value' => static function (Post $model) {
                        return PostHelper::statusLabel($model);
                    },
                    'format' => 'raw',
                ],
            ],
        ]); ?>
    </div>
    <!-- /.card-body -->
    <div class="card-footer clearfix">
        <nav aria-label="" class="nav-pagination">
            <?= LinkPager::widget([
                'pagination' => $dataProvider->getPagination(),
            ]) ?>
        </nav>
    </div>
</div>
<!-- /.card -->
