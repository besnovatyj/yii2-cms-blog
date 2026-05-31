<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Blog\entities\Post;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $post \Besnovatyj\Blog\entities\Post */
/* @var $frontendUrl string */
/* @var $absoluteFrontendUrl string */

$urlManager = Yii::$app->get('frontendUrlManager');

?>

<?php $this->beginBlock('pageActions'); ?>
<p>
    <?php if ($post->isActive()): ?>
        <?= Html::a('Active', ['draft', 'id' => $post->id], ['class' => 'btn btn-success', 'data-method' => 'post']) ?>
    <?php else: ?>
        <?= Html::a('Draft', ['activate', 'id' => $post->id], ['class' => 'btn btn-secondary', 'data-method' => 'post']) ?>
    <?php endif; ?>
    <?php if ($post->isPinned()): ?>
        <?= Html::a('Pinned', ['pin-toggle', 'id' => $post->id], ['class' => 'btn  btn-warning', 'data-method' => 'post']) ?>
    <?php else: ?>
        <?= Html::a('Unpinned', ['pin-toggle', 'id' => $post->id], ['class' => 'btn  btn-secondary', 'data-method' => 'post']) ?>
    <?php endif; ?>

    <?= Html::a('Update', ['update', 'id' => $post->id], ['class' => 'btn  btn-primary']) ?>
    <?= Html::a('Delete', ['delete', 'id' => $post->id], [
        'class' => 'btn  btn-danger',
        'data' => [
            'confirm' => 'Are you sure?',
            'method' => 'post',
        ],
    ]) ?>
    <a class="btn  btn-secondary" target="_blank"
       href="<?= $absoluteFrontendUrl; ?>">
        <i class="bi bi-eye"></i>
    </a>

    <?= \Besnovatyj\Menu\widgets\add\AddItemWidget::widget([
        'endpoint' => \yii\helpers\Url::to('/Menu/tree/create-node', true),
        'link' => $frontendUrl,
        'name' => $post->title,
    ]) ?>

</p>
<?php $this->endBlock(); ?>

<?php if (isset($this->blocks['pageActions'])): ?>
    <div class="d-none d-md-block">
        <?= $this->blocks['pageActions'] ?>
    </div>
<?php endif; ?>
