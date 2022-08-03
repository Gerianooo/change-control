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
        'classification',
        'reason_change',
        'level',
        'expired_at',
        'created_by_id',
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
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachmentable');
    }

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
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function createdBy()
    {
        return $this->hasOne(User::class, 'id', 'created_by_id');
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
                $revision->code = preg_replace_callback(sprintf('/^(%s)-([\d]+)-([\d]+)$/', $document->code), function ($matches) use ($revision) {
                    $major = (int) $matches[2];
                    $minor = (int) $matches[3];

                    if (mb_strtolower($revision->classification) === 'major') {
                        $major += 1;
                        $minor = 0;
                    } else {
                        $minor += 1;
                    }

                    return sprintf('%s-%d-%d', $matches[1], $major, $minor);
                }, $latest->code);
            } else {
                $revision->code = $document->code . '-0-0';
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

        static::deleted(function (Revision $revision) {
            $revision->attachments()->delete();
        });
    }
}
