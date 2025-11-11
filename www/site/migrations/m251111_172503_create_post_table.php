<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%post}}`.
 * Creates the post table for StoryVault application with all required fields.
 */
class m251111_172503_create_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%post}}', [
            'id' => $this->primaryKey(),
            'author_name' => $this->string(15)->notNull()->comment('Author name (2-15 characters)'),
            'email' => $this->string(255)->notNull()->comment('Author email address'),
            'message' => $this->text()->notNull()->comment('Post message content (5-1000 characters)'),
            'ip_address' => $this->string(45)->notNull()->comment('Author IP address (supports IPv4 and IPv6)'),
            'image_path' => $this->string(255)->null()->comment('Path to uploaded image (optional)'),
            'created_at' => $this->integer()->notNull()->comment('Publication timestamp (Unix time)'),
            'updated_at' => $this->integer()->null()->comment('Last modification timestamp (Unix time)'),
            'deleted_at' => $this->integer()->null()->comment('Soft deletion timestamp (Unix time)'),
            'secure_token' => $this->string(64)->notNull()->unique()->comment('Secure token for management links'),
        ]);

        // Add indexes for performance optimization
        $this->createIndex(
            'idx-post-ip_address',
            '{{%post}}',
            'ip_address'
        );

        $this->createIndex(
            'idx-post-email',
            '{{%post}}',
            'email'
        );

        $this->createIndex(
            'idx-post-created_at',
            '{{%post}}',
            'created_at'
        );

        $this->createIndex(
            'idx-post-deleted_at',
            '{{%post}}',
            'deleted_at'
        );

        $this->createIndex(
            'idx-post-secure_token',
            '{{%post}}',
            'secure_token',
            true // unique index
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%post}}');
    }
}
