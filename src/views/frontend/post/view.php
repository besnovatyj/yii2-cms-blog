<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Blog\entities\Post;

/** @var $post Post */

?>
<div class="container">
    <div class="row"><?= $post->title ?><span class="bg-warning">ИЗ МОДУЛЯ</span></div>
    <div class="row">
        <div class="col-12 col-md-12">
            <?= \Besnovatyj\Shortcode\widgets\ShortcodeContent::widget([
                'content' => $post->content,
            ]);
            ?>
        </div>
    </div>
</div>
