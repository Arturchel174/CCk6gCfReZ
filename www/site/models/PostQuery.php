<?php

namespace app\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Post]].
 *
 * @see Post
 */
class PostQuery extends ActiveQuery
{
    /**
     * Filter query to exclude soft-deleted posts by default
     * 
     * @return $this
     */
    public function active()
    {
        return $this->andWhere(['deleted_at' => null]);
    }

    /**
     * Filter query to include only soft-deleted posts
     * 
     * @return $this
     */
    public function deleted()
    {
        return $this->andWhere(['not', ['deleted_at' => null]]);
    }

    /**
     * {@inheritdoc}
     * @return Post[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Post|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * Override to apply soft delete filter by default
     * {@inheritdoc}
     */
    public function prepare($builder)
    {
        // Automatically filter out soft-deleted records unless explicitly querying for them
        if (!$this->where || !$this->isQueryingDeletedRecords()) {
            $this->active();
        }
        
        return parent::prepare($builder);
    }

    /**
     * Check if the query is explicitly looking for deleted records
     * 
     * @return bool
     */
    private function isQueryingDeletedRecords()
    {
        $where = $this->where;
        
        if (is_array($where)) {
            return $this->checkWhereForDeletedAt($where);
        }
        
        return false;
    }

    /**
     * Recursively check WHERE conditions for deleted_at references
     * 
     * @param array $conditions
     * @return bool
     */
    private function checkWhereForDeletedAt($conditions)
    {
        foreach ($conditions as $key => $value) {
            if ($key === 'deleted_at' || (is_string($key) && strpos($key, 'deleted_at') !== false)) {
                return true;
            }
            
            if (is_array($value) && $this->checkWhereForDeletedAt($value)) {
                return true;
            }
        }
        
        return false;
    }
}
