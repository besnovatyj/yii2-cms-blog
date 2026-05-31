<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Blog\entities\Comment;
use Besnovatyj\Blog\forms\backend\search\CommentSearch;
use Besnovatyj\Backend\Widgets\grid\ActionColumn;
use modules\user\components\Helper;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\StringHelper;
use Besnovatyj\Blog\helpers\CommentHelper;
use yii\web\View;

/* @var $this View */
/* @var $searchModel \Besnovatyj\Blog\forms\backend\search\CommentSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = 'Комментарии';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card">
    <div class="card-header"><?=$this->title?></div>
    <!-- /.card-header -->
    <div class="card-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                'id',
                'created_at:datetime',
                [
                    'attribute' => 'text',
                    'value' => static function (Comment $model) {
                        return StringHelper::truncate(strip_tags($model->text), 100);
                    },
                ],
                [
                    'attribute' => 'active',
                    'filter' => $searchModel->statusList(),
                    'value' => static function (Comment $model) {
                        return CommentHelper::statusLabel($model);
                    },
                    'format' => 'raw',
                ],
                ['class' => ActionColumn::class,
                    'template' => Helper::filterActionColumn(['view', 'update', 'delete',]),
                ],
            ],
        ]); ?>
    </div>
    <!-- /.card-body -->
    <div class="card-footer clearfix">

    </div>
</div>
<!-- /.card -->
