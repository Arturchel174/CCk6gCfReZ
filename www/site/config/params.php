<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'StoryVault',
    
    // StoryVault configuration parameters
    'post_rate_limit_mode' => 'ip', // Options: 'ip', 'email', 'combined'
    'post_rate_limit_interval' => 180,
    'post_image_max_size' => 2097152,
    'post_image_max_dimension' => 1500,
    'post_edit_window' => 43200, // 12 hours in seconds
    'post_delete_window' => 1209600, // 14 days in seconds
    'upload_directory' => 'uploads/posts', // Relative to web root
];
