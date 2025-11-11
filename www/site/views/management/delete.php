<?php

/** @var yii\web\View $this */
/** @var app\models\Post $model */

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;

$this->title = 'Delete Post';
$this->params['breadcrumbs'][] = ['label' => 'Posts', 'url' => ['/post/index']];
$this->params['breadcrumbs'][] = $this->title;

// Sanitize message for preview
$message = HtmlPurifier::process($model->message, [
    'HTML.Allowed' => 'b,i,s',
    'AutoFormat.RemoveEmpty' => true,
]);
?>

<div class="post-delete">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h1><?= Html::encode($this->title) ?></h1>

            <div class="alert alert-warning">
                <strong>Warning:</strong> This action cannot be undone. Are you sure you want to delete this post?
                <?php if ($timeRemaining = $model->getDeleteTimeRemaining()): ?>
                    <br>Time remaining to delete: <strong><?= $timeRemaining ?></strong>
                <?php endif; ?>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5>Post Preview</h5>
                </div>
                <div class="card-body">
                    <?php if ($model->image_path): ?>
                        <img src="<?= Html::encode('/' . $model->image_path) ?>" alt="Post image" class="img-fluid mb-3" style="max-width: 400px;">
                    <?php endif; ?>
                    
                    <h5 class="card-title"><?= Html::encode($model->author_name) ?></h5>
                    <p class="card-text"><?= $message ?></p>
                    <p class="card-text">
                        <small class="text-muted">
                            Posted: <?= Yii::$app->formatter->asRelativeTime($model->created_at) ?>
                        </small>
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5>Confirm Deletion</h5>
                </div>
                <div class="card-body">
                    <p>Please confirm that you want to permanently delete this post.</p>
                    
                    <?= Html::beginForm(['management/delete', 'token' => $model->secure_token], 'post') ?>
                        <?= Html::submitButton('Yes, Delete This Post', [
                            'class' => 'btn btn-danger',
                            'data' => [
                                'confirm' => 'Are you absolutely sure you want to delete this post?',
                            ],
                        ]) ?>
                        <?= Html::a('No, Keep This Post', ['/post/index'], ['class' => 'btn btn-secondary']) ?>
                    <?= Html::endForm() ?>
                </div>
            </div>
        </div>
    </div>
</div>
