<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Content extends Model
{
    use HasFactory, Notifiable;

    /**
     * @var string[]
     */
    protected $fillable = [
        'procedure_id',
        'value',
    ];

    /**
     * @return array
     */
    public function via()
    {
        return ['broadcast'];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function procedure()
    {
        return $this->hasOne(Procedure::class, 'id', 'procedure_id');
    }
}
