<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\controllers\frontend;

use Besnovatyj\Blog\forms\frontend\search\SearchForm;
use Besnovatyj\Blog\readModels\PostReadRepository;
use Yii;
use yii\web\Controller;

/**
 * Фронтовый поиск по блогу.
 *
 * Базовая безопасная реализация по образцу {@see \Besnovatyj\Person\controllers\frontend\PersonController::actionSearch}:
 * запрос читается в {@see SearchForm} из query-параметров, валидируется, затем read-репозиторий строит
 * параметризованный `like`-поиск по активным постам. Роут — `Blog/search/index` (ЧПУ `blog/search`).
 */
class SearchController extends Controller
{
    private PostReadRepository $posts;

    public function __construct(
        $id,
        $module,
        PostReadRepository $posts,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
        $this->posts = $posts;
    }

    public function actionIndex(): string
    {
        $form = new SearchForm();
        $form->load(Yii::$app->request->queryParams);
        $form->validate();

        $dataProvider = $this->posts->search($form);

        return $this->render('/frontend/search/index', [
            'dataProvider' => $dataProvider,
            'searchForm' => $form,
        ]);
    }
}
