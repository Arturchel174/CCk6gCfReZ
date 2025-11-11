<?php

namespace app\controllers;

use Yii;
use app\models\Post;
use app\components\RateLimiter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\data\Pagination;
use yii\filters\VerbFilter;

/**
 * PostController handles post creation and display operations
 */
class PostController extends Controller
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
                    'create' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
                'minLength' => 4,
                'maxLength' => 4,
            ],
        ];
    }

    /**
     * Lists all Post models with pagination
     * 
     * @return string
     */
    public function actionIndex()
    {
        $query = Post::find()->orderBy(['created_at' => SORT_DESC]);

        $pagination = new Pagination([
            'defaultPageSize' => 10,
            'totalCount' => $query->count(),
        ]);

        $posts = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $model = new Post(['scenario' => 'create']);

        return $this->render('index', [
            'posts' => $posts,
            'pagination' => $pagination,
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Post model
     * 
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Post(['scenario' => 'create']);

        if ($model->load(Yii::$app->request->post())) {
            // Check rate limiting
            $rateLimiter = new RateLimiter();
            $rateLimitCheck = $rateLimiter->checkRateLimit($model->email, $model->ip_address);

            if (!$rateLimitCheck['allowed']) {
                Yii::$app->session->setFlash('error', $rateLimitCheck['message']);
                return $this->redirect(['index']);
            }

            // Handle image upload
            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');

            if ($model->validate()) {
                // Save the post first to get the ID
                if ($model->save(false)) {
                    // Upload image if provided
                    if ($model->imageFile) {
                        $model->upload();
                        $model->save(false); // Save image path
                    }

                    // Send email notification
                    $this->sendManagementEmail($model);

                    Yii::$app->session->setFlash('success', 'Your post has been published successfully! Check your email for management links.');
                    return $this->redirect(['index']);
                }
            } else {
                // Display validation errors
                foreach ($model->errors as $attribute => $errors) {
                    foreach ($errors as $error) {
                        Yii::$app->session->addFlash('error', $error);
                    }
                }
            }
        }

        return $this->redirect(['index']);
    }

    /**
     * Send management email to post author
     * 
     * @param Post $model
     * @return bool
     */
    protected function sendManagementEmail($model)
    {
        try {
            return Yii::$app->mailer->compose('post-created', [
                'model' => $model,
                'editUrl' => Yii::$app->urlManager->createAbsoluteUrl(['management/edit', 'token' => $model->secure_token]),
                'deleteUrl' => Yii::$app->urlManager->createAbsoluteUrl(['management/delete', 'token' => $model->secure_token]),
            ])
                ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                ->setTo($model->email)
                ->setSubject('Your StoryVault post has been published')
                ->send();
        } catch (\Exception $e) {
            Yii::error('Failed to send management email: ' . $e->getMessage());
            return false;
        }
    }
}
