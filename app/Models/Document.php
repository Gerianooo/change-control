<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'code',
        'max_revision_interval',
    ];

    /**
     * @var string[]
     */
    protected $with = [
        'revision',
        'approve',
    ];

    /**
     * @var string[]
     */
    protected $withCount = [
        'approvers',
    ];

    /**
     * @var string[]
     */
    protected $appends = [
        'pending',
        'approved',
        'rejected',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function approvers()
    {
        return $this->morphMany(Approver::class, 'approverable')->orderBy('position');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function approvals()
    {
        return $this->morphMany(Approval::class, 'approvalable')->orderBy('created_at');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function approval()
    {
        return $this->morphOne(Approval::class, 'approvelable')->orderBy('created_at', 'desc');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function approves()
    {
        return $this->morphMany(Approve::class, 'approvable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function approve()
    {
        return $this->morphOne(Approve::class, 'approvable')->orderBy('created_at', 'desc');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function revisions()
    {
        return $this->hasMany(Revision::class, 'document_id', 'id')->orderBy('created_at', 'desc');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function revision()
    {
        return $this->hasOne(Revision::class, 'document_id', 'id')->orderBy('created_at', 'desc');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function authorizations()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function pending() : Attribute
    {
        return Attribute::make(
            get: function () {
                if ($approve = $this->approve) {
                    return $approve->approvals
                                    ->where('status', '!=', 'rejected')
                                    ->where('status', 'pending')
                                    ->isNotEmpty();
                }

                return false;
            },
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function rejected() : Attribute
    {
        return Attribute::make(
            get: function () {
                if ($approve = $this->approve) {
                    return $approve->approvals
                                    ->where('status', 'rejected')
                                    ->isNotEmpty();
                }

                return false;
            },
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function approved() : Attribute
    {
        return Attribute::make(
            get: function () {
                if ($approve = $this->approve) {
                    return $approve->approvals->count() === $approve->approvals->where('status', 'approved')->count();
                }

                return false;
            },
        );
    }
}
