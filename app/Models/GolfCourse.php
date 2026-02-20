<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GolfCourse extends Model
{
    protected $fillable = ['name', 'address', 'address_link'];

    public function courseInfo()
    {
        return $this->hasMany(CourseInfo::class);
    }
}
