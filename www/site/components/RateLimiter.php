<?php

namespace app\components;

use Yii;
use app\models\Post;

/**
 * RateLimiter component for controlling post submission frequency
 * 
 * Supports three modes:
 * - 'ip': Rate limit by IP address only
 * - 'email': Rate limit by email address only
 * - 'combined': Rate limit by both IP and email (must match both)
 */
class RateLimiter
{
    /**
     * Check if a user can post based on configured rate limiting rules
     * 
     * @param string $email User's email address
     * @param string $ipAddress User's IP address
     * @return array ['allowed' => bool, 'message' => string, 'next_allowed_time' => int|null]
     */
    public function checkRateLimit($email, $ipAddress)
    {
        $mode = Yii::$app->params['post_rate_limit_mode'];
        $interval = Yii::$app->params['post_rate_limit_interval'];
        
        $query = Post::find();
        
        // Apply mode-specific filtering
        switch ($mode) {
            case 'email':
                $query->andWhere(['email' => $email]);
                break;
                
            case 'combined':
                $query->andWhere(['email' => $email, 'ip_address' => $ipAddress]);
                break;
                
            case 'ip':
            default:
                $query->andWhere(['ip_address' => $ipAddress]);
                break;
        }
        
        // Get the most recent post
        $lastPost = $query->orderBy(['created_at' => SORT_DESC])->one();
        
        if ($lastPost) {
            $timeSinceLastPost = time() - $lastPost->created_at;
            
            if ($timeSinceLastPost < $interval) {
                $timeRemaining = $interval - $timeSinceLastPost;
                $nextAllowedTime = $lastPost->created_at + $interval;
                
                return [
                    'allowed' => false,
                    'message' => $this->formatRateLimitMessage($timeRemaining, $nextAllowedTime),
                    'next_allowed_time' => $nextAllowedTime,
                    'time_remaining' => $timeRemaining,
                ];
            }
        }
        
        return [
            'allowed' => true,
            'message' => '',
            'next_allowed_time' => null,
            'time_remaining' => 0,
        ];
    }

    /**
     * Format a user-friendly rate limit error message
     * 
     * @param int $timeRemaining Seconds until next post is allowed
     * @param int $nextAllowedTime Unix timestamp when next post is allowed
     * @return string
     */
    protected function formatRateLimitMessage($timeRemaining, $nextAllowedTime)
    {
        $minutes = floor($timeRemaining / 60);
        $seconds = $timeRemaining % 60;
        
        $timeString = '';
        if ($minutes > 0) {
            $timeString .= $minutes . ' minute' . ($minutes > 1 ? 's' : '');
            if ($seconds > 0) {
                $timeString .= ' and ' . $seconds . ' second' . ($seconds > 1 ? 's' : '');
            }
        } else {
            $timeString = $seconds . ' second' . ($seconds > 1 ? 's' : '');
        }
        
        $nextAllowedTimeFormatted = Yii::$app->formatter->asDatetime($nextAllowedTime, 'php:H:i:s');
        
        return "You can only post once every 3 minutes. Please wait {$timeString} before posting again (next allowed: {$nextAllowedTimeFormatted}).";
    }

    /**
     * Get time remaining until next post is allowed
     * 
     * @param string $email User's email address
     * @param string $ipAddress User's IP address
     * @return int|null Seconds remaining, or null if allowed to post
     */
    public function getTimeRemaining($email, $ipAddress)
    {
        $result = $this->checkRateLimit($email, $ipAddress);
        return $result['allowed'] ? null : $result['time_remaining'];
    }

    /**
     * Check if a specific user is currently rate limited
     * 
     * @param string $email User's email address
     * @param string $ipAddress User's IP address
     * @return bool
     */
    public function isRateLimited($email, $ipAddress)
    {
        return !$this->checkRateLimit($email, $ipAddress)['allowed'];
    }
}
