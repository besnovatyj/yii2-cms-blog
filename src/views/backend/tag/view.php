<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $tag Besnovatyj\Blog\entities\Tag */

$this->title = $tag->name;
$this->params['breadcrumbs'][] = ['label' => 'Теги', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<p>
    <?= Html::a('Редактировать', ['update', 'id' => $tag->id], ['class' => 'btn  btn-primary']) ?>
    <?= Html::a('Удалить', ['delete', 'id' => $tag->id], [
        'class' => 'btn  btn-danger',
        'data' => [
            'confirm' => 'Вы подтверждаете удаление?',
            'method' => 'post',
        ],
    ]) ?>
</p>

<div class="card">
    <div class="card-header"><?= $this->title ?></div>
    <div class="card-body">
        <?= DetailView::widget([
            'model' => $tag,
            'attributes' => [
                'id',
                'name',
                'slug',
            ],
        ]) ?>
    </div>
    <div class="card-footer clearfix">
    </div>
</div>
<!-- /.card -->

<div class="card">
    <div class="card-header"><a class="btn btn-success  btn-refresh"> <i class="bi bi-arrow-repeat"></i></a>&nbsp;Tagged
            posts</div>
</div>
<!-- /.card -->
<?php yii\widgets\Pjax::begin(); ?>
<div class="card-content-posts">

</div>
<?php yii\widgets\Pjax::end(); ?>
<?php
$this->registerJs("
  var boxPosts = jQuery('.card');
    var postsList = jQuery('.card-content-posts');

    boxPosts.on('click', 'a.btn-refresh', function () {
    var route = '" . Url::to(['/Blog/backend/tag/tagged-posts', 'id' => $tag->id]) . "';
        $.post(route, function (data) {
            postsList.html('');
            postsList.append(data);
            }
        );
    });
");
?>














