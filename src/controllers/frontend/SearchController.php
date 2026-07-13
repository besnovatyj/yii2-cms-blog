<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\controllers\frontend;

use Besnovatyj\Blog\readModels\PostReadRepository;
use Yii;
use yii\web\Controller;

/**
 * Фронтовый поиск по блогу.
 *
 * Простейшая базовая реализация: принимает строку запроса `q`, ищет по активным постам
 * (title/description/content) и переиспользует вью списка постов `/frontend/post/index`.
 * Роут — `Blog/search/index` (ЧПУ `blog/search`, правило в config/common.php).
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
        $query = (string)Yii::$app->request->get('q', '');
        $dataProvider = $this->posts->search($query);

        return $this->render('/frontend/post/index', [
            'dataProvider' => $dataProvider,
            'query' => $query,
        ]);
    }
}
