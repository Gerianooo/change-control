<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'procedure_id',
        'value',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function procedure()
    {
        return $this->hasOne(Procedure::class, 'id', 'procedure_id');
    }
}
