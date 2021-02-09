<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;

class EduAssignmentSubmission_Teacher extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'edu_assignment_submissions';
    protected $fillable = ['id', 'assignment_id', 'comment', 'submission_date','submission_time', 'late_submit', 'mark', 'mark_by', 'created_by', 'valid'];

    public function scopeValid($query)
    {
        return $query->where('valid', 1);
    }
    public static function boot()
    {
        parent::EmployeeBoot(0);
    }

}
