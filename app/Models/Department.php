<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    //
    protected $fillable = [
        'name',
        'code',
        'description',
        'manager_id',
        'location_id',
    ];
    // protected $with = ['employees'];
    protected $casts = [
        'manager_id' => 'integer',
        'location_id' => 'integer',
    ];

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }
    public function employees()
    {
        return $this->hasMany(Employee::class, 'department_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
