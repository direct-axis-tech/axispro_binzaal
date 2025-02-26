<?php

namespace App\Models;

use App\Traits\CachesSyntheticAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DocumentType extends Model
{
    use CachesSyntheticAttributes;

    const EMP_PASSPORT = 1;
    const SPECIAL_RESERVED = 1000;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_document_types';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Scope a query to only include the document types of specified entity.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $entity
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfEntity($query, $entity) {
        return $query->where('entity_type', $entity);
    }

    /**
     * Indicates if the department is already been used
     *
     * @return bool
     */
    public function getIsUsedAttribute()
    {
        return $this->getOrComputeAttribute('is_used', function() {
            return  data_get(
                DB::selectOne(
                    'select '.static::isUsedQuery('docType.id').' as is_used'
                    . ' from 0_document_types docType'
                    . ' where docType.id = ' . quote($this->id)
                ),
                'is_used'
            );
        });
    }

    /**
     * Returns the conditions for is_used query
     *
     * @param string $onClause
     * @return string
     */
    public static function isUsedQuery($onClause)
    {
        return "("
             . "exists(select 1 from `0_documents` used where used.document_type = {$onClause})"
            . " OR is_reserved"
        .")";
    }
}
