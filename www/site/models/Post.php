<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\web\UploadedFile;

/**
 * This is the model class for table "post".
 *
 * @property int $id
 * @property string $author_name
 * @property string $email
 * @property string $message
 * @property string $ip_address
 * @property string|null $image_path
 * @property int $created_at
 * @property int|null $updated_at
 * @property int|null $deleted_at
 * @property string $secure_token
 * 
 * @property UploadedFile|null $imageFile
 */
class Post extends ActiveRecord
{
    /**
     * @var UploadedFile
     */
    public $imageFile;

    /**
     * @var string Captcha input
     */
    public $verifyCode;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'post';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => time(),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // Required fields
            [['author_name', 'email', 'message'], 'required'],
            
            // Author name validation
            ['author_name', 'string', 'min' => 2, 'max' => 15],
            ['author_name', 'trim'],
            
            // Email validation
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            
            // Message validation
            ['message', 'string', 'min' => 5, 'max' => 1000],
            ['message', 'trim'],
            ['message', 'filter', 'filter' => function($value) {
                // Reject whitespace-only messages
                return trim($value) === '' ? null : $value;
            }],
            ['message', 'required', 'message' => 'Message cannot consist only of whitespace.'],
            
            // IP address
            ['ip_address', 'string', 'max' => 45],
            
            // Image file validation
            ['imageFile', 'file', 
                'skipOnEmpty' => true,
                'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
                'maxSize' => Yii::$app->params['post_image_max_size'],
                'checkExtensionByMimeType' => true,
            ],
            
            // Image validation by dimensions
            ['imageFile', 'validateImageDimensions'],
            
            // Captcha validation (only for new posts)
            ['verifyCode', 'captcha', 'captchaAction' => 'post/captcha', 'on' => 'create'],
            ['verifyCode', 'required', 'on' => 'create'],
            
            // Secure token
            ['secure_token', 'string', 'max' => 64],
            ['secure_token', 'unique'],
            
            // Timestamps
            [['created_at', 'updated_at', 'deleted_at'], 'integer'],
            
            // Image path
            ['image_path', 'string', 'max' => 255],
        ];
    }

    /**
     * Custom validator for image dimensions
     * 
     * @param string $attribute
     * @param mixed $params
     */
    public function validateImageDimensions($attribute, $params)
    {
        if ($this->imageFile) {
            $maxDimension = Yii::$app->params['post_image_max_dimension'];
            $imageInfo = getimagesize($this->imageFile->tempName);
            
            if ($imageInfo) {
                list($width, $height) = $imageInfo;
                if ($width > $maxDimension || $height > $maxDimension) {
                    $this->addError($attribute, "Image dimensions must not exceed {$maxDimension}px on either side. Current: {$width}x{$height}px");
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'author_name' => 'Your Name',
            'email' => 'Email',
            'message' => 'Message',
            'ip_address' => 'IP Address',
            'image_path' => 'Image',
            'imageFile' => 'Upload Image (optional)',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'secure_token' => 'Secure Token',
            'verifyCode' => 'Verification Code',
        ];
    }

    /**
     * Default query to exclude soft-deleted posts
     * 
     * {@inheritdoc}
     * @return PostQuery
     */
    public static function find()
    {
        return new PostQuery(get_called_class());
    }

    /**
     * Generate a cryptographically secure token
     * 
     * @return string
     */
    public static function generateSecureToken()
    {
        return bin2hex(random_bytes(32)); // 64 characters
    }

    /**
     * Soft delete the post
     * 
     * @return bool
     */
    public function softDelete()
    {
        $this->deleted_at = time();
        return $this->save(false);
    }

    /**
     * Check if the post is soft deleted
     * 
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted_at !== null;
    }

    /**
     * Check if edit window is still open (12 hours from creation)
     * 
     * @return bool
     */
    public function canEdit()
    {
        $editWindow = Yii::$app->params['post_edit_window'];
        return (time() - $this->created_at) < $editWindow;
    }

    /**
     * Check if delete window is still open (14 days from creation)
     * 
     * @return bool
     */
    public function canDelete()
    {
        $deleteWindow = Yii::$app->params['post_delete_window'];
        return (time() - $this->created_at) < $deleteWindow;
    }

    /**
     * Get time remaining for editing in human-readable format
     * 
     * @return string|null
     */
    public function getEditTimeRemaining()
    {
        if (!$this->canEdit()) {
            return null;
        }
        
        $editWindow = Yii::$app->params['post_edit_window'];
        $timeElapsed = time() - $this->created_at;
        $timeRemaining = $editWindow - $timeElapsed;
        
        return Yii::$app->formatter->asDuration($timeRemaining);
    }

    /**
     * Get time remaining for deletion in human-readable format
     * 
     * @return string|null
     */
    public function getDeleteTimeRemaining()
    {
        if (!$this->canDelete()) {
            return null;
        }
        
        $deleteWindow = Yii::$app->params['post_delete_window'];
        $timeElapsed = time() - $this->created_at;
        $timeRemaining = $deleteWindow - $timeElapsed;
        
        return Yii::$app->formatter->asDuration($timeRemaining);
    }

    /**
     * Upload and save the image file
     * 
     * @return bool
     */
    public function upload()
    {
        if ($this->imageFile) {
            $uploadDir = Yii::getAlias('@webroot/' . Yii::$app->params['upload_directory']);
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $extension = $this->imageFile->extension;
            $filename = $this->id . '_' . time() . '_' . Yii::$app->security->generateRandomString(8) . '.' . $extension;
            $filePath = $uploadDir . '/' . $filename;
            
            // Delete old image if exists
            if ($this->image_path) {
                $oldPath = Yii::getAlias('@webroot/' . $this->image_path);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            
            // Save new image
            if ($this->imageFile->saveAs($filePath)) {
                $this->image_path = Yii::$app->params['upload_directory'] . '/' . $filename;
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get the count of posts by the same IP address
     * 
     * @return int
     */
    public function getPostCountByIp()
    {
        return static::find()
            ->where(['ip_address' => $this->ip_address])
            ->count();
    }

    /**
     * Hook to set default values before validation
     */
    public function beforeValidate()
    {
        if ($this->isNewRecord) {
            // Capture IP address
            if (!$this->ip_address) {
                $this->ip_address = Yii::$app->request->userIP;
            }
            
            // Generate secure token
            if (!$this->secure_token) {
                $this->secure_token = static::generateSecureToken();
            }
        }
        
        return parent::beforeValidate();
    }
}
