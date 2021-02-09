<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;

class EduCourses_Teacher extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'edu_courses';
    protected $fillable = ['id', 'course_name', 'course_thumb', 'course_overview', 'total_enrolled', 'certificate_config', 'certificate_name', 'publish_status', 'created_by', 'valid'];

    public function scopeValid($query)
    {
        return $query->where('valid', 1);
    }
    public static function boot()
    {
        parent::EmployeeBoot(0);
    }
}
