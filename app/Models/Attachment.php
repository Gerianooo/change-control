<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'attachmentable_id',
        'attachmentable_type',
        'name',
        'filename',
    ];

    /**
     * @var string[]
     */
    protected $appends = [
        'url',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function attachmentable()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function url() : Attribute
    {
        return Attribute::make(
            get: fn () => url('/storage/attachments/' . $this->filename),
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function path() : Attribute
    {
        return Attribute::make(
            get: fn () => Storage::path('/public/attachments/' . $this->filename),
        );
    }

    /**
     * @inheritdoc
     */
    public static function boot()
    {
        parent::boot();

        static::deleted(function (Attachment $attachment) {
            @unlink($attachment->path);
        });
    }
}
