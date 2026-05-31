<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\controllers\backend;

use Besnovatyj\Blog\entities\taxonomy\Taxonomy;
use Besnovatyj\Blog\forms\backend\TaxonomyForm;
use Besnovatyj\Blog\readModels\PostReadRepository;
use Besnovatyj\TreeManager\Manager\controllers\TreeController;
use Besnovatyj\TreeManager\Manager\TreeDataSource;
use Yii;

class TaxonomyController extends TreeController
{
    private PostReadRepository $postsReadRepo;

    public function __construct($id, $module, $config = [])
    {
        $this->treeManager = Yii::$container->get('blog.tree.manager');
        $this->dataSource = new TreeDataSource(
            Taxonomy::class,
            function (Taxonomy $model) {
                return [
                    'id' => $model->id,
                    'title' => $model->name,
                    'slug' => $model->slug,
                ];
            },
            'sort_order'
        );
        $this->createFormClass = TaxonomyForm::class;
        $this->updateFormClass = TaxonomyForm::class;
        $this->formView = '_form';
        $this->indexTitle = 'Управление категориями';

        $this->postsReadRepo = new PostReadRepository();

        parent::__construct($id, $module, $config);
    }

    // TODO
    public function actionTaxonomicPosts(int $id): string
    {
        $taxonomy = $this->treeManager->getNode($id);
        $dataProvider = $this->postsReadRepo->getAllByOtherTaxonomy($taxonomy);

        return $this->renderPartial('taxonomic', [
            'taxonomy' => $taxonomy,
            'dataProvider' => $dataProvider,
        ]);
    }
}
