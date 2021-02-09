<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;

class EduClassAssignments_User extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'edu_class_assignments';
    protected $fillable = ['id', 'batch_id', 'course_id', 'assign_batch_class_id', 'title', 'overview', 'start_date', 'due_date', 'due_time', 'created_by', 'valid'];

    public function scopeValid($query)
    {
        $authId = Auth::id();
        return $query->where('valid', 1);
    }
    public static function boot()
    {
        parent::studentBoot();
    }
}
