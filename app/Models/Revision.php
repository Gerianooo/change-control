<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Revision extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'document_id',
        'code',
        'expired_at',
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
     * @var string[]
     */
    protected $withCount = [
        'approvers',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function approves()
    {
        return $this->morphMany(Approve::class, 'approvable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function approve()
    {
        return $this->morphOne(Approve::class, 'approvable')->orderBy('created_at', 'desc');
    }

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
        return $this->morphMany(Approval::class, 'approvalable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function document()
    {
        return $this->hasOne(Document::class, 'id', 'document_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function procedures()
    {
        return $this->hasMany(Procedure::class, 'revision_id', 'id')
                    ->orderBy('position')
                    ->whereNull('parent_id')
                    ->with(['childs'])
                    ->withCount('childs');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function pending() : Attribute
    {
        return Attribute::make(
            get: function () {
                if ($approve = $this->approve) {
                    return ! $this->rejected && ! $this->approved;
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

    /**
     * @inheritdoc
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function (Revision $revision) {
            $document = $revision->document;
            $latest = $document->revision;

            if ($latest) {
                $revision->code = preg_replace_callback(sprintf('/^(%s)-([\d]+)$/', $document->code), function ($matches) {
                    $number = (int) $matches[2];

                    return sprintf('%s-%d', $matches[1], $number + 1);
                }, $latest->code);
            } else {
                $revision->code = $document->code . '-0';
            }
        });

        static::created(function (Revision $revision) {
            Revision::where('id', '!=', $revision->id)
                    ->where('created_at', '<=', $revision->created_at)
                    ->orderBy('created_at', 'desc')
                    ->update([
                        'expired_at' => now(),
                    ]);
        });
    }
}
