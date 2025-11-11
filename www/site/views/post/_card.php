<?php

/** @var app\models\Post $post */

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use app\helpers\IpHelper;

// Sanitize message - allow only b, i, s tags
$message = HtmlPurifier::process($post->message, [
    'HTML.Allowed' => 'b,i,s',
    'AutoFormat.RemoveEmpty' => true,
]);

$maskedIp = IpHelper::mask($post->ip_address);

$postCount = $post->getPostCountByIp();

$createdAtRelative = Yii::$app->formatter->asRelativeTime($post->created_at);

$postCountText = Yii::t('app', '{n, plural, =0{нет постов} one{# пост} few{# поста} many{# постов} other{# поста}}', ['n' => $postCount]);
?>

<div class="card card-default mb-3">
    <?php if ($post->image_path): ?>
        <img src="<?= Html::encode('/' . $post->image_path) ?>" class="card-img-top" alt="Image from <?= Html::encode($post->author_name) ?>">
    <?php endif; ?>
    
    <div class="card-body">
        <h5 class="card-title"><?= Html::encode($post->author_name) ?></h5>
        <p class="card-text"><?= $message ?></p>
        <p class="card-text">
            <small class="text-muted">
                <?= $createdAtRelative ?> | 
                <?= Html::encode($maskedIp) ?> | 
                <?= $postCountText ?>
            </small>
        </p>
    </div>
</div>
