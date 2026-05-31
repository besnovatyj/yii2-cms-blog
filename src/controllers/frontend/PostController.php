<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\controllers\frontend;

use Besnovatyj\Blog\forms\frontend\CommentForm;
use Besnovatyj\Blog\readModels\TaxonomyReadRepository;
use Besnovatyj\Blog\readModels\PostReadRepository;
use Besnovatyj\Blog\readModels\TagReadRepository;
use Besnovatyj\Blog\services\CommentService;
use Yii;

use yii\helpers\VarDumper;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class PostController extends \yii\web\Controller
{
    use \common\components\controller\ControllerTrait;

    private CommentService $service;
    private PostReadRepository $posts;
    private TaxonomyReadRepository $taxonomies;
    private TagReadRepository $tags;

    public function __construct(
        $id,
        $module,
        CommentService $service,
        PostReadRepository $posts,
        TaxonomyReadRepository $taxonomies,
        TagReadRepository $tags,
        $config = []
    )
    {
        parent::__construct($id, $module, $config);
        $this->service = $service;
        $this->posts = $posts;
        $this->taxonomies = $taxonomies;
        $this->tags = $tags;
    }

    /**
     * @return string
     */
    public function actionIndex(): string
    {
        $dataProvider = $this->posts->getAll();

        return $this->render('/frontend/post/index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param string $slug
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionTaxonomy(string $slug): string
    {
        if (!$taxonomy = $this->taxonomies->findBySlug($slug)) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $dataProvider = $this->posts->getAllByTaxonomy($taxonomy);

        return $this->render('/frontend/post/taxonomy', [
            'taxonomy' => $taxonomy,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param string $slug
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionTag(string $slug): string
    {
        if (!$tag = $this->tags->findBySlug($slug)) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $dataProvider = $this->posts->getAllByTag($tag);

        return $this->render('/frontend/post/tag', [
            'tag' => $tag,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView(int $id): string
    {
        if (!$post = $this->posts->find($id)) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $post->refreshViewCounter();
        return $this->render('/frontend/post/view', [
            'post' => $post,
        ]);
    }

    /**
     * @param int $id
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionComment(int $id): Response|string
    {
        if (!$post = $this->posts->find($id)) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        if (!$post->isCommentsAllowed()) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $form = new CommentForm();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $comment = $this->service->create($post->id, Yii::$app->user->id, $form);
                $this->userLog('create comment #' . $comment->id, __METHOD__);
                return $this->redirect(['post', 'id' => $post->id, '#' => 'comment_' . $comment->id]);
            } catch (\DomainException $e) {
                $this->handleDomainException($e, 'Ошибка');
            }
        }

        return $this->render('/frontend/post/post', [
            'post' => $post,
            'model' => $form,
        ]);
    }
}
