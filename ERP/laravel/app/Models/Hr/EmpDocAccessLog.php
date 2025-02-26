<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;

class EmpDocAccessLog extends Model
{
    const REQUESTED = 'Release Requested';
    const RELEASED = 'Released';
    const APPROVED = 'Request Approved';
    const REJECTED = 'Request Rejected';
    const CANCELLED = 'Request Cancelled';
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_emp_doc_access_log';

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
     * The employee associated with this leave
     *
     * @return void
     */
    public function employee() {
        return $this->belongsTo(\App\Models\Hr\Employee::class);
    }
    
    /**
     * The employee associated with this leave
     *
     * @return void
     */
    public function documentType() {
        return $this->belongsTo(\App\Models\DocumentType::class, 'document_type_id');
    }
}