<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\widgets;

use Exception;
use Besnovatyj\Blog\readModels\PostReadRepository;
use yii\base\Widget;
use Besnovatyj\Blog\Module;

class BackendDashboardPostWidget extends Widget
{
    public $limit;
    public $blankImgUrl = '#';
    private $repository;

    public function __construct(PostReadRepository $repository, $config = [])
    {
        parent::__construct($config);
        $this->repository = $repository;
    }

    /**
     * @throws Exception
     */
    public function run(): string
    {
        // $this->context->module ???????
        $module = Module::class;
        if (!$module::isInstalled()) {
            return '';
        }

        return $this->render('backend-dashboard-post', [
            'posts_last_updated' => $this->repository->getLastUpdated($this->limit),
            'posts_pinned' => $this->repository->getPinned(),
            'posts_drafted' => $this->repository->getDrafted(),
            'blankImgUrl' => $this->blankImgUrl,
        ]);
    }
}
