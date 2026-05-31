<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\controllers\backend;

use Besnovatyj\Blog\entities\Post;
use Besnovatyj\Blog\forms\backend\PostForm;
use Besnovatyj\Blog\forms\backend\search\PostSearch;
use Besnovatyj\Blog\services\manage\PostManageService;
use common\components\controller\ControllerTrait;
use common\components\urlmanager\UrlManagerHelperTrait;
use Exception;
use Throwable;
use Yii;
use yii\base\ExitException;
use yii\base\InvalidConfigException;
use yii\base\UnknownPropertyException;
use yii\filters\VerbFilter;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class PostController extends Controller
{
    use ControllerTrait;
    use UrlManagerHelperTrait;

    private PostManageService $service;

    public function __construct($id, $module, PostManageService $service, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->service = $service;
    }

    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'activate' => ['POST'],
                    'draft' => ['POST'],
                    'delete' => ['POST'],
                    'ajax-save' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionIndex(): string
    {
        $searchModel = new PostSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * @param integer $id
     * @return string
     * @throws NotFoundHttpException|InvalidConfigException
     */
    public function actionView(int $id): string
    {
        $absoluteFrontendUrl = $this->getAbsoluteFrontendRoute('/Blog/post/view/', ['id' => $id]);
        $frontendUrl = $this->getFrontendRoute('/Blog/post/view/', ['id' => $id]);

        return $this->render('view', [
            'post' => $this->findModel($id),
            'absoluteFrontendUrl' => $absoluteFrontendUrl,
            'frontendUrl' => $frontendUrl,
        ]);
    }

    /**
     * @return string|Response
     */
    public function actionCreate(): Response|string
    {
        $form = new PostForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $post = $this->service->create($form);
                Yii::$app->session->setFlash('success', 'Post successfully created');
                return $this->redirect(['view', 'id' => $post->id]);
            } catch (Throwable $e) {
                $this->handleDomainException($e, 'Ошибка');
            }
        }
        return $this->render('create', [
            'model' => $form,
        ]);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionUpdate(int $id): Response|string
    {
        $post = $this->findModel($id);
        $form = new PostForm($post);
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->service->edit($post, $form);
                Yii::$app->session->setFlash('success', 'Post successfully updated');
                return $this->redirect(['view', 'id' => $post->id]);
            } catch (Throwable $e) {
                $this->handleDomainException($e, 'Ошибка');
            }
        }
        return $this->render('update', [
            'model' => $form,
            'post' => $post,
        ]);
    }

    /**
     * @throws ExitException
     */
    public function actionAjaxSave(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $response = ['status' => 'error'];

        if (Yii::$app->request->isAjax) {
            try {
                // Сохраняемый контент
                $content = Yii::$app->request->post('editor_content') ?: '';
                // Идентификатор редактируемой сущности
                $id = Yii::$app->request->post('model_id') ?: null;
                // Название редактируемого поля сущности
                $fieldName = Yii::$app->request->post('field_name') ?: null;

                if (!empty($id) && !empty($fieldName)) {

                    $post = $this->findModel((int)$id);

                    if (!isset($post->$fieldName)) {
                        throw new UnknownPropertyException('Trying to change non-existent property: ' . $fieldName);
                    }

                    $form = new \Besnovatyj\Blog\forms\backend\PostForm($post);

                    if (($form->$fieldName = urldecode($content)) && $form->validate()) {
                        $this->service->edit($post, $form);
                    }

                    $response['status'] = 'success';
                    $response['message'] = 'Saved successfully!';
                    return $response;

                }

                $response['status'] = 'error';
                $response['message'] = 'SAVE NORMALLY BEFORE!';
                return $response;

            } catch (Throwable $e) {
                $this->ajaxError($e);
            }
        }

        return $response;
    }

    /**
     * @param int $id
     * @return Response
     */
    public function actionDelete(int $id): Response
    {
        try {
            $this->service->remove($id);
            Yii::$app->session->setFlash('success', 'Post successfully deleted');
        } catch (Throwable $e) {
            Yii::$app->errorHandler->logException($e);
            if (YII_DEBUG) {
                Yii::$app->session->setFlash('error', VarDumper::dumpAsString($e->getMessage()));
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка');
            }
        }
        return $this->redirect(['index']);
    }

    /**
     * @param integer $id
     * @return Response
     */
    public function actionActivate(int $id): Response
    {
        try {
            $this->service->activate($id);
            Yii::$app->session->setFlash('success', 'Post successfully activated');
        } catch (Throwable $e) {
            Yii::$app->errorHandler->logException($e);
            if (YII_DEBUG) {
                Yii::$app->session->setFlash('error', VarDumper::dumpAsString($e->getMessage()));
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка');
            }
        }
        return $this->goReferer();
    }

    /**
     * @param integer $id
     * @return Response
     */
    public function actionDraft(int $id): Response
    {
        try {
            $this->service->draft($id);
            Yii::$app->session->setFlash('success', 'Post successfully drafted');
        } catch (Exception $e) {
            Yii::$app->errorHandler->logException($e);
            if (YII_DEBUG) {
                Yii::$app->session->setFlash('error', VarDumper::dumpAsString($e->getMessage()));
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка');
            }
        }
        return $this->goReferer();
    }

    public function actionPinToggle(int $id): Response
    {
        try {
            $this->service->pinToggle($id);
            Yii::$app->session->setFlash('success', 'Post pin changed.');
        } catch (Exception $e) {
            Yii::$app->errorHandler->logException($e);
            if (YII_DEBUG) {
                Yii::$app->session->setFlash('error', VarDumper::dumpAsString($e->getMessage()));
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка');
            }
        }
        return $this->goReferer();
    }

    /**
     * @param int $id
     * @return Post the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): Post
    {
        if (($model = Post::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
