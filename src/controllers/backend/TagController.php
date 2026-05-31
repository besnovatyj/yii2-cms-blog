<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\controllers\backend;

use DomainException;
use Besnovatyj\Blog\entities\Tag;
use Besnovatyj\Blog\forms\backend\search\TagSearch;
use Besnovatyj\Blog\forms\backend\TagForm;
use Besnovatyj\Blog\readModels\PostReadRepository;
use Besnovatyj\Blog\services\manage\TagManageService;
use Throwable;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use yii\filters\VerbFilter;

use yii\helpers\VarDumper;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class TagController extends \yii\web\Controller
{
    use \common\components\controller\ControllerTrait;

    private $service;
    private $posts;

    public function __construct(
        $id,
        $module,
        TagManageService $service,
        PostReadRepository $posts,
        $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->service = $service;
        $this->posts = $posts;
    }

    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex(): string
    {
        $searchModel = new TagSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param integer $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView(int $id): string
    {
        return $this->render('view', [
            'tag' => $this->findModel($id),
        ]);
    }

    /**
     * @param integer $id
     * @return Response|string
     * @throws NotFoundHttpException|Exception
     */
    public function actionUpdate(int $id): Response|string
    {
        $tag = $this->findModel($id);

        $form = new \Besnovatyj\Blog\forms\backend\TagForm($tag);
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->service->edit($tag->id, $form);
                return $this->redirect(['view', 'id' => $tag->id]);
            } catch (DomainException $e) {
                $this->handleDomainException($e, 'Ошибка');
            }
        }
        return $this->render('update', [
            'model' => $form,
            'tag' => $tag,
        ]);
    }

    /**
     * @param integer $id
     * @return Response
     * @throws Throwable
     */
    public function actionDelete(int $id): Response
    {
        try {
            $this->service->remove($id);
        } catch (\Exception $e) {
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
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionTaggedPosts(int $id): string
    {
        $tag = $this->findModel($id);
        $dataProvider = $this->posts->getAllByTag($tag);

        return $this->renderPartial('tagged', [
            'tag' => $tag,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionEmptyTags(): Response|string
    {
        try {
            $dataProvider = $this->service->findEmpty();
            return $this->render('empty-tags',
                ['dataProvider' => $dataProvider]
            );
        } catch (DomainException $e) {
            Yii::$app->errorHandler->logException($e);
            if (YII_DEBUG) {
                Yii::$app->session->setFlash('error', VarDumper::dumpAsString($e->getMessage()));
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка');
            }
        }
        return $this->redirect('index');
    }

    public function actionDeleteEmptyTags(): Response|string
    {
        try {
            $count = $this->service->deleteEmpty();
            Yii::$app->session->setFlash('success', '"' . $count . '" tags deleted.');
            return $this->redirect('empty-tags');
        } catch (DomainException $e) {
            Yii::$app->errorHandler->logException($e);
            if (YII_DEBUG) {
                Yii::$app->session->setFlash('error', VarDumper::dumpAsString($e->getMessage()));
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка');
            }
        }
        return $this->redirect('empty-tags');
    }

    /**
     * Для виджета Select2Widget
     * @return array
     */
    public function actionSearchEndpoint(): array
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $query = \Yii::$app->request->get('q', '');
        $tags = Tag::find()
            ->where(['like', 'name', $query])
            ->select(['id', 'name as text'])
            ->asArray()
            ->all();
        return ['results' => $tags];
    }

    /**
     * @param integer $id
     * @return Tag the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): Tag
    {
        if (($model = Tag::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
