<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Blog\entities\Tag;
use Besnovatyj\Blog\forms\backend\search\TagSearch;
use Besnovatyj\Backend\Widgets\grid\ActionColumn;
use Besnovatyj\User\components\Helper;
use Besnovatyj\Backend\Widgets\pagination\LinkPager;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\View;

/* @var $this View */
/* @var $searchModel TagSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = 'Empty tags';
$this->params['breadcrumbs'][] = ['label' => 'Tags', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<p>
    <?= Html::a('Delete empty tags', ['delete-empty-tags'], ['class' => 'btn  btn-danger']) ?>
</p>
<div class="card">
    <div class="card-header"><?= $this->title ?></div>
    <div class="card-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
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
                ['class' => ActionColumn::class,
                    'template' => Helper::filterActionColumn(['view', 'update', 'delete',]),
                ],
            ],
        ]) ?>
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
