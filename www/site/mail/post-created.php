<?php

/** @var app\models\Post $model */
/** @var string $editUrl */
/** @var string $deleteUrl */

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;

// Sanitize message for email preview
$message = HtmlPurifier::process($model->message, [
    'HTML.Allowed' => 'b,i,s',
    'AutoFormat.RemoveEmpty' => true,
]);
?>

<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <div style="background-color: #007bff; color: white; padding: 20px; text-align: center;">
        <h1 style="margin: 0;">StoryVault</h1>
        <p style="margin: 10px 0 0 0;">Your post has been published!</p>
    </div>

    <div style="padding: 20px; background-color: #f8f9fa;">
        <h2>Your Post:</h2>
        
        <div style="background-color: white; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <?php if ($model->image_path): ?>
                <img src="<?= Html::encode(Yii::$app->urlManager->createAbsoluteUrl('/' . $model->image_path)) ?>" 
                     alt="Post image" 
                     style="max-width: 100%; height: auto; margin-bottom: 15px;">
            <?php endif; ?>
            
            <h3 style="color: #333; margin: 0 0 10px 0;"><?= Html::encode($model->author_name) ?></h3>
            <div style="color: #555; line-height: 1.6;"><?= $message ?></div>
            <p style="color: #999; font-size: 12px; margin: 10px 0 0 0;">
                Posted: <?= Yii::$app->formatter->asDatetime($model->created_at) ?>
            </p>
        </div>

        <h2>Manage Your Post:</h2>
        
        <p>You can edit or delete your post using the secure links below:</p>

        <table style="width: 100%; margin: 20px 0;">
            <tr>
                <td style="padding: 10px 0;">
                    <a href="<?= $editUrl ?>" 
                       style="display: inline-block; padding: 12px 30px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;">
                        Edit Post
                    </a>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 0;">
                    <a href="<?= $deleteUrl ?>" 
                       style="display: inline-block; padding: 12px 30px; background-color: #dc3545; color: white; text-decoration: none; border-radius: 5px;">
                        Delete Post
                    </a>
                </td>
            </tr>
        </table>

        <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;">
            <h4 style="margin: 0 0 10px 0; color: #856404;">Important Information:</h4>
            <ul style="margin: 0; padding-left: 20px; color: #856404;">
                <li><strong>Edit window:</strong> You can edit your post for <strong>12 hours</strong> after publication.</li>
                <li><strong>Delete window:</strong> You can delete your post for <strong>14 days</strong> after publication.</li>
                <li>Only the message and image can be edited. Author name and email cannot be changed.</li>
                <li>Keep this email safe - these are the only links to manage your post.</li>
            </ul>
        </div>
    </div>

    <div style="background-color: #343a40; color: white; padding: 15px; text-align: center; font-size: 12px;">
        <p style="margin: 0;">
            This is an automated email from StoryVault. Please do not reply to this message.
        </p>
        <p style="margin: 10px 0 0 0;">
            &copy; <?= date('Y') ?> StoryVault. All rights reserved.
        </p>
    </div>
</div>
