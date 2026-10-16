<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Blog\grid;

use Besnovatyj\Backend\Widgets\grid\ActionColumn;
use Besnovatyj\Blog\entities\Post;
use Besnovatyj\Blog\entities\taxonomy\Taxonomy;
use Besnovatyj\Blog\forms\backend\search\PostSearch;
use Besnovatyj\Blog\helpers\PostHelper;
use Besnovatyj\DateTime\DateTimeRangeWidget;
use Besnovatyj\Kernel\security\AccessHelper;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\UrlManager;

/**
 * Колонки GridView списка постов в админке.
 *
 * Единственное место описания колонок: из него строится таблица в `views/backend/post/index.php`
 * и отдельная строка для htmx-ответов контроллёра ({@see renderRow()}), поэтому строка после
 * переключения статуса/закрепления по разметке совпадает с исходной.
 *
 * `data-label` задаётся на сервере: JS админки (`grid-view.ts`) проставляет подписи только при
 * загрузке страницы, а строки, подменённые htmx, без них потеряли бы подписи в мобильных карточках.
 */
final class PostGrid
{
    /** Абсолютный маршрут контроллёра: строка рендерится и во вьюхе, и в htmx-ответе */
    private const ROUTE = '/Blog/backend/post/';

    /** Классы таблицы: `table-mobile-cards` превращает строки в карточки на мобилке (grid-view.scss) */
    public const TABLE_CLASS = 'table table-hover mb-0 table-mobile-cards';

    /** Ячейка карточки «подпись сверху, содержимое под ней» (стили во views/backend/post/index.php) */
    public const CELL_STACK = 'blog-post-grid__stack';

    /** Ячейка кнопок: на мобилке кнопки переносятся на новую строку, а не вылезают */
    public const CELL_ACTIONS = 'blog-post-grid__actions';

    /**
     * Описание колонок.
     *
     * @param UrlManager      $frontendUrlManager для ссылки «Открыть на сайте»
     * @param PostSearch|null $searchModel        модель фильтров; null — строка для htmx-ответа,
     *                                            фильтры не строятся (не нужны и стоят запросов в БД)
     */
    public static function columns(UrlManager $frontendUrlManager, ?PostSearch $searchModel = null): array
    {
        $canActivate = AccessHelper::checkRoute(self::ROUTE . 'activate');
        $canDraft = AccessHelper::checkRoute(self::ROUTE . 'draft');

        return [
            [
                'attribute' => 'id',
                'label' => 'ID',
                'contentOptions' => ['data-label' => 'ID'],
            ],
            [
                'label' => 'Photo',
                'value' => static function (Post $model) {
                    return $model->photo ? Html::img($model->getThumbUrl('photo', 'admin')) : null;
                },
                'format' => 'raw',
                'contentOptions' => ['data-label' => 'Photo'],
            ],
            [
                'attribute' => 'created_at',
                'label' => 'Created',
                // Режим без модели: скрытые поля без id — иначе они совпали бы по id с полями
                // того же виджета в мобильной форме фильтров (_mobileFilters.php)
                'filter' => $searchModel ? DateTimeRangeWidget::widget([
                    'nameFrom' => Html::getInputName($searchModel, 'date_from'),
                    'nameTo' => Html::getInputName($searchModel, 'date_to'),
                    'valueFrom' => (string)$searchModel->date_from,
                    'valueTo' => (string)$searchModel->date_to,
                    'valueFormat' => PostSearch::DATE_FORMAT,
                ]) : false,
                'format' => 'datetime',
                'contentOptions' => ['data-label' => 'Created'],
            ],
            [
                'label' => 'Title or desc',
                'attribute' => 'title',
                // Текст внешний (парсеры Хабра/Пикабу) — экранируем; doubleEncode=false сохраняет
                // уже записанные сущности вроде &nbsp; вместо показа их текстом.
                // Обёртка <div>: в мобильной карточке ячейка — flex, и без неё ссылка и описание
                // становятся отдельными flex-элементами в ряд и вылезают по ширине.
                'value' => static function (Post $model) {
                    $out = Html::a(Html::encode($model->title, false), [self::ROUTE . 'view', 'id' => $model->id]);
                    return '<div>' . $out . '<br /><small>' . Html::encode($model->description, false) . '</small></div>';
                },
                'format' => 'raw',
                'contentOptions' => ['class' => self::CELL_STACK, 'data-label' => 'Title or desc'],
            ],
            [
                'attribute' => 'taxonomy_id',
                'label' => 'Taxonomy',
                'filter' => $searchModel ? new TreeQueryScope(Taxonomy::class)->dropdownTree() : false,
                'value' => 'taxonomy.name',
                'contentOptions' => ['data-label' => 'Taxonomy'],
            ],
            [
                'attribute' => 'status',
                'label' => 'Status',
                'filter' => $searchModel?->statusList() ?? false,
                'value' => static function (Post $model) use ($canActivate, $canDraft): string {
                    $badge = Html::tag('span', PostHelper::statusName($model->status), [
                        'class' => $model->isActive()
                            ? 'badge rounded-pill text-bg-success'
                            : 'badge rounded-pill text-bg-secondary',
                    ]);
                    $action = $model->isActive() ? 'draft' : 'activate';
                    if (!($model->isActive() ? $canDraft : $canActivate)) {
                        return $badge;
                    }
                    return self::htmxButton($action, $model, $badge, [
                        'class' => 'btn btn-link p-0 d-inline-flex align-items-center gap-1',
                        'title' => 'Сменить статус',
                    ], 'Сменить статус?');
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
            [
                'attribute' => 'views',
                'label' => 'Views',
                'contentOptions' => ['data-label' => 'Views'],
            ],
//                    'comments_count',
            [
                'class' => ActionColumn::class,
                'header' => '',
                // Имена кнопок совпадают с действиями контроллёра: AccessHelper проверяет права по ним
                'template' => AccessHelper::filterActionColumn(['update', 'view', 'activate', 'draft', 'pin-toggle', 'delete']),
                'headerOptions' => ['style' => 'width:190px'],
                'contentOptions' => ['class' => self::CELL_ACTIONS, 'data-label' => ''],
                'buttons' => [
                    'update' => static fn ($url, Post $model): string => Html::a(
                        '<i class="bi bi-pencil"></i>',
                        [self::ROUTE . 'update', 'id' => $model->id],
                        ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Редактировать'],
                    ),
                    // Карточка в админке + пост на сайте
                    'view' => static fn ($url, Post $model): string => Html::a(
                        '<i class="bi bi-eye"></i>',
                        [self::ROUTE . 'view', 'id' => $model->id],
                        ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'Просмотр'],
                    ) . ' ' . Html::a(
                        '<i class="bi bi-box-arrow-up-right"></i>',
                        $frontendUrlManager->createAbsoluteUrl(['/Blog/post/view/', 'id' => $model->id]),
                        ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'Открыть на сайте', 'target' => '_blank'],
                    ),
                    'activate' => static function ($url, Post $model): string {
                        if ($model->isActive()) return '';
                        return self::htmxButton('activate', $model, '<i class="bi bi-check-circle"></i>', [
                            'class' => 'btn btn-sm btn-outline-success',
                            'title' => 'Опубликовать',
                        ], 'Опубликовать пост?');
                    },
                    'draft' => static function ($url, Post $model): string {
                        if ($model->isDraft()) return '';
                        return self::htmxButton('draft', $model, '<i class="bi bi-file-earmark"></i>', [
                            'class' => 'btn btn-sm btn-outline-secondary',
                            'title' => 'В черновик',
                        ], 'Перевести в черновик?');
                    },
                    'pin-toggle' => static fn ($url, Post $model): string => self::htmxButton(
                        'pin-toggle',
                        $model,
                        $model->isPinned() ? '<i class="bi bi-pin-angle-fill"></i>' : '<i class="bi bi-pin-angle"></i>',
                        [
                            'class' => $model->isPinned() ? 'btn btn-sm btn-warning' : 'btn btn-sm btn-outline-warning',
                            'title' => $model->isPinned() ? 'Открепить' : 'Закрепить',
                        ],
                    ),
                    // Удаление — обычный POST с перезагрузкой: меняются пагинация и счётчик записей
                    'delete' => static fn ($url, Post $model): string => Html::a(
                        '<i class="bi bi-trash"></i>',
                        [self::ROUTE . 'delete', 'id' => $model->id],
                        [
                            'class' => 'btn btn-sm btn-outline-danger',
                            'title' => 'Удалить',
                            'data-method' => 'post',
                            'data-confirm' => 'Удалить пост безвозвратно?',
                        ],
                    ),
                ],
            ],
        ];
    }

    /**
     * Строка таблицы для htmx-ответа: тот же GridView по одной записи, без фильтров.
     *
     * @param Post       $post
     * @param UrlManager $frontendUrlManager
     */
    public static function renderRow(Post $post, UrlManager $frontendUrlManager): string
    {
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => [$post], 'pagination' => false]),
            'columns' => self::columns($frontendUrlManager),
        ]);

        // Ключ строки как у ActiveDataProvider в index — первичный ключ (атрибут data-key)
        return $grid->renderTableRow($post, $post->id, 0);
    }

    /**
     * Кнопка htmx-действия над постом: POST, ответ — обновлённая строка таблицы вместо текущей.
     * Ошибку ответа (4xx/5xx) показывает глобальный обработчик админки (`htmx-errors.ts`).
     *
     * @param string      $action  действие контроллёра
     * @param Post        $model
     * @param string      $content HTML содержимого кнопки
     * @param array       $options атрибуты кнопки
     * @param string|null $confirm текст подтверждения; null — без подтверждения
     */
    private static function htmxButton(string $action, Post $model, string $content, array $options, ?string $confirm = null): string
    {
        return Html::button(
            $content . '<span class="spinner-border spinner-border-sm htmx-indicator" role="status" aria-hidden="true"></span>',
            array_merge([
                'hx-post' => Url::to([self::ROUTE . $action, 'id' => $model->id]),
                'hx-target' => 'closest tr',
                'hx-swap' => 'outerHTML',
                // Индикатор — сама кнопка: спиннер внутри неё (стили во views/backend/post/index.php)
                'hx-indicator' => 'this',
                'hx-confirm' => $confirm,
            ], $options),
        );
    }
}
