<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\controllers\backend;

use yii\base\Controller;

class ClearController extends Controller
{
    public function actionGetData(): string
    {
        return '{"status": "success","data":"123.45 КБ"}';
    }

    public function actionClearData(): string
    {
        return '{"status":"success","message":"Данные успешно очищены"}';
    }
}
