<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\widgets;

use Besnovatyj\Blog\entities\Comment;
use Besnovatyj\Kernel\security\AccessHelper;
use yii\bootstrap5\Widget;

class CommentNotificationsWidget extends Widget
{
    private $comments;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->comments = Comment::find()->draft()->all();
    }

    public function run()
    {
        if (AccessHelper::checkRoute('comment/view')) {
            return $this->render('comment-notifications', ['comments' => $this->comments]);
        }
        return '';
    }
}
