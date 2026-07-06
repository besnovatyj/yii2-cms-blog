<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Blog\controllers\backend;

use Besnovatyj\Blog\services\BlogCacheClearService;
use Exception;
use Yii;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

/**
 * Контроллер интеграции блога с модулем очистки (ClearManager).
 *
 * Работает с кешем изображений блога (@static/cache/Blog): отдаёт его размер и очищает содержимое.
 * Формат ответа и обработка ошибок повторяют {@see \Besnovatyj\ClearManager\controllers\backend\DataController}:
 *  - все экшены — только POST и только AJAX, ответ в JSON;
 *  - ожидаемые сбои бросаются {@see ServerErrorHttpException} (нативный конверт Yii ErrorHandler),
 *    непредвиденные исключения сервиса не ловятся и всплывают к ErrorHandler.
 */
class ClearController extends Controller
{
    private BlogCacheClearService $service;

    public function __construct($id, $module, BlogCacheClearService $service, array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->service = $service;
    }

    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    '*' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Все экшены отдают JSON и принимают только AJAX-запросы.
     *
     * @throws BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        // Формат ставим до parent::beforeAction — чтобы ошибки фильтров (verb) тоже ушли как JSON.
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!parent::beforeAction($action)) {
            return false;
        }
        if (!Yii::$app->getRequest()->getIsAjax()) {
            throw new BadRequestHttpException('Ожидается AJAX-запрос.');
        }
        return true;
    }

    /**
     * Возвращает размер кеша изображений блога.
     *
     * @throws Exception
     */
    public function actionGetData(): array
    {
        return ['status' => 'success', 'data' => $this->service->getData()];
    }

    /**
     * Очищает кеш изображений блога.
     *
     * @throws ServerErrorHttpException
     * @throws Exception
     */
    public function actionClearData(): array
    {
        if (!$this->service->clearData()) {
            throw new ServerErrorHttpException('Не удалось очистить кеш изображений блога.');
        }
        return ['status' => 'success', 'message' => 'Кеш изображений блога очищен'];
    }
}
