<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approver extends Model
{
    use HasFactory;
    
    /**
     * @var string[]
     */
    protected $fillable = [
        'approverable_id',
        'approverable_type',
        'user_id',
        'position',
    ];

    /**
     * @var string[]
     */
    protected $with = [
        'user',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function approverable()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * @inheritdoc
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function (Approver $approver) {
            $approver->position = $approver->approverable->approvers->count() + 1;
        });
    }
}
