@extends('teacher.layouts.default')

@section('content')
<!-- Page header -->
<div class="page-header">
    <div class="breadcrumb-line">
        <ul class="breadcrumb">
            <li><a href="{{route('teacher.home')}}"><i class="icon-home2 position-left"></i> Home</a></li>
            <li class="active">Dashboard</li>
        </ul>
    </div>
</div>
<!-- /page header -->

<!-- Content area -->
<div class="content">
    <div class="panel panel-flat">
        <div class="table-responsive">
            <table class="table text-nowrap data-list">
                <thead>
                    <tr>
                        <th class="col-md-3">Batch</th>
                        <th class="col-md-3">ClassName</th>
                        <th class="col-md-2">Assignment</th>
                        <th class="col-md-2">Quiz</th>
                        <th class="col-md-2 text-center" style="width: 20px;">Videos</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="active border-double">
                        <td colspan="4">Today's Class List</td>
                        <td class="text-right">
                            <span class="progress-meter" id="today-progress" data-progress="30"></span>
                        </td>
                    </tr>
                    @if (!empty($my_assigned_batches))
                        @foreach ($my_assigned_batches as $batch)
                        <tr>
                            <td>
                                <div class="media-left media-middle">
                                    <i class="icon-checkmark3 text-success"></i>
                                </div>
                                <div class="media-left">
                                    <div class="text-default text-semibold">{{$batch->batch_no}}</div>
                                </div>
                            </td>
                            <td>
                                <div class="media-left">
                                    <div class="text-default text-semibold">{{$batch->running_class->class_name}}</div>
                                    <div class="text-muted text-size-small">
                                        <span class="status-mark border-blue position-left"></span>
                                        {{ Helper::timeGia($batch->running_class->start_time) }} ({{ date("jS F, Y", strtotime($batch->running_class->start_date)) }})
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="media-left">
                                    <a href="{{route('teacher.batchstuAssignments.index', ['batch_class_id'=>$batch->running_class->id])}}" class="btn border-teal-400 text-teal btn-flat btn-rounded btn-icon btn-xs"><i class="icon-redo2"></i></a>
                                </div>
                                <div class="media-body">
                                    <div class="media-heading">
                                        <a href="{{route('teacher.batchstuAssignments.index', ['batch_class_id'=>$batch->running_class->id])}}" class="letter-icon-title">Go To Assignment</a>
                                    </div>
                                    <div class="text-muted text-size-small"><i class="icon-checkmark3 text-success text-size-mini position-left"></i>{{$batch->run_total_submitted_assignment}} Submitted</div>
                                </div>
                            </td>
                            <td>
                                <div class="media-left">
                                    <a href="{{route('teacher.classExamResult', ['batch_class_id'=>$batch->running_class->id])}}" class="btn border-teal-400 text-teal btn-flat btn-rounded btn-icon btn-xs"><i class="icon-redo2"></i></a>
                                </div>
                                <div class="media-body">
                                    <div class="media-heading">
                                        <a href="{{route('teacher.classExamResult', ['batch_class_id'=>$batch->running_class->id])}}" class="letter-icon-title">Go To Quiz</a>
                                    </div>
                                    <div class="text-muted text-size-small"><i class="icon-checkmark3 text-success text-size-mini position-left"></i>{{$batch->run_total_given_quiz}} Given</div>
                                </div>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-info btn-xs open-modal" modal-title="Class Videos" modal-type="show" modal-size="medium" modal-class="" selector="viewVideo" modal-link="{{route('teacher.classVideos', [$batch->running_class->class_id])}}"> View Videos </button>
                            </td>
                        </tr>
                        @endforeach
                    @else 
                        <tr>
                            <td colspan="5">No Data Found!</td>
                        </tr>
                    @endif

                    <tr class="active border-double">
                        <td colspan="4">Last Completed Class List</td>
                        <td class="text-right">
                            <span class="progress-meter" id="yesterday-progress" data-progress="65"></span>
                        </td>
                    </tr>
                    @if (!empty($my_assigned_batches))
                        @foreach ($my_assigned_batches as $batch)
                        <tr>
                            <td>
                                <div class="media-left media-middle">
                                    <i class="icon-checkmark3 text-success"></i>
                                </div>
                                <div class="media-left">
                                    <div class="text-default text-semibold">{{$batch->batch_no}}</div>
                                </div>
                            </td>
                            <td>
                                <div class="media-left">
                                    <div class="text-default text-semibold">{{$batch->completed_class->class_name}}</div>
                                    <div class="text-muted text-size-small">
                                        <span class="status-mark border-blue position-left"></span>
                                        {{ Helper::timeGia($batch->completed_class->start_time) }} ({{ date("jS F, Y", strtotime($batch->completed_class->start_date)) }})
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="media-left">
                                    <a href="{{route('teacher.batchstuAssignments.index', ['batch_class_id'=>$batch->completed_class->id])}}" class="btn border-teal-400 text-teal btn-flat btn-rounded btn-icon btn-xs"><i class="icon-redo2"></i></a>
                                </div>
                                <div class="media-body">
                                    <div class="media-heading">
                                        <a href="{{route('teacher.batchstuAssignments.index', ['batch_class_id'=>$batch->completed_class->id])}}" class="letter-icon-title">Go To Assignment</a>
                                    </div>
                                    <div class="text-muted text-size-small"><i class="icon-checkmark3 text-success text-size-mini position-left"></i>{{$batch->com_total_submitted_assignment}} Submitted</div>
                                </div>
                            </td>
                            <td>
                                <div class="media-left">
                                    <a href="{{route('teacher.classExamResult', ['batch_class_id'=>$batch->completed_class->id])}}" class="btn border-teal-400 text-teal btn-flat btn-rounded btn-icon btn-xs"><i class="icon-redo2"></i></a>
                                </div>
                                <div class="media-body">
                                    <div class="media-heading">
                                        <a href="{{route('teacher.classExamResult', ['batch_class_id'=>$batch->completed_class->id])}}" class="letter-icon-title">Go To Quiz</a>
                                    </div>
                                    <div class="text-muted text-size-small"><i class="icon-checkmark3 text-success text-size-mini position-left"></i>{{$batch->com_total_given_quiz}} Given</div>
                                </div>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-info btn-xs open-modal" modal-title="Class Videos" modal-type="show" modal-size="medium" modal-class="" selector="viewVideo" modal-link="{{route('teacher.classVideos', [$batch->completed_class->class_id])}}"> View Videos </button>
                            </td>
                        </tr>
                        @endforeach
                    @else 
                        <tr>
                            <td colspan="5">No Data Found!</td>
                        </tr>
                    @endif

                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- /content area -->
@endsection
