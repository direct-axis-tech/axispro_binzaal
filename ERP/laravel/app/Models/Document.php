<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_documents';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'expires_on',
        'issued_on'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'context' => 'array'
    ];

    /**
     * Scopes this query with the entity
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfEmployee($query, $employeeId) {
        return $query->where([
            ['entity_type', '=', Entity::EMPLOYEE],
            ['entity_id', '=', $employeeId],
        ]);
    }

    /**
     * Scopes this query with the entity
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfLabour($query, $labourId) {
        return $query->where([
            ['entity_type', '=', Entity::LABOUR],
            ['entity_id', '=', $labourId],
        ]);
    }

    /**
     * Scopes this query with the type of document
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type) {
        return $query->where('document_type', $type);
    }

    /**
     * Get the type of this document
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type() {
        return $this->belongsTo(\App\Models\DocumentType::class, 'document_type');
    }

    /**
     * Get the owner entity this document belongs to
     */
    public function owner() {
        return $this->morphTo(__FUNCTION__, 'entity_type', 'entity_id');
    }
}
