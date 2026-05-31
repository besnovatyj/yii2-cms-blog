<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Blog\entities\Post;
use Besnovatyj\Blog\helpers\PostHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $post Post */
/* @var $frontendUrl string */
/* @var $absoluteFrontendUrl string */

$this->title = $post->title;
$this->params['breadcrumbs'][] = ['label' => 'Posts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="text-danger border border-warning m-1 p-2"> $this->beginBlock('pageRight.Actions');</div>

<?php $this->beginBlock('pageRight.Actions'); ?>
<?php echo $this->render('_pageActions', [
    '$this' => $this,
    'post' => $post,
    'frontendUrl' => $frontendUrl,
    'absoluteFrontendUrl' => $absoluteFrontendUrl,
]); ?>
<?php $this->endBlock(); ?>

<div>
    <h1>TODO HighlightAssets и Mathjax277Asset</h1>
</div>

<div class="row">
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header">
                Common
            </div>
            <div class="card-body">
                <?= DetailView::widget([
                    'model' => $post,
                    'attributes' => [
                        'id',
                        [
                            'attribute' => 'status',
                            'value' => PostHelper::statusLabel($post),
                            'format' => 'raw',
                        ],
                        'title',
                        [
                            'attribute' => 'taxonomy_id',
                            'value' => ArrayHelper::getValue($post, 'taxonomy.name'),
                        ],
                        [
                            'label' => 'Прочие taxonomy',
                            'value' => implode(', ', ArrayHelper::getColumn($post->taxonomies, 'name')),
                        ],
                        [
                            'attribute' => 'comments_allowed',
                            'value' => PostHelper::commentStatusLabel($post),
                            'format' => 'raw',
                        ],
                        [
                            'label' => 'Теги',
                            'value' => implode(', ', ArrayHelper::getColumn($post->tags, 'name')),
                        ],
                    ],
                ]) ?>
            </div>
            <div class="card-footer">

            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header">Main image</div>
            <div class="card-body">
                <?php if ($post->photo): ?>
                    <?= Html::a(Html::img($post->getThumbUrl('photo', 'admin_view'), ['class' => 'img-fluid']), $post->getUploadUrl('photo'), [
                        'target' => '_blank'
                    ]) ?>
                <?php endif; ?>
            </div>
            <div class="card-footer"></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Description</div>
    <div class="card-body">
        <?= $post->description ?>
    </div>
    <div class="card-footer"></div>
</div>

<div class="card">
    <div class="card-header">Text body</div>
    <div class="card-body">
        <?= $post->content ?>
    </div>
    <div class="card-footer"></div>
</div>

<div class="card">
    <div class="card-header">SEO</div>
    <div class="card-body">
        <?= DetailView::widget([
            'model' => $post,
            'attributes' => [
                [
                    'attribute' => 'meta.title',
                    'value' => $post->meta->title,
                ],
                [
                    'attribute' => 'meta.description',
                    'value' => $post->meta->description,
                ],
                [
                    'attribute' => 'meta.keywords',
                    'value' => $post->meta->keywords,
                ],
            ],
        ]) ?>
    </div>
    <div class="card-footer"></div>
</div>
