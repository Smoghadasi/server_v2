<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait SelfParental
{
    protected $parentColumn = 'parent_id';

    /**
     * Get parent of model
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(static::class, $this->parentColumn);
    }

    /**
     * Get children of model
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(static::class, $this->parentColumn);
    }

    /**
     * Get all children of model
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    /**
     * Get all parents of model
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function parentsRecursive()
    {
        return $this->parent()->with('parentsRecursive');
    }

    /**
     * Get all parents of model
     *
     * @return Model
     */
    public function root()
    {
        return $this->parent
            ? $this->parent->root()
            : $this;
    }
}
