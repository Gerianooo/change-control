<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Procedure extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'revision_id',
        'parent_id',
        'name',
        'position',
    ];

    /**
     * @var string[]
     */
    protected $with = [
        'content',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function revision()
    {
        return $this->hasOne(Revision::class, 'id', 'revision_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function parent()
    {
        return $this->hasOne(Procedure::class, 'id', 'parent_id')->without('child')->withCount('childs');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function childs()
    {
        return $this->hasMany(Procedure::class, 'parent_id', 'id')->orderBy('position', 'asc')->with(['parent', 'childs']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function content()
    {
        return $this->hasOne(Content::class, 'procedure_id', 'id');
    }
}
