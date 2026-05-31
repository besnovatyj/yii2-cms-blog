<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\controllers\backend;

use DomainException;
use Besnovatyj\Blog\forms\backend\search\CommentSearch;
use Besnovatyj\Blog\forms\backend\CommentEditForm;
use Besnovatyj\Blog\services\manage\CommentManageService;
use Yii;
use Besnovatyj\Blog\entities\Post;

use yii\helpers\VarDumper;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

class CommentController extends \yii\web\Controller
{
    use \common\components\controller\ControllerTrait;

    private $service;

    public function __construct($id, $module, CommentManageService $service, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->service = $service;
    }

    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'activate' => ['POST'],
                    'draft' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex(): string
    {
        $searchModel = new CommentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param integer $post_id
     * @param integer $id
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate(int $post_id, int $id): Response|string
    {
        $post = $this->findModel($post_id);
        $comment = $post->getComment($id);

        $form = new \Besnovatyj\Blog\forms\backend\CommentEditForm($comment);
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->service->edit($post->id, $comment->id, $form);
                return $this->redirect(['view', 'post_id' => $post->id, 'id' => $comment->id]);
            } catch (DomainException $e) {
                $this->handleDomainException($e, 'Ошибка');
            }
        }
        return $this->render('update', [
            'post' => $post,
            'model' => $form,
            'comment' => $comment,
        ]);
    }

    /**
     * @param integer $post_id
     * @param integer $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView(int $post_id, int $id): string
    {
        $post = $this->findModel($post_id);
        $comment = $post->getComment($id);

        return $this->render('view', [
            'post' => $post,
            'comment' => $comment,
        ]);
    }

    /**
     * @param int $post_id
     * @param integer $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionActivate(int $post_id, int $id): Response
    {
        $post = $this->findModel($post_id);
        try {
            $this->service->activate($post->id, $id);
        } catch (DomainException $e) {
            Yii::$app->errorHandler->logException($e);
            if (YII_DEBUG) {
                Yii::$app->session->setFlash('error', VarDumper::dumpAsString($e->getMessage()));
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка');
            }
        }
        return $this->redirect(['view', 'post_id' => $post_id, 'id' => $id]);
    }

    /**
     * @param int $post_id
     * @param integer $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionDraft(int $post_id, int $id): Response
    {
        $post = $this->findModel($post_id);
        try {
            $this->service->draft($post->id, $id);
        } catch (DomainException $e) {
            Yii::$app->errorHandler->logException($e);
            if (YII_DEBUG) {
                Yii::$app->session->setFlash('error', VarDumper::dumpAsString($e->getMessage()));
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка');
            }
        }
        return $this->redirect(['view', 'post_id' => $post_id, 'id' => $id]);
    }

    /**
     * @param int $post_id
     * @param integer $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionDelete(int $post_id, int $id): Response
    {
        $post = $this->findModel($post_id);
        try {
            $this->service->remove($post->id, $id);
        } catch (DomainException $e) {
            Yii::$app->errorHandler->logException($e);
            if (YII_DEBUG) {
                Yii::$app->session->setFlash('error', VarDumper::dumpAsString($e->getMessage()));
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка');
            }
        }
        return $this->redirect(['view', 'post_id' => $post_id, 'id' => $id]);
    }

    /**
     * @param integer $id
     * @return \Besnovatyj\Blog\entities\Post the loaded model
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
