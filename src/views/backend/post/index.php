<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Blog\entities\Post;
use Besnovatyj\Blog\entities\taxonomy\Taxonomy;
use Besnovatyj\Blog\forms\backend\search\PostSearch;
use Besnovatyj\Blog\helpers\PostHelper;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;
use Besnovatyj\DateTime\DateTimeRangeWidget;
use Besnovatyj\Backend\Widgets\pagination\LinkPager;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel PostSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('Blog', 'Posts');
$this->params['breadcrumbs'][] = $this->title;
//core\widgets\multiselect\Widget::widget(['id' => 'postsearch-taxonomy_id']);
//$this->registerJs('$(document).ready(function() {$("#postsearch-taxonomy_id").multiselect();});');
?>

<?php echo $this->render('_search', ['model' => $searchModel]); ?>

<div class="card mt-3">
    <div class="card-header">
        Posts list
    </div>
    <div class="card-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => "{summary}\n{items}",
            'columns' => [
                'id',
                [
                    'value' => static function (Post $model) {
                        return $model->photo ? Html::img($model->getThumbUrl('photo', 'admin')) : null;
                    },
                    'format' => 'raw',
                    'contentOptions' => ['data-label' => 'Photo'],
                ],
                [
                    'attribute' => 'created_at',
                    'value' => function (Post $model) {
                        return $model->created_at;
                    },
                    'filter' => DateTimeRangeWidget::widget([
                        'model' => $searchModel,
                        'attributeFrom' => 'date_from',
                        'attributeTo' => 'date_to',
                    ]),
                    'format' => 'datetime',
                ],
                [
                    'label' => 'Title or desc',
                    'attribute' => 'title',
                    'value' => static function (Post $model) {
                        $out = Html::a($model->title, ['view', 'id' => $model->id]);
                        return $out . '<br /><small>' . $model->description . '</small>';
                    },
                    'format' => 'raw',
                    'contentOptions' => ['data-label' => 'Title or desc'],
                ],
                [
                    'attribute' => 'taxonomy_id',
                    'filter' => new TreeQueryScope(Taxonomy::class)->dropdownTree(),
                    'value' => 'taxonomy.name',
                    'contentOptions' => ['data-label' => 'Taxonomy'],
                ],
                [
                    'attribute' => 'status',
                    'filter' => $searchModel->statusList(),
                    'value' => static function (Post $model) {
                        return PostHelper::statusLabel($model);
                    },
                    'format' => 'raw',
                    'contentOptions' => ['style' => 'min-height: 40px', 'data-label' => 'Status'],
                ],
//                    [
//                        'attribute' => 'comments_allowed',
//                        'filter' => $searchModel->commentStatusList(),
//                        'value' => function (Post $model) {
//                            return PostHelper::commentStatusLabel($model);
//                        },
//                        'format' => 'raw',
//                    ],
                'views',
//                    'comments_count',
            ],
        ]) ?>
    </div>
    <div class="card-footer">
        <?= LinkPager::widget([
            'pagination' => $dataProvider->getPagination(),
        ]) ?>
    </div>
</div>
