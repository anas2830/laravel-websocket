<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;

class EduTeacherUsers_Teacher extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'edu_teachers';
    protected $fillable = ['id','teacher_id', 'name','address', 'email', 'phone','password','active_status','image','status','created_by', 'valid'];

    public function scopeValid($query)
    {
        $authId = Auth::guard('teacher')->id();
        return $query->where('id', $authId)->where('valid', 1);
    }
    public static function boot()
    {
        parent::EmployeeBoot(0);
    }
}
