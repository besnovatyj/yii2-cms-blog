<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\widgets;

use Besnovatyj\Blog\readModels\PostReadRepository;
use yii\base\Widget;

class MostPopular extends Widget
{
    public $limit;
    private $posts;

    public function __construct(PostReadRepository $posts, $config = [])
    {
        parent::__construct($config);
        $this->posts = $posts;
    }

    public function run(): string
    {

        return $this->render('most-popular', [
            'posts' => $this->posts->getPopularByViews($this->limit),
        ]);

    }

}
