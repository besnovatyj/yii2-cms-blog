<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Blog\entities\Post;
use Besnovatyj\Blog\entities\taxonomy\Taxonomy;
use Besnovatyj\Blog\forms\backend\PostForm;
use Besnovatyj\Blog\helpers\PostHelper;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $model PostForm */
/* @var $post Post */
/* @var $form ActiveForm */
?>
<?php $form = ActiveForm::begin([
    'options' => ['enctype' => 'multipart/form-data']
]); ?>

<div class="row">
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header d-md-flex justify-content-md-between">
                <div class="pt-1">Теги, таксономия</div>
                <a class="btn btn-sm collapse-button" data-bs-toggle="collapse" href="#collapse-2" role="button"
                   aria-expanded="true" aria-controls="collapseTagsTaxonomies">
                    <i class="bi bi-plus-lg"></i>
                    <i class="bi bi-dash-lg"></i>
                </a>
            </div>
            <div class="collapse show" id="collapse-2">
                <div class="card-body">
                    <?= $form->field($model->tags, 'newTagsNames')->widget(\Besnovatyj\Select2\Select2Widget::class, [
                        'endpoint' => Url::to(['/Blog/backend/tag/search-endpoint'], true),
//                        'options' => ['class' => 'form-control'],
                        'options' => ['class' => ''],
                    ]) ?>
                    <?= $form->field($model->taxonomies, 'main')->dropDownList(new TreeQueryScope(Taxonomy::class)->dropdownTree(), ['prompt' => 'Не выбрано', 'class' => 'form-select']) ?>
                </div>
                <div class="card-footer">
                    <div class="d-grid gap-2">
                        <?= Html::submitButton('Save', ['class' => 'btn btn-success', 'role' => 'button']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header d-md-flex justify-content-md-between">
                <div class="pt-1">Комментарии, постер</div>
                <a class="btn btn-sm collapse-button" data-bs-toggle="collapse" href="#collapse-3" role="button"
                   aria-expanded="true" aria-controls="collapseCommentsPoster">
                    <i class="bi bi-plus-lg"></i>
                    <i class="bi bi-dash-lg"></i>
                </a>
            </div>
            <div class="collapse show" id="collapse-3">
                <div class="card-body">
                    <?= $form->field($model, 'comments_allowed')->dropDownList(PostHelper::commentStatusList(), ['class' => 'form-select']) ?>
                    <?php // echo $form->field($model, 'photo')->widget(FileWidget::class); ?>
                    <?php if (!empty($post->photo)): ?>
                        <img src="<?php echo $post->getThumbUrl('photo', 'admin_view'); ?>" alt="">
                    <?php endif; ?>
                    <?= $form->field($model, 'photo')->fileInput() ?>
                </div>
                <div class="card-footer">
                    <div class="d-grid gap-2">
                        <?= Html::submitButton('Save', ['class' => 'btn btn-success', 'role' => 'button']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header d-md-flex justify-content-md-between">
                <div class="pt-1">Content</div>
                <a class="btn btn-sm collapse-button" data-bs-toggle="collapse" href="#collapse-4" role="button"
                   aria-expanded="true" aria-controls="collapseContent">
                    <i class="bi bi-plus-lg"></i>
                    <i class="bi bi-dash-lg"></i>
                </a>
            </div>
            <div class="collapse show" id="collapse-4">
                <div class="card-body">
                    <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'class' => 'form-control']) ?>
                    <?= $form->field($model, 'description')->textarea(['rows' => 5, 'class' => 'form-control']) ?>


                    <?php
                    if (!isset($post)) {
                        echo '<div class="alert alert-danger" role="alert">Перед заполнением контента сохраните пост.</div>';
                    } else {
                        // TODO создавать папку при создании поста. При удалении удалять.
                        $editorConfig = [];
                        $editorConfig['language'] = 'ru';
                        $editorConfig['fmDefaultPath'] = '/static/origin/Blog/' . $post->id;
                        echo $form->field($model, 'content')->widget(\Besnovatyj\File\widgets\customeditor\src\CkeditorCustomWidget::class, $editorConfig);
                    }
                    ?>


                    <?php
                    //                    if (!isset($post)) {
                    //                        echo '<div class="alert alert-danger" role="alert">Перед заполнением контента сохраните пост.</div>';
                    //                    } else {
                    //                        // TODO создавать папку при создании поста. При удалении удалять.
                    //                        $editorConfig = [];
                    //                        $editorConfig['language'] = 'ru';
                    //                        $editorConfig['fmDefaultPath'] = '/origin/Blog/' . $post->id;
                    //                        echo $form->field($model, 'content')->widget(\modules\file\widgets\editor\src\CKEditor5::class, $editorConfig);
                    //                    }
                    ?>
                </div>
                <div class="card-footer">
                    <div class="d-grid gap-2">
                        <?= Html::submitButton('Save', ['class' => 'btn btn-success', 'role' => 'button']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header d-md-flex justify-content-md-between">
                <div class="pt-1">Дополнительные категории</div>
                <a class="btn btn-sm collapse-button" data-bs-toggle="collapse" href="#collapse-1" role="button"
                   aria-expanded="false" aria-controls="collapseTaxonomies">
                    <i class="bi bi-plus-lg"></i>
                    <i class="bi bi-dash-lg"></i>
                </a>
            </div>
            <div class="collapse" id="collapse-1">
                <div class="card-body">
                    <?= $form->field($model->taxonomies, 'others')->checkboxList($model->taxonomies->taxonomiesList()) ?>
                </div>
                <div class="card-footer">
                    <div class="d-grid gap-2">
                        <?= Html::submitButton('Save', ['class' => 'btn btn-success', 'role' => 'button']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header d-md-flex justify-content-md-between">
                <div class="pt-1">SEO</div>
                <a class="btn btn-sm collapse-button" data-bs-toggle="collapse" href="#collapse-5" role="button"
                   aria-expanded="false" aria-controls="collapseSEO">
                    <i class="bi bi-plus-lg"></i>
                    <i class="bi bi-dash-lg"></i>
                </a>
            </div>
            <div class="collapse" id="collapse-5">
                <div class="card-body">
                    <?= $form->field($model->meta, 'title')->textInput(['class' => 'form-control']) ?>
                    <?= $form->field($model->meta, 'description')->textarea(['rows' => 2, 'class' => 'form-control']) ?>
                    <?= $form->field($model->meta, 'keywords')->textInput(['class' => 'form-control']) ?>
                </div>
                <div class="card-footer">
                    <div class="d-grid gap-2">
                        <?= Html::submitButton('Save', ['class' => 'btn btn-success', 'role' => 'button']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>
