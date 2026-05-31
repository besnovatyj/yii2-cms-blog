<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\controllers\backend;

use Exception;
use Besnovatyj\Blog\services\manage\PostManageService;
use Besnovatyj\Blog\services\parse\ParseService;
use Besnovatyj\Helpers\url\UrlHelper;

use Yii;
use yii\helpers\VarDumper;
use yii\web\Response;

class ParseController extends \yii\web\Controller
{
    private ParseService $parseService;
    private PostManageService $postManageService;

    public function __construct($id, $module, ParseService $parseService, PostManageService $postManageService, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->parseService = $parseService;
        $this->postManageService = $postManageService;
    }

    public function actionIndex(): string
    {
        return $this->render('index');
    }

    public function actionGetStartData(): array
    {
        $response = ['status' => 'error'];
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $url = urldecode(Yii::$app->request->post('source_url'));
            if (UrlHelper::isUrl($url)) {
                try {
                    $response['data'] = $this->parseService->getStartData($url);
                    $response['status'] = 'success';
                    $response['message'] = 'Данные получены!';
                } catch (Exception $e) {
                    Yii::$app->errorHandler->logException($e);
                    if (YII_DEBUG) {
                        Yii::$app->session->setFlash('error', VarDumper::dumpAsString($e->getMessage()));
                    } else {
                        Yii::$app->session->setFlash('error', 'Ошибка');
                    }
                    $response['data'] = $e->getCode();
                    $response['status'] = 'error';
                    $response['message'] = YII_DEBUG ? VarDumper::dumpAsString($e->getMessage()) : 'Ошибка';
                }
            } else {
                $response['message'] = 'Error getting URL';
            }
        }
        return $response;
    }

    /**
     * @deprecated Images are now downloaded during preview, this endpoint is no longer needed
     */
    public function actionGetImageByUrl(): array
    {
        $response = ['status' => 'error'];
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $article_source_url = urldecode(Yii::$app->request->post('article_source_url'));
            $image_url = urldecode(Yii::$app->request->post('image_url'));
            if (!empty($article_source_url) && !empty($image_url) && UrlHelper::isUrl($article_source_url) && UrlHelper::isUrl($image_url)) {
                try {
                    $response['data'] = $this->parseService->downloadImage($article_source_url, $image_url);
                    $response['status'] = 'success';
                    $response['message'] = 'Image successfully downloaded!';
                } catch (Exception $e) {
                    Yii::$app->errorHandler->logException($e);
                    if (YII_DEBUG) {
                        Yii::$app->session->setFlash('error', VarDumper::dumpAsString($e->getMessage()));
                    } else {
                        Yii::$app->session->setFlash('error', 'Ошибка');
                    }
                    $response['data'] = $e->getCode();
                    $response['status'] = 'error';
                    $response['message'] = YII_DEBUG ? VarDumper::dumpAsString($e->getMessage()) : 'Ошибка';
                }
            } else {
                $response['message'] = 'Image downloading error.';
            }
        }
        return $response;
    }

    public function actionSaveToPost(): array
    {
        $response = ['status' => 'error'];
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $url = urldecode(Yii::$app->request->post('source_url'));
            if (!empty($url) && UrlHelper::isUrl($url)) {
                try {
                    $response['data'] = $this->parseService->parseToPost($url);
                    $response['status'] = 'success';
                    $response['message'] = 'Данные получены!';
                } catch (Exception $e) {
                    Yii::$app->errorHandler->logException($e);
                    if (YII_DEBUG) {
                        Yii::$app->session->setFlash('error', VarDumper::dumpAsString($e->getMessage()));
                    } else {
                        Yii::$app->session->setFlash('error', 'Ошибка');
                    }
                    $response['data'] = $e->getCode();
                    $response['status'] = 'error';
                    $response['message'] = YII_DEBUG ? VarDumper::dumpAsString($e->getMessage()) : 'Ошибка';
                }
            } else {
                $response['message'] = 'Error getting URL';
            }
        }
        return $response;
    }

}
