@extends('layouts.default')

@push('styles')
<link href="{{ asset('web_graph/line_chart/css/lineStyle.css') }}" rel="stylesheet" type="text/css"/>
@endpush
@section('content')
<!-- Page header -->
<div class="page-header">
    <div class="breadcrumb-line">
        <ul class="breadcrumb">
            <li class="active"><a href="{{route('home')}}"><i class="icon-home2 position-left"></i> Home</a></li>
        </ul>
    </div>
</div>
<!-- /page header -->

<!-- Content area -->
<div class="content">
    <div class="row">
        <div class="col-lg-3 col-md-6">
            
            <div class="thumbnail lfwf-p-thumbnail">
                <div class="thumb">
                    @if( !empty($userInfo->image) || $userInfo->image != Null)
                        <img src="{{ asset('uploads/studentProfile/'.$userInfo->image)}}" alt="{{$userInfo->image}}" style="max-height: 260px!important;">
                    @else
                        <img src="{{ asset('backend/assets/images/placeholder.jpg') }}" alt="" style="max-height: 260px!important;">
                    @endif
                </div>
            
                <div class="caption text-center">
                    <h6 class="text-semibold no-margin">{{$userInfo->name}}<small class="display-block"></small></h6>
                    <p class="course-name"><strong> Course: </strong>{{@$running_course_info->course_name}}</p>
                    <ul class="icons-list mt-15">
                        <li><span class="batch_no">{{@$assigned_batch_info->batch_no}}</span></li>
                        <li><a href="{{@$assigned_batch_info->batch_fb_url}}" target="_blank" data-popup="tooltip" title="Facebook Group" data-container="body"><i class="icon-facebook2"></i></a></li>
                        {{-- <li><a href="#" data-popup="tooltip" title="Google Drive" data-container="body"><i class="icon-google-drive"></i></a></li> --}}
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-9 col-md-6">
            <div class="panel dashboard-graph">
                <figure class="highcharts-figure">
                    <div id="container"></div>
                  </figure>
            </div>
        </div>
    </div>
    {{-- <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="panel">
                <div class="panel-body text-center">
                    <div class="progress content-group-sm">
                        <div class="progress-bar progress-bar-success progress-bar-striped active" style="width: 55%">
                            <span>55% Complete (success)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    <h4 class="text-center content-group">
        Class Overview & Support
    </h4>
    @if (isset($student_course_info))
            
        <div class="row">
            <div class="col-md-4">
                <div class="panel panel-body">
                    <div class="media">
                        <div class="media-left">
                            <a href="#"><i class="icon-file-text2 text-danger-400 icon-2x no-edge-top mt-5"></i></a>
                        </div>

                        <div class="media-body">
                            <h6 class="media-heading text-semibold"><a href="@if(!empty($completed_class)) {{route('class', ['class_id'=>$completed_class->id] )}} @else # @endif" class="text-default">Last completed class</a></h6>
                            @if(!empty($completed_class))
                                {{$completed_class->class_name}}
                            @else
                            <span class="label label-danger">Class not found</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel panel-body">
                    <div class="media">
                        <div class="media-left">
                            <a href="#"><i class="icon-file-text2 text-success-400 icon-2x no-edge-top mt-5"></i></a>
                        </div>

                        <div class="media-body">
                            <h6 class="media-heading text-semibold"><a href="@if(!empty($upcomming_class)) {{route('class', ['class_id'=>$upcomming_class->id] )}} @else # @endif" class="text-default">Upcomming Class</a></h6>
                            @if(!empty($upcomming_class))
                                {{$upcomming_class->class_name}}
                            @else
                            <span class="label label-danger">Class not found</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel panel-body">
                    <div class="media">
                        <div class="media-left">
                            <a href="#"><i class="icon-file-xml text-info icon-2x no-edge-top mt-5"></i></a>
                        </div>

                        <div class="media-body">
                            <h6 class="media-heading text-semibold"><a href="#" class="text-default">Help & Support</a></h6>
                            <span style="color:red">Support is Comming Soon</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <h4 class="text-center content-group">
            All Personal News
        </h4>
        <hr>
        @if (count($all_personal_news) > 0)
            <!-- Info blocks -->
            <div class="row">
                @foreach ($all_personal_news as $key => $widget)
                <div class="col-md-4">
                    <div class="panel">
                        <div class="panel-body text-center">
                            <div class="icon-object border-success-400 text-success"><i class="icon-book"></i></div>
                            <h5 class="text-semibold">{{$widget['title']}}</h5><hr>
                            <p class="mb-15">{!! $widget['overview'] !!}</p>
                            {{-- <a href="#" class="btn bg-success-400">Browse articles</a> --}}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else 
            <div class="row">
                <div class="col-md-12">
                    <div class="panel-group panel-group-control content-group-lg" id="accordion-control">
                        <div class="panel panel-white">
                            <div class="panel-heading">
                                <h6 class="panel-title text-center">Widget Data Not Found !!!</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif 

        <h4 class="text-center content-group">
            All News By Teacher
        </h4>
        <hr>
        @if (count($all_teacher_news) > 0)
            <!-- Info blocks -->
            <div class="row">
                @foreach ($all_teacher_news as $key => $widget)
                <div class="col-md-4">
                    <div class="panel">
                        <div class="panel-body text-center">
                            <div class="icon-object border-success-400 text-success"><i class="icon-book"></i></div>
                            <h5 class="text-semibold">{{ $widget['title'] }}</h5><hr>
                            <p class="mb-15">{!! $widget['overview'] !!}</p>
                            {{-- <a href="#" class="btn bg-success-400">Browse articles</a> --}}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else 
            <div class="row">
                <div class="col-md-12">
                    <div class="panel-group panel-group-control content-group-lg" id="accordion-control">
                        <div class="panel panel-white">
                            <div class="panel-heading">
                                <h6 class="panel-title text-center">Widget Data Not Found !!!</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif 

        <h4 class="text-center content-group">
            All Latest By Authority
        </h4>
        <hr>
        @if (count($all_provider_news) > 0)
            <!-- Info blocks -->
            <div class="row">
                @foreach ($all_provider_news as $key => $widget)
                <div class="col-md-4">
                    <div class="panel">
                        <div class="panel-body text-center">
                            <div class="icon-object border-success-400 text-success"><i class="icon-book"></i></div>
                            <h5 class="text-semibold">{{ $widget['title'] }}</h5><hr>
                            <p class="mb-15">{!! $widget['overview'] !!}</p>
                            {{-- <a href="#" class="btn bg-success-400">Browse articles</a> --}}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else 
            <div class="row">
                <div class="col-md-12">
                    <div class="panel-group panel-group-control content-group-lg" id="accordion-control">
                        <div class="panel panel-white">
                            <div class="panel-heading">
                                <h6 class="panel-title text-center">Widget Data Not Found !!!</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif 
        <!-- /info blocks -->
    @else
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-body border-top-warning text-center">
                    <h6 class="no-margin text-semibold">No Course Found !!!</h6>
                    <p class="content-group-sm text-muted">Please Enroll our Course!</p>
                </div>
            </div>
        </div>
    @endif
    <!-- Footer -->
    <div class="footer text-muted">
        &copy; {{date('Y')}}. <a href="#">Developed</a> by <a href="#" target="_blank">DevsSquad IT Solutions</a>
    </div>
    <!-- /footer -->
</div>
<!-- /content area -->
@endsection

@push('javascript')
<script src="{{ asset('web_graph/line_chart/js/highcharts.js') }}"></script>
<script src="{{ asset('web_graph/line_chart/js/data.js') }}"></script>
<script src="{{ asset('web_graph/line_chart/js/exporting.js') }}"></script>
<script src="{{ asset('web_graph/line_chart/js/export-data.js') }}"></script>
<script src="{{ asset('web_graph/line_chart/js/accessibility.js') }}"></script>

<script type="text/javascript">

</script>
<!-- Line Chart -->
<script>

var arrayFromPHP = <?php echo json_encode($all_assign_classes) ?>;
var catArray = [];
var averagePer = [];

$.each(arrayFromPHP, function (i, elem){
    catArray.push(elem.std_class_name);
    // var total_average_value = elem.std_class_practiceTime+elem.std_class_attend+elem.std_class_mark+elem.std_class_video+elem.std_class_assignment+elem.std_class_exam;

    pr_std_class_practiceTime = parseInt(elem.std_class_practiceTime) > 100 ? 100 : parseInt(elem.std_class_practiceTime);
    pr_std_class_attend = parseInt(elem.std_class_attend) > 100 ? 100 : parseInt(elem.std_class_attend);
    pr_std_class_mark = parseInt(elem.std_class_mark) > 100 ? 100 : parseInt(elem.std_class_mark);
    pr_std_class_video = parseInt(elem.std_class_video) > 100 ? 100 : parseInt(elem.std_class_video);
    pr_std_class_assignment = parseInt(elem.std_class_assignment) > 100 ? 100 : parseInt(elem.std_class_assignment);
    pr_std_class_exam = parseInt(elem.std_class_exam) > 100 ? 100 : parseInt(elem.std_class_exam);
    var total_average_value = pr_std_class_practiceTime + pr_std_class_attend + pr_std_class_mark + pr_std_class_video + pr_std_class_assignment + pr_std_class_exam;
    
    averagePer.push(parseInt( total_average_value/6));
});


 Highcharts.chart('container', {
  chart: {
    type: 'line',
    scrollablePlotArea: {
      minWidth: 600,
      scrollPositionX: 0
    }
  },
  title: {
    text: '',
    align: 'left'
  },
  subtitle: {
    text: '',
    align: 'left'
  },
  xAxis: {
    categories: catArray,
    crosshair: true
   },
  yAxis: {
    min: 0,
    max:100,
    title: {
      text: 'Mark In (%)'
    },
    minorGridLineWidth: 0,
    gridLineWidth: 0,
    alternateGridColor: null,
    plotBands: [{ // Red Zone
      from: 0,
      to: 40,
      color: 'rgba(244, 67, 54,.4)',
      label: {
        text: 'Red Zone',
        style: {
          color: '#f44336'
        }
      }
    }, { // Blue Zone
      from: 41,
      to: 70,
      color: 'rgba(33, 150, 243,.2)',
      label: {
        text: 'Blue Zone',
        style: {
          color: '#4CAF50'
        }
      }
    }, { // Green Zone
      from: 71,
      to: 99,
      color: 'rgba(76, 176, 81,.4)',
      label: {
        text: 'Green Zone',
        style: {
          color: '#2196F3'
        }
      }
    }]
  },
  tooltip: {
    valueSuffix: '%'
  },
  plotOptions: {
    spline: {
      lineWidth: 4,
      states: {
        hover: {
          lineWidth: 5
        }
      },
      marker: {
        enabled: false
      },
      pointInterval: 3600000, // one hour
      pointStart: 0
    }
  },
  series: [{
    name: 'Performance',
    data: averagePer
    // data: [50, 71, 10, 12, 14, 17, 13, 14, 21]

  }],
  navigation: {
    menuItemStyle: {
      fontSize: '10px'
    }
  }
});
</script>
@endpush

