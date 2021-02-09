<?php

namespace App\Http\Controllers\Teacher;

use Auth;
use Validator;

use App\Models\EduTeachers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\EduAssignBatch_Teacher;
use App\Models\EduStudentExam_Teacher;
use App\Models\EduClassAssignments_Teacher;
use App\Models\EduAssignBatchClasses_Teacher;
use App\Models\EduAssignmentSubmission_Teacher;
use App\Models\EduCourseClassMaterials_Teacher;

class MasterController extends Controller
{
    public function getLogin()
    {
        return view('teacher.login');
    }
    public function postLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|min:8'
        ]);
        $data = array(
            'email'          => $request->email,
            'password'       => $request->password,
            'email_verified' => 1,
            'status'         => 'Active',
            'valid'          => 1
        );
        if (Auth::guard('teacher')->attempt($data)) {
            return redirect()->route('teacher.home');
        } else {
            return redirect()->route('teacher.login')->with('error', 'Email or password is not correct.');
        }
    }
    public function logout()
    {
        Auth::guard('teacher')->logout();
        return redirect()->route('teacher.login');

    }
    public function home(){
        $authId = Auth::guard('teacher')->id();
        $data['userInfo'] = EduTeachers::where('valid', 1)->find($authId);
        return view('teacher.home', $data);

    }

    public function dashboard(){
        $authId = Auth::guard('teacher')->id();
        $data['my_assigned_batches'] = $my_assigned_batches = EduAssignBatch_Teacher::valid()->where('teacher_id', $authId)->get();
        foreach ($my_assigned_batches as $key => $batch) {
            // RUNNING CLASSES
            $batch->running_class = $running_class = EduAssignBatchClasses_Teacher::join('edu_course_assign_classes', 'edu_course_assign_classes.id', '=', 'edu_assign_batch_classes.class_id')
                ->select('edu_assign_batch_classes.*', 'edu_course_assign_classes.class_name')
                ->where('edu_assign_batch_classes.valid', 1)
                ->where('edu_assign_batch_classes.batch_id', $batch->id)
                ->where('edu_assign_batch_classes.complete_status', 2)
                ->orderBy('edu_assign_batch_classes.id', 'desc')
                ->first();

            if (!empty($running_class)) {
                //FOR QUIZ
                $batch->run_total_given_quiz = EduStudentExam_Teacher::valid()->where('assign_batch_class_id', $running_class->id)->count();
                // FOR ASSIGNMENT
                $running_class_assignment_ids = EduClassAssignments_Teacher::valid()->where('assign_batch_class_id', $running_class->id)->get()->pluck('id');
                $batch->run_total_submitted_assignment = EduAssignmentSubmission_Teacher::valid()->whereIn('assignment_id', $running_class_assignment_ids)->count();
            } else {
                $batch->run_total_given_quiz = 0;
                $batch->run_total_submitted_assignment = 0;
            }
            //COMPLETED CLASSES
            $batch->completed_class = $completed_class = EduAssignBatchClasses_Teacher::join('edu_course_assign_classes', 'edu_course_assign_classes.id', '=', 'edu_assign_batch_classes.class_id')
                ->select('edu_assign_batch_classes.*', 'edu_course_assign_classes.class_name')
                ->where('edu_assign_batch_classes.valid', 1)
                ->where('edu_assign_batch_classes.batch_id', $batch->id)
                ->where('edu_assign_batch_classes.complete_status', 1)
                ->orderBy('edu_assign_batch_classes.id', 'desc')
                ->first();

            if (!empty($completed_class)) {
                //FOR QUIZ
                $batch->com_total_given_quiz = EduStudentExam_Teacher::valid()->where('assign_batch_class_id', $completed_class->id)->count();
                // FOR ASSIGNMENT
                $completed_class_assignment_ids = EduClassAssignments_Teacher::valid()->where('assign_batch_class_id', $completed_class->id)->get()->pluck('id');
                $batch->com_total_submitted_assignment = EduAssignmentSubmission_Teacher::valid()->whereIn('assignment_id', $completed_class_assignment_ids)->count();
            } else {
                $batch->com_total_given_quiz = 0;
                $batch->com_total_submitted_assignment = 0;
            }
                
        }
        return view('teacher.dashboard.dashboard', $data);

    }

    public function classVideos(Request $request, $class_id)
    {
        // dd($class_id);
        $data['class_materials'] = EduCourseClassMaterials_Teacher::valid()->where('class_id', $class_id)->get();
        return view('teacher.dashboard.showMaterial', $data);
    }

}
