<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Validator;
use Helper;
use File;
use DB;
use Auth;

use App\Models\EduAssignBatch_Teacher;
use App\Models\EduAssignBatchSchedule_Teacher;
use App\Models\EduAssignBatchStudent_Teacher;

class AssignedBatchController extends Controller
{
	public function assignedBatch()
	{
		$authId = Auth::guard('teacher')->id();
    	$data['assign_batches'] = $assign_batches = EduAssignBatch_Teacher::join('edu_courses', 'edu_courses.id', '=', 'edu_assign_batches.course_id')
			->select('edu_assign_batches.*', 'edu_courses.course_name')
			->where('edu_assign_batches.valid', 1)
            ->where('edu_assign_batches.teacher_id', $authId)
			->orderBy('edu_assign_batches.id', 'desc')
			->get();

        foreach ($assign_batches as $key => $assign_batche) {
        	$assign_batche->schedules = EduAssignBatchSchedule_Teacher::valid()->where('batch_id',$assign_batche->id)->get();
        	$assign_batche->total_students = EduAssignBatchStudent_Teacher::valid()->where('active_status',1)->where('batch_id',$assign_batche->id)->count();
        }
        return view('teacher.assignBatch.listData', $data);

    }

}
