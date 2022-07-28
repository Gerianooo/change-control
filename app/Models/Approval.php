<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'approvalable_type',
        'approvalable_id',
        'status',
        'requester_id',
        'requested_at',
        'requester_note',
        'responder_id',
        'responded_at',
        'responder_note',
    ];

    /**
     * @var string[]
     */
    protected $with = [
        'requester',
        'responder',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function approvalable()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function requester()
    {
        return $this->hasOne(User::class, 'id', 'requester_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function responder()
    {
        return $this->hasOne(User::class, 'id', 'responder_id');
    }

    /**
     * @inheritdoc
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function (Approval $approval) {
            $approval->requested_at = now();
        });

        static::updating(function (Approval $approval) {
            $approval->responded_at = now();
        });
    }
}
