<?php

namespace app\controllers;

use Yii;
use app\models\Post;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;

/**
 * ManagementController handles post edit and delete operations via secure tokens
 */
class ManagementController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['get', 'post'],
                ],
            ],
        ];
    }

    /**
     * Edit a post by secure token
     * 
     * @param string $token Secure token
     * @return string|\yii\web\Response
     */
    public function actionEdit($token)
    {
        $model = $this->findModelByToken($token);

        // Check if edit window is still open
        if (!$model->canEdit()) {
            Yii::$app->session->setFlash('error', 'The edit window has expired. Posts can only be edited within 12 hours of creation.');
            return $this->redirect(['/post/index']);
        }

        if ($model->load(Yii::$app->request->post())) {
            // Handle image upload
            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');

            if ($model->validate(['message', 'imageFile'])) {
                // Upload new image if provided
                if ($model->imageFile) {
                    $model->upload();
                }

                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'Your post has been updated successfully!');
                    return $this->redirect(['/post/index']);
                }
            }
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * Delete a post (soft delete) by secure token
     * 
     * @param string $token Secure token
     * @return string|\yii\web\Response
     */
    public function actionDelete($token)
    {
        $model = $this->findModelByToken($token);

        // Check if delete window is still open
        if (!$model->canDelete()) {
            Yii::$app->session->setFlash('error', 'The delete window has expired. Posts can only be deleted within 14 days of creation.');
            return $this->redirect(['/post/index']);
        }

        // Handle POST request (confirmation)
        if (Yii::$app->request->isPost) {
            if ($model->softDelete()) {
                Yii::$app->session->setFlash('success', 'Your post has been deleted successfully.');
                return $this->redirect(['/post/index']);
            } else {
                Yii::$app->session->setFlash('error', 'Failed to delete the post. Please try again.');
            }
        }

        // Show confirmation page
        return $this->render('delete', [
            'model' => $model,
        ]);
    }

    /**
     * Find post model by secure token
     * 
     * @param string $token
     * @return Post
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModelByToken($token)
    {
        // Search without soft delete filter to allow accessing soft-deleted posts
        $model = Post::find()
            ->where(['secure_token' => $token])
            ->andWhere(['deleted_at' => null]) // But still don't allow managing already deleted posts
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException('The requested post does not exist or has been deleted.');
        }

        return $model;
    }
}
