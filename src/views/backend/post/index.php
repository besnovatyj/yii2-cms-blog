<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Blog\forms\backend\search\PostSearch;
use Besnovatyj\Blog\grid\PostGrid;
use Besnovatyj\Backend\Widgets\pagination\LinkPager;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel PostSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('Blog', 'Posts');
$this->params['breadcrumbs'][] = $this->title;
// Фильтры в оффканвас для мобилки: строка фильтров таблицы там скрыта (grid-view.scss)
$this->params['mobileFiltersForm'] = $this->render('_mobileFilters', ['model' => $searchModel]);
//core\widgets\multiselect\Widget::widget(['id' => 'postsearch-taxonomy_id']);
//$this->registerJs('$(document).ready(function() {$("#postsearch-taxonomy_id").multiselect();});');

// Спиннер htmx-кнопок колонок статуса и закрепления (PostGrid::htmxButton)
$this->registerCss('
.blog-post-grid .htmx-indicator {
    display: none;
}
.blog-post-grid .htmx-request .htmx-indicator {
    display: inline-block;
}
/* Иконку на время запроса скрываем, чтобы кнопка не растягивалась */
.blog-post-grid .htmx-request i {
    display: none;
}

/* Мобильные карточки (дополнение к grid-view.scss админки, тот же брейкпоинт) */
@media (max-width: 767.98px) {
    /* Длинная ячейка: подпись сверху, содержимое под ней на всю ширину карточки */
    .blog-post-grid td.' . PostGrid::CELL_STACK . ' {
        flex-direction: column;
        align-items: stretch;
        text-align: left;
    }
    .blog-post-grid td.' . PostGrid::CELL_STACK . '::before {
        margin-right: 0;
        margin-bottom: .25rem;
    }
    .blog-post-grid td.' . PostGrid::CELL_STACK . ' > div {
        min-width: 0;
        overflow-wrap: anywhere;
    }
    /* Кнопки переносятся по строкам */
    .blog-post-grid td.' . PostGrid::CELL_ACTIONS . ' {
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: .25rem;
    }
}
');
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
            'layout' => "
                <div class='d-flex justify-content-between align-items-center mb-3'>
                    {summary}
                    <button class='btn btn-primary d-md-none' type='button' data-bs-toggle='offcanvas' data-bs-target='#offcanvasFilters'>
                        <i class='bi bi-funnel'></i> Фильтры
                    </button>
                </div>
                {items}
            ",
            'options' => ['class' => 'grid-view blog-post-grid'],
            'tableOptions' => ['class' => PostGrid::TABLE_CLASS],
            'columns' => PostGrid::columns(Yii::$app->get('frontendUrlManager'), $searchModel),
        ]) ?>
    </div>
    <div class="card-footer">
        <?= LinkPager::widget([
            'pagination' => $dataProvider->getPagination(),
        ]) ?>
    </div>
</div>
