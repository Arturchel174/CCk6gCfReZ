<?php

/** @var yii\web\View $this */
/** @var app\models\Post $model */

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Edit Post';
$this->params['breadcrumbs'][] = ['label' => 'Posts', 'url' => ['/post/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="post-edit">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h1><?= Html::encode($this->title) ?></h1>

            <div class="alert alert-info">
                <strong>Note:</strong> You can only edit the message and optionally replace the image.
                Author name and email cannot be changed.
                <?php if ($timeRemaining = $model->getEditTimeRemaining()): ?>
                    <br>Time remaining to edit: <strong><?= $timeRemaining ?></strong>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>Edit Your Post</h5>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

                    <div class="form-group">
                        <label>Author Name</label>
                        <p class="form-control-plaintext"><strong><?= Html::encode($model->author_name) ?></strong></p>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <p class="form-control-plaintext"><strong><?= Html::encode($model->email) ?></strong></p>
                    </div>

                    <?= $form->field($model, 'message')->textarea([
                        'rows' => 6,
                        'maxlength' => 1000,
                    ])->hint('You can use <b>bold</b>, <i>italic</i>, and <s>strikethrough</s> tags.') ?>

                    <?php if ($model->image_path): ?>
                        <div class="form-group">
                            <label>Current Image</label><br>
                            <img src="<?= Html::encode('/' . $model->image_path) ?>" alt="Current image" class="img-thumbnail" style="max-width: 300px;">
                        </div>
                    <?php endif; ?>

                    <?= $form->field($model, 'imageFile')->fileInput([
                        'accept' => 'image/jpeg,image/png,image/webp'
                    ])->hint('Optional. Uploading a new image will replace the current one. Max 2MB, max 1500px. Formats: JPG, PNG, WEBP') ?>

                    <div class="form-group mt-3">
                        <?= Html::submitButton('Update Post', ['class' => 'btn btn-primary']) ?>
                        <?= Html::a('Cancel', ['/post/index'], ['class' => 'btn btn-secondary']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
