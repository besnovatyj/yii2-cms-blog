<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\widgets;

use Besnovatyj\Blog\entities\Comment;

class CommentView
{
    public $comment;
    /**
     * @var self[]
     */
    public $children;

    public function __construct(Comment $comment, array $children)
    {
        $this->comment = $comment;
        $this->children = $children;
    }
}
