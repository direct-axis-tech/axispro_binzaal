<?php

namespace App\Traits;

use Arr;
use Illuminate\Database\Eloquent\Builder;

trait CachesSyntheticAttributes {

    /**
     * Synthetic Attributes array
     *
     * @var array
     */
    protected $syntheticAttributes = [];

    /**
     * Caches the attribute if not exists
     *
     * @param string  $key
     * @param callable $callback
     * @return mixed
     */
    protected function getOrComputeAttribute($key, $callback)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        $value = call_user_func($callback);

        $this->attributes[$key] = $value;
        $this->syntheticAttributes[] = $key;

        return $value;
    }

    /**
     * Get all the synthetic attributes
     *
     * @return array
     */
    public function getSyntheticAttributes() {
        return array_unique($this->syntheticAttributes);
    }

    /**
     * Get the attributes that have been changed since last sync.
     *
     * @return array
     */
    public function getDirty()
    {
        $dirty = [];
        $ownAttributes = Arr::except($this->getAttributes(), $this->getSyntheticAttributes());

        foreach ($ownAttributes as $key => $value) {
            if (! $this->originalIsEquivalent($key, $value)) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    /**
     * Perform a model insert operation.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return bool
     */
    protected function performInsert(Builder $query)
    {
        if ($this->fireModelEvent('creating') === false) {
            return false;
        }

        // First we'll need to create a fresh query instance and touch the creation and
        // update timestamps on this model, which are maintained by us for developer
        // convenience. After, we will just continue saving these model instances.
        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        // If the model has an incrementing key, we can use the "insertGetId" method on
        // the query builder, which will give us back the final inserted ID for this
        // table from the database. Not all tables have to be incrementing though.
        $attributes = Arr::except($this->getAttributes(), $this->getSyntheticAttributes());

        if ($this->getIncrementing()) {
            $this->insertAndSetId($query, $attributes);
        }

        // If the table isn't incrementing we'll simply insert these attributes as they
        // are. These attribute arrays must contain an "id" column previously placed
        // there by the developer as the manually determined key for these models.
        else {
            if (empty($attributes)) {
                return true;
            }

            $query->insert($attributes);
        }

        // We will go ahead and set the exists property to true, so that it is set when
        // the created event is fired, just in case the developer tries to update it
        // during the event. This will allow them to do so and run an update here.
        $this->exists = true;

        $this->wasRecentlyCreated = true;

        $this->fireModelEvent('created', false);

        return true;
    }
}