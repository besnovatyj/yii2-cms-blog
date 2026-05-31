<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Blog\helpers\PostHelper;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;

/**
 * @var Besnovatyj\Blog\entities\Post[] $posts_last_updated
 * @var Besnovatyj\Blog\entities\Post[] $posts_pinned
 * @var Besnovatyj\Blog\entities\Post[] $posts_drafted
 * @var Besnovatyj\Blog\entities\Post[] $posts_under_revision
 * @var string $blankImgUrl
 */

?>
<div class="card">
    <div class="card-header d-md-flex justify-content-md-between">
        <div><i class="bi bi-list-ol mr-1"></i>Posts</div>
        <div class="card-tools">
            <ul class="nav nav-pills ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#recently-edited-posts" data-toggle="tab">
                        Recently edited</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#drafted-posts" data-toggle="tab">Drafted</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="#pinned-posts" data-toggle="tab">Pinned</a>
                </li>
            </ul>
        </div>
    </div><!-- /.card-header -->
    <div class="card-body direct-chat-messages">
        <div class="tab-content p-0">
            <div class="chart tab-pane" id="recently-edited-posts">
                <ul class="products-list product-list-in-card pl-2 pr-2">
                    <?php foreach ($posts_last_updated as $post): ?>
                        <?php $view_url = Url::to(['/Blog/backend/post/view', 'id' => $post->id]); ?>
                        <?php $update_url = Url::to(['/Blog/backend/post/update', 'id' => $post->id]); ?>
                        <li class="item">
                            <div class="product-img">
                                <img src="<?= Html::encode($post->getThumbUrl('photo', 'last_updated', $blankImgUrl)) ?>"
                                     alt="Post Image"
                                     class="img-size-50">
                            </div>
                            <div class="product-info">
                                <a href="<?= Html::encode($view_url) ?>" class="product-title">
                                    <?= Html::encode($post->title) ?>
                                </a>
                                <?= PostHelper::statusLabel($post) ?><br/>
                                <a href="<?= $update_url ?>" class="badge badge-warning float-right">
                                    <i class="bi bi-pen"></i>
                                </a>
                                <span class="product-description">
                        <?= Html::encode(StringHelper::truncateWords(strip_tags($post->description), 20)) ?>
                      </span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="chart tab-pane" id="drafted-posts">
                <ul class="products-list product-list-in-card pl-2 pr-2">
                    <?php foreach ($posts_drafted as $post): ?>
                        <?php $view_url = Url::to(['/Blog/backend/post/view', 'id' => $post->id]); ?>
                        <?php $update_url = Url::to(['/Blog/backend/post/update', 'id' => $post->id]); ?>
                        <li class="item">
                            <div class="product-img">
                                <img src="<?= Html::encode($post->getThumbUrl('photo', 'last_updated', $blankImgUrl)) ?>"
                                     alt="Post Image"
                                     class="img-size-50">
                            </div>
                            <div class="product-info">
                                <a href="<?= Html::encode($view_url) ?>" class="product-title">
                                    <?= Html::encode($post->title) ?>
                                </a>
                                <?= PostHelper::statusLabel($post) ?><br/>
                                <a href="<?= $update_url ?>" class="badge badge-warning float-right">
                                    <i class="bi bi-pen"></i>
                                </a>
                                <span class="product-description">
                        <?= Html::encode(StringHelper::truncateWords(strip_tags($post->description), 20)) ?>
                      </span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="chart tab-pane active" id="pinned-posts">
                <ul class="products-list product-list-in-card pl-2 pr-2">
                    <?php foreach ($posts_pinned as $post): ?>
                        <?php $view_url = Url::to(['/Blog/backend/post/view', 'id' => $post->id]); ?>
                        <?php $update_url = Url::to(['/Blog/backend/post/update', 'id' => $post->id]); ?>
                        <li class="item">
                            <div class="product-img">
                                <img src="<?= Html::encode($post->getThumbUrl('photo', 'last_updated', $blankImgUrl)) ?>"
                                     alt="Post Image"
                                     class="img-size-50">
                            </div>
                            <div class="product-info">
                                <a href="<?= Html::encode($view_url) ?>" class="product-title">
                                    <?= Html::encode($post->title) ?>
                                </a>
                                <?= PostHelper::statusLabel($post) ?><br/>
                                <a href="<?= $update_url ?>" class="badge badge-warning float-right">
                                    <i class="bi bi-pen"></i>
                                </a>
                                <span class="product-description">
                        <?= Html::encode(StringHelper::truncateWords(strip_tags($post->description), 20)) ?>
                      </span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <!-- /.card-body -->
    <div class="card-footer">
        <a href="<?= Url::to('/Blog/backend/post/index') ?>" class="btn btn-info  float-right">
            <i class="fa fa-list-ol"></i> All
        </a>
        <a href="<?= Url::to('/Blog/backend/post/create') ?>" class="btn btn-info  float-right mr-3">
            <i class="fa fa-plus"></i> Add
        </a>
        <a href="<?= Url::to('/Blog/backend/parse/index') ?>" class="btn btn-info  float-right mr-3">
            <i class="fa fa-plus"></i> Parse
        </a>
    </div>
    <!-- /.card-footer -->
</div>
