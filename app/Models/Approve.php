<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approve extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'approvable_id',
        'approvable_type',
    ];

    /**
     * @var string[]
     */
    protected $with = [
        'approvals',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function approvals()
    {
        return $this->morphMany(Approval::class, 'approvalable');
    }
}
