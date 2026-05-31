<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Blog\entities\Tag;
use Besnovatyj\Backend\Widgets\grid\ActionColumn;
use Besnovatyj\Blog\forms\backend\search\TagSearch;
use modules\user\components\Helper;
use Besnovatyj\Backend\Widgets\pagination\LinkPager;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel \Besnovatyj\Blog\forms\backend\search\TagSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Tags';
$this->params['breadcrumbs'][] = $this->title;
?>
<p>
    <?= Html::a('Find empty tags', ['empty-tags'], ['class' => 'btn  btn-warning']) ?>
</p>

<div class="card">
    <div class="card-header"><?= $this->title ?></div>
    <div class="card-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => "{summary}\n{items}",
            'columns' => [
                'id',
                [
                    'attribute' => 'name',
                    'value' => static function (Tag $model) {
                        return Html::a(Html::encode($model->name), ['view', 'id' => $model->id]);
                    },
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'freq_of_use',
                    'value' => static function (Tag $model) {
                        $out = '';
                        foreach ($model->tagAssignments as $item) {
                            $out .= $item->post_id . '; ';
                        }
                        return $out;
                    },
                    'format' => 'raw',
                ],
                'slug',
                ['class' => ActionColumn::class,
                    'template' => Helper::filterActionColumn(['view', 'update', 'delete',]),
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
