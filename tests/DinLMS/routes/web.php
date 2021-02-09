<?php

use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth/login');
});

Auth::routes(['verify' => true, 'register' => false]);
Route::group(['middleware' => 'auth'], function (){
    Route::get('home', [App\Http\Controllers\Student\MasterController::class, 'home'])->name('home');
    Route::get('profile', [App\Http\Controllers\Student\ProfileController::class, 'index'])->name('profile');
    Route::get('changeProfile', [App\Http\Controllers\Student\ProfileController::class, 'changeProfile'])->name('changeProfile');
    Route::post('changeProfileUpdate', [App\Http\Controllers\Student\ProfileController::class, 'changeProfileUpdate'])->name('changeProfileUpdate');
    Route::put('profileUpdate/{id}', [App\Http\Controllers\Student\ProfileController::class, 'updateProfile'])->name('profileUpdate');
    Route::put('profilePassUpdate/{id}', [App\Http\Controllers\Student\ProfileController::class, 'updatePassword'])->name('profilePassUpdate');
    Route::post('notifySeen', [App\Http\Controllers\Student\MasterController::class, 'notifySeen'])->name('notifySeen');

    //practice time
    Route::post('parcticeTimeUpdate', [App\Http\Controllers\Student\ProfileController::class, 'updatePracticeTime'])->name('parcticeTimeUpdate');
    // CLASSROOM
    Route::get('overview', [App\Http\Controllers\Student\ClassroomController::class, 'overview'])->name('overview');
    Route::get('todayGoal', [App\Http\Controllers\Student\ClassroomController::class, 'todayGoal'])->name('todayGoal');
    // Class Menu
    Route::get('class', [App\Http\Controllers\Student\ClassroomController::class, 'classIndex'])->name('class');
    Route::get('classDetails', [App\Http\Controllers\Student\ClassroomController::class, 'classDetails'])->name('classDetails');
    Route::get('assignments', [App\Http\Controllers\Student\ClassroomController::class, 'assignments'])->name('assignments');
    Route::post('submitAssignment', [App\Http\Controllers\Student\ClassroomController::class, 'submitAssignment'])->name('submitAssignment');
    Route::get('activities', [App\Http\Controllers\Student\ClassroomController::class, 'activities'])->name('activities');
    Route::post('updateVideoWatchTime', [App\Http\Controllers\Student\ClassroomController::class, 'updateVideoWatchTime'])->name('updateVideoWatchTime');
    
    Route::get('quiz', [App\Http\Controllers\Student\ClassroomController::class, 'quiz'])->name('quiz');                                        
    Route::get('classExamRunning/{batch_class_id}', [App\Http\Controllers\Student\ClassExamController::class, 'classExam'])->name('classExamRunning');
    Route::post('classExamSubmit', [App\Http\Controllers\Student\ClassExamController::class, 'classExamSubmit'])->name('classExamSubmit');
    // end class menu
    Route::resource('takeSupport', App\Http\Controllers\Student\SupportController::class);
    // CLASS REQUEST
    Route::resource('requestClass', App\Http\Controllers\Student\ClassRequestController::class);
    //liveClass
    Route::get('stdLiveClass', [App\Http\Controllers\Student\StdLiveClassController::class, 'stdLiveClass'])->name('stdLiveClass');
});


Route::group(['prefix' => 'provider', 'as'=>'provider.'], function (){

    //Login & Logout
    Route::get('/', ['as'=>'login', function (){ return redirect()->route('provider.login');}]);
    Route::get('login', [App\Http\Controllers\Provider\MasterController::class, 'getLogin'])->name('login');
    Route::post('login', [App\Http\Controllers\Provider\MasterController::class, 'postLogin']);
    Route::post('logout', [App\Http\Controllers\Provider\MasterController::class, 'logout'])->name('logout');

    Route::group(['middleware' => 'providerAuth'], function (){
        Route::get('home', [App\Http\Controllers\Provider\MasterController::class, 'home'])->name('home');
        Route::get('profile', [App\Http\Controllers\Provider\ProfileController::class, 'index'])->name('profile');
        Route::put('profileUpdate/{id}', [App\Http\Controllers\Provider\ProfileController::class, 'updateProfile'])->name('profileUpdate');
        Route::put('profilePassUpdate/{id}', [App\Http\Controllers\Provider\ProfileController::class, 'updatePassword'])->name('profilePassUpdate');

        Route::resource('widget', App\Http\Controllers\Provider\StudentWidgetController::class);
        Route::resource('notification', App\Http\Controllers\Provider\StudentNotificationController::class);

        Route::resource('supCategory', App\Http\Controllers\Provider\SupportCategoryController::class);

        Route::resource('student', App\Http\Controllers\Provider\StudentController::class);
        Route::resource('teacher', App\Http\Controllers\Provider\TeacherController::class);
        Route::resource('support', App\Http\Controllers\Provider\SupportController::class);
        Route::get('traineeUserLogin', [App\Http\Controllers\Provider\StudentController::class, 'traineeUserLogin'])->name('traineeUserLogin');

        Route::resource('course', App\Http\Controllers\Provider\CourseController::class);
        Route::resource('courseAddClass', App\Http\Controllers\Provider\ClassController::class);
        Route::resource('courseAssignmentArchive', App\Http\Controllers\Provider\ArchiveAssignmentController::class);
        Route::resource('courseQuestionArchive', App\Http\Controllers\Provider\ArchiveQuestionController::class);
        Route::resource('batch', App\Http\Controllers\Provider\AssignBatchController::class);
        //Update Schedule
        Route::get('updateSchedule/{id}', [App\Http\Controllers\Provider\AssignBatchController::class, 'schedule'])->name('updateSchedule');
        Route::post('updateSchedule/{id}', [App\Http\Controllers\Provider\AssignBatchController::class, 'updateSchedule'])->name('updateSchedule');
        // Assign Teacher
        Route::get('assignTeacher/{id}', [App\Http\Controllers\Provider\AssignBatchController::class, 'assignTeacher'])->name('assignTeacher');
        Route::post('assignTeacher/{id}', [App\Http\Controllers\Provider\AssignBatchController::class, 'updateTeacher'])->name('assignTeacher');
        //BATCH COMPLETE
        Route::get('batchComplete/{id}', [App\Http\Controllers\Provider\AssignBatchController::class, 'batchComplete'])->name('batchComplete');
        Route::post('batchCompleteAction/{id}', [App\Http\Controllers\Provider\AssignBatchController::class, 'batchCompleteAction'])->name('batchCompleteAction');
        //Add Assign Batch Class
        Route::get('assignBatchList', [App\Http\Controllers\Provider\AssignBatchController::class, 'assignBatchList'])->name('assignBatchList');
        Route::resource('batchAddClass', App\Http\Controllers\Provider\BatchAddClassController::class);
        // Show and Update Remark of Attendant Student
        Route::get('batchShowAttendence', [App\Http\Controllers\Provider\BatchAddClassController::class, 'showAttendence'])->name('batchShowAttendence');
        Route::get('batchAttendenceRemark', [App\Http\Controllers\Provider\BatchAddClassController::class, 'attendenceRemark'])->name('batchAttendenceRemark');
        Route::post('batchSaveAttendenceRemark', [App\Http\Controllers\Provider\BatchAddClassController::class, 'saveAttendenceRemark'])->name('batchSaveAttendenceRemark');
        
        Route::resource('assignStudent', App\Http\Controllers\Provider\AssignStudentController::class);

        // student Progress
        Route::get('stdProgress', [App\Http\Controllers\Provider\StudentProgressController::class, 'stdProgress'])->name('stdProgress');
        Route::post('saveStdProgress', [App\Http\Controllers\Provider\StudentProgressController::class, 'saveStdProgress'])->name('saveStdProgress');

        //Analysis
        Route::get('analysisActivity', [App\Http\Controllers\Provider\AnalysisActivityController::class, 'index'])->name('analysisActivity');
        Route::get('analysisBatchStudents', [App\Http\Controllers\Provider\AnalysisActivityController::class, 'batchStudents'])->name('analysisBatchStudents');
    });
});

Route::group(['prefix' => 'teacher', 'as'=>'teacher.'], function (){

    //Login & Logout  
    Route::get('/', ['as'=>'login', function (){ return redirect()->route('teacher.login');}]);
    Route::get('login', [App\Http\Controllers\Teacher\MasterController::class, 'getLogin'])->name('login');
    Route::post('login', [App\Http\Controllers\Teacher\MasterController::class, 'postLogin']);
    Route::post('logout', [App\Http\Controllers\Teacher\MasterController::class, 'logout'])->name('logout');

    Route::group(['middleware' => 'teacherAuth'], function (){
        Route::get('home', [App\Http\Controllers\Teacher\MasterController::class, 'home'])->name('home');
        Route::get('dashboard', [App\Http\Controllers\Teacher\MasterController::class, 'dashboard'])->name('dashboard');
        Route::get('classVideos/{class_id}', [App\Http\Controllers\Teacher\MasterController::class, 'classVideos'])->name('classVideos');
        Route::get('profile', [App\Http\Controllers\Teacher\ProfileController::class, 'index'])->name('profile');

        Route::resource('widget', App\Http\Controllers\Teacher\StuedentWidgetController::class);
        // Teacher Zoom Account
        Route::get('teacherZoomAcc', [App\Http\Controllers\Teacher\TZoomAccountController::class, 'teacherZoomAcc'])->name('teacherZoomAcc');
        Route::post('saveTeacherZoomAcc', [App\Http\Controllers\Teacher\TZoomAccountController::class, 'saveTeacherZoomAcc'])->name('saveTeacherZoomAcc');


        Route::put('profileUpdate/{id}', [App\Http\Controllers\Teacher\ProfileController::class, 'updateProfile'])->name('profileUpdate');
        Route::put('profilePassUpdate/{id}', [App\Http\Controllers\Teacher\ProfileController::class, 'updatePassword'])->name('profilePassUpdate');
    
        //Assigned Batch   
        Route::get('assignedBatch', [App\Http\Controllers\Teacher\AssignedBatchController::class, 'assignedBatch'])->name('assignedBatch');
        Route::resource('assignedBatchClass', App\Http\Controllers\Teacher\AssignedClassController::class);
        
        Route::get('classStatus/{class_id}/{batch_id}', [App\Http\Controllers\Teacher\AssignedClassController::class, 'classStatus'])->name('classStatus');
        Route::post('updateClassStatus/{class_id}/{batch_id}', [App\Http\Controllers\Teacher\AssignedClassController::class, 'updateClassStatus'])->name('updateClassStatus');
        // Take Attendance
        Route::get('batchstuAttendence', [App\Http\Controllers\Teacher\StudentAttendenceController::class, 'index'])->name('batchstuAttendence');
        Route::get('batchstuClassList/{batch_id}', [App\Http\Controllers\Teacher\StudentAttendenceController::class, 'classList'])->name('batchstuClassList');
        Route::get('batchstuGiveAttendence', [App\Http\Controllers\Teacher\StudentAttendenceController::class, 'giveAttendence'])->name('batchstuGiveAttendence');
        Route::post('batchstuSaveAttendence', [App\Http\Controllers\Teacher\StudentAttendenceController::class, 'saveAttendence'])->name('batchstuSaveAttendence');
        // show attendence 
        Route::get('showAttendence', [App\Http\Controllers\Teacher\StudentAttendenceController::class, 'showAttendence'])->name('showAttendence');
        
        // Give Assignment
        Route::resource('batchstuAssignments', App\Http\Controllers\Teacher\AssignmentController::class);
        
        // Assignment 
        Route::get('batchstuStudentList', [App\Http\Controllers\Teacher\AssignmentSubmitStudentController::class, 'index'])->name('batchstuStudentList');
        Route::get('batchstuStudentGiveMark', [App\Http\Controllers\Teacher\AssignmentSubmitStudentController::class, 'batchstuStudentGiveMark'])->name('batchstuStudentGiveMark');
        Route::post('batchstuStudentGiveMarkSave', [App\Http\Controllers\Teacher\AssignmentSubmitStudentController::class, 'batchstuStudentGiveMarkSave'])->name('batchstuStudentGiveMarkSave');
        Route::get('viewSubmissionDetails', [App\Http\Controllers\Teacher\AssignmentSubmitStudentController::class, 'viewSubmissionDetails'])->name('viewSubmissionDetails');

        //Class wise Exam
        Route::get('classExamBatch', [App\Http\Controllers\Teacher\ClassExamController::class, 'index'])->name('classExamBatch');
        Route::get('classExamBatchClassList/{batch_id}', [App\Http\Controllers\Teacher\ClassExamController::class, 'batchClassList'])->name('classExamBatchClassList');
        Route::get('classExamConfig', [App\Http\Controllers\Teacher\ClassExamController::class, 'examConfig'])->name('classExamConfig');
        Route::post('classExamConfig', [App\Http\Controllers\Teacher\ClassExamController::class, 'saveExamConfig'])->name('classExamConfig');

        //class exam result
        Route::get('classExamResult', [App\Http\Controllers\Teacher\ClassExamController::class, 'examResult'])->name('classExamResult');
        Route::get('studentResult', [App\Http\Controllers\Teacher\ClassExamController::class, 'examResultShow'])->name('studentResult');

        // REQUESTED CLASS
        Route::get('stdRequestClass', [App\Http\Controllers\Teacher\StdClassRequestController::class, 'index'])->name('stdRequestClass');
        Route::get('stdRequestClassFeeback', [App\Http\Controllers\Teacher\StdClassRequestController::class, 'requestFeedback'])->name('stdRequestClassFeeback');
        Route::put('stdRequestClassFeebackAction', [App\Http\Controllers\Teacher\StdClassRequestController::class, 'requestFeebackAction'])->name('stdRequestClassFeebackAction');
        
    });
});

Route::group(['prefix' => 'support', 'as'=>'support.'], function (){

    //Login & Logout
    Route::get('/', ['as'=>'login', function (){ return redirect()->route('support.login');}]);
    Route::get('login', [App\Http\Controllers\Support\MasterController::class, 'getLogin'])->name('login');
    Route::post('login', [App\Http\Controllers\Support\MasterController::class, 'postLogin']);
    Route::post('logout', [App\Http\Controllers\Support\MasterController::class, 'logout'])->name('logout');

    Route::group(['middleware' => 'supportAuth'], function (){
        Route::get('home', [App\Http\Controllers\Support\MasterController::class, 'home'])->name('home');
        Route::get('profile', [App\Http\Controllers\Support\ProfileController::class, 'index'])->name('profile');

        // Support Zoom Account
        Route::get('supportZoomAcc', [App\Http\Controllers\Support\SZoomAccountController::class, 'supportZoomAcc'])->name('supportZoomAcc');
        Route::post('saveSupportZoomAcc', [App\Http\Controllers\Support\SZoomAccountController::class, 'saveSupportZoomAcc'])->name('saveSupportZoomAcc');

        Route::get('stdRequest', [App\Http\Controllers\Support\StdRequestController::class, 'index'])->name('stdRequest');

        Route::get('stdRequestSchedule', [App\Http\Controllers\Support\StdRequestController::class, 'stdRequestSchedule'])->name('stdRequestSchedule');
        Route::post('stdRequestScheduleAction', [App\Http\Controllers\Support\StdRequestController::class, 'stdRequestScheduleAction'])->name('stdRequestScheduleAction');
    });
});
