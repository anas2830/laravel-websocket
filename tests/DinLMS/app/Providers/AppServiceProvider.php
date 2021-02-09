<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

use Auth;
use Helper;
use App\Models\EduProviderUsers;
use App\Models\EduTeachers;
use App\Models\User;
use App\Models\EduSupports;
use App\Models\EduStudentPracticeTime_User;
use App\Models\EduAssignBatchStudent_User;
use App\Models\EduActivityNotify_User;
use App\Models\EduNotifySeenHistory_User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // BOTH ARE WORKING
        // $authId = Auth::guard('provider')->id();
        // $data['userInfo'] = EduProviderUsers::where('valid', 1)->find($authId);
        // View::share($data);
        view()->composer(
            'provider.layouts.default',
            function ($view)
            {
                $authId = Auth::guard('provider')->id();
                $data['userInfo'] = EduProviderUsers::where('valid', 1)->find($authId);
                $view->with($data);
            }
        );
        view()->composer(
            'teacher.layouts.default',
            function ($view)
            {
                $authId = Auth::guard('teacher')->id();
                $data['userInfo'] = EduTeachers::where('valid', 1)->find($authId);
                $view->with($data);
            }
        );
        view()->composer(
            'support.layouts.default',
            function ($view)
            {
                $authId = Auth::guard('support')->id();
                $data['userInfo'] = EduSupports::where('valid', 1)->find($authId);
                $view->with($data);
            }
        );
        view()->composer(
            'layouts.default',
            function ($view)
            {
                $data['auth_id'] = $authId = Auth::id();
                $data['userInfo'] = User::find($authId);
                $current_date = date('Y-m-d');
                $data['student_course_info'] = $student_course_info = EduAssignBatchStudent_User::valid()->where('is_running', 1)->where('active_status', 1)->first();
                $today_practice_time = EduStudentPracticeTime_User::valid()
                    ->where('student_id', $authId)
                    ->whereDate('date',$current_date)
                    ->first();

                if(!empty($today_practice_time)){
                    $today_practice_time = @Helper::secondsToTime($today_practice_time->total_time);
                    $time_array = explode(':',$today_practice_time);
                    $data['hour'] = $time_array[0];
                    $data['minute'] = $time_array[1];
                    $data['seconds'] = $time_array[2];
                }else{
                    $data['today_practice_time'] = '';
                    $data['hour'] = 00;
                    $data['minute'] = 00;
                    $data['seconds'] = 00;
                }

                $student_batch_id = $student_course_info->batch_id;

                $data['student_notify'] = $student_notify = EduActivityNotify_User::valid()
                    ->where('course_id', $student_course_info->course_id)
                    ->where(function($q) use ($student_batch_id) {
                        $q->where('batch_id', $student_batch_id)
                          ->orWhere('batch_id', null);
                    })
                    ->where(function($y) use ($authId) {
                        $y->where('student_id', $authId)
                          ->orWhere('student_id', null);
                    })
                    ->latest()
                    ->get();

                foreach($student_notify as $key => $notify){
                    $notify->seen = EduNotifySeenHistory_User::valid()
                        ->where('assign_batch_class_id', $notify->assign_batch_class_id)
                        ->where('notify_id', $notify->id)
                        ->where('created_by', $authId)
                        ->first();
                }

                $view->with($data);
            }
        );
    }
}
