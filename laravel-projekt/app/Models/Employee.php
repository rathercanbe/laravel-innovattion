<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'first_name', 'last_name'];

    /**
     * Relacja one-to-many z WorkTime
     *
     * @return HasMany
     */
    public function workTimes(): HasMany
    {
        return $this->hasMany(WorkTime::class);
    }
}
