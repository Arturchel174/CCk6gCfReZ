<?php

/** @var yii\web\View $this */
/** @var app\models\Post[] $posts */
/** @var yii\data\Pagination $pagination */
/** @var app\models\Post $model */

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\widgets\LinkPager;
use yii\bootstrap5\ActiveForm;
use yii\captcha\Captcha;
use app\helpers\IpHelper;

$this->title = 'StoryVault – поделитесь своей историей';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="post-index">
    <div class="row">
        <div class="col-md-8">
            <h1><?= Html::encode($this->title) ?></h1>
            <p class="lead">StoryVault – поделитесь своей историей</p>

            <!-- Post Submission Form -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Создать новый пост</h5>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'id' => 'post-form',
                        'action' => ['post/create'],
                        'options' => ['enctype' => 'multipart/form-data'],
                    ]); ?>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'author_name')->textInput(['maxlength' => 15, 'placeholder' => 'Your name (2-15 characters)']) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'email')->textInput(['maxlength' => 255, 'placeholder' => 'your@email.com']) ?>
                        </div>
                    </div>

                    <?= $form->field($model, 'message')->textarea([
                        'rows' => 4,
                        'maxlength' => 1000,
                        'placeholder' => 'Share your message (5-1000 characters)...'
                    ])->hint('You can use <b>bold</b>, <i>italic</i>, and <s>strikethrough</s> tags.') ?>

                    <?= $form->field($model, 'imageFile')->fileInput([
                        'accept' => 'image/jpeg,image/png,image/webp'
                    ])->hint('Optional. Max 2MB, max 1500px on either side. Formats: JPG, PNG, WEBP') ?>

                    <?= $form->field($model, 'verifyCode')->widget(Captcha::class, [
                        'template' => '<div class="row"><div class="col-lg-3">{image}</div><div class="col-lg-9">{input}</div></div>',
                        'captchaAction' => 'post/captcha',
                    ]) ?>

                    <div class="form-group">
                        <?= Html::submitButton('Publish Post', ['class' => 'btn btn-primary btn-lg']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <!-- Posts List -->
            <h2 class="mt-5 mb-4">Последние сообщения</h2>

            <?php if (empty($posts)): ?>
                <div class="alert alert-info">
                    Пока нет публикаций. Будьте первым, кто поделится своей историей!
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <?= $this->render('_card', ['post' => $post]) ?>
                <?php endforeach; ?>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    <?= LinkPager::widget([
                        'pagination' => $pagination,
                        'options' => ['class' => 'pagination'],
                    ]) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Об StoryVault</h5>
                </div>
                <div class="card-body">
                    <p>StoryVault — это место, где вы можете делиться своими мыслями, историями и идеями со всем миром.</p>
                    <hr>
                    <h6>Правила:</h6>
                    <ul class="small">
                        <li>Будьте вежливы и доброжелательны</li>
                        <li>Без спама и оскорбительного контента</li>
                        <li>Одна публикация каждые 3 минуты</li>
                        <li>Вы можете редактировать свою публикацию в течение 12 часов</li>
                        <li>Вы можете удалить свою публикацию в течение 14 дней</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
