<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="keywords" content="">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'LFWF Academy') }}</title>

    <!-- Favicon -->
    <link href="{{ asset('web/img/fav.png') }}" rel="shortcut icon" type="image/x-icon"/>
    

	<!-- Global stylesheets -->
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
	<link href="{{ asset('backend/assets/css/icons/icomoon/styles.css') }}" rel="stylesheet" type="text/css">
	<link href="{{ asset('backend/assets/css/minified/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
	<link href="{{ asset('backend/assets/css/minified/core.min.css') }}" rel="stylesheet" type="text/css">
	<link href="{{ asset('backend/assets/css/minified/components.min.css') }}" rel="stylesheet" type="text/css">
	<link href="{{ asset('backend/assets/css/minified/colors.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('backend/assets/css/practiceTime.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('backend/assets/summernote/summernote.css') }}" rel="stylesheet" type="text/css"/>
    <!-- /global stylesheets --> 
    <style>
        .add-new {
            color: #fff!important;
        }
        .add-new:hover {
            opacity: 1 !important;
        }
        .panel>.dataTables_wrapper .table-bordered {
            border: 1px solid #ddd;
        }
        .dataTables_length {
            margin: 20px 0 20px 20px;
        }
        .dataTables_filter {
            margin: 20px 0 20px 20px;
        }
        .dataTables_info {
            margin-bottom: 20px;
        }
        .dataTables_paginate {
            margin: 20px 0 20px 20px;
        }
        .action-icon {
            padding: 0px 10px 0 0;
        }

        .kv-fileinput-upload {
            display: none;
        }
    </style>
    @stack('styles')
</head>
<body class="navbar-top-md-xs sidebar-xs has-detached-left" >
    <div id="app">
        <!-- Main navbar -->
        <div class="navbar navbar-inverse navbar-fixed-top">
            <div class="navbar-header">
                <a class="navbar-brand" href="{{route('home')}}"><img src="{{ asset('backend/assets/images/logo_light.png') }}" alt=""></a>

                <ul class="nav navbar-nav pull-right visible-xs-block">
                    <li><a data-toggle="collapse" data-target="#navbar-mobile"><i class="icon-tree5"></i></a></li>
                </ul>
            </div>

            <div class="navbar-collapse collapse" id="navbar-mobile">
                <ul class="nav navbar-nav">
                    {{-- <li><a class="sidebar-control sidebar-main-toggle hidden-xs"><i class="icon-paragraph-justify3"></i></a></li> --}}
                    @if (request()->is('class'))
                    <li><a class="sidebar-control sidebar-detached-hide hidden-xs"><i class="icon-drag-left"></i></a></li>
                    @endif
                </ul>
                <ul class="nav navbar-nav">
                    <li class="{{ (request()->is('home')) ? 'active' : '' }}"><a href="{{route('home')}}"><i class="icon-home4 position-left"></i> <span>Dashboard</span></a></li>
                    <li class="{{ (request()->is('overview')) ? 'active' : '' }}"><a href="{{route('overview')}}">Overview</a></li>
                    <li class="{{ (request()->is('todayGoal')) ? 'active' : '' }}"><a href="{{route('todayGoal')}}">Today Goal</a></li> 
                    <li class="{{ (request()->is('class')) ? 'active' : '' }}"><a href="{{route('class')}}">Class</a></li>
                    <li class="{{ (request()->is('stdLiveClass*')) ? 'active' : '' }}"><a href="{{route('stdLiveClass')}}" class="joinAClass" id="joinLive">Live Class</a></li>
                    <li class="{{ (request()->is('takeSupport*')) ? 'active' : '' }}"><a href="{{route('takeSupport.index')}}">Support</a></li>
                    <li class="{{ (request()->is('requestClass*')) ? 'active' : '' }}"><a href="{{route('requestClass.index')}}">Request Class</a></li>
                    <li class=""><a href="#">Improve Score</a></li>
                    {{-- <li class=""><a href="#">About LFWF</a></li> --}}
                </ul>

                <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="icon-bubbles4"></i>
                            <span class="visible-xs-inline-block position-right">Messages</span>
                            @php
                                $unseenMsgCount = 0;
                                $seenHistory = 0;
                                $total_notify = count($student_notify);
                            @endphp
                            <span id="unseenMsg" class="badge bg-warning-400">{{$unseenMsgCount}}</span>
                        </a>
                        
                        <div class="dropdown-menu dropdown-content width-350">
                            <div class="dropdown-content-heading">
                                Messages
                            </div>
    
                            <ul class="media-list dropdown-content-body">
                                @foreach ($student_notify as $notify)
                                    @if (empty($notify->seen))
                                        @php $unseenMsgCount++; @endphp
                                        <li class="media">
                                            <div class="media-left">
												<a href="{{url($notify->notify_link)}}" class="btn border-teal-400 text-teal btn-flat btn-rounded btn-icon btn-xs notifyLink" data-id={{$notify->id}}><i class="icon-redo2"></i></a>
											</div>
            
                                            <div class="media-body"> 
                                                <a href="{{url($notify->notify_link)}}" class="media-heading notifyLink" data-id={{$notify->id}}>
                                                    <span class="text-semibold">{{ Helper::getAuthorName(2,$notify->created_by) }}</span>
                                                    <span class="media-annotation pull-right">{{$notify->notify_date}} {{Helper::timeGia($notify->notify_time) }}</span>
                                                </a>
                                                <span class="text-muted">{!! $notify->notify_title !!}</span>
                                            </div>
                                        </li>
                                    @else
                                        @php $seenHistory++; @endphp
                                        <li class="media">
                                            <p id="emptyNotifiy"></p>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
    
                            <div class="dropdown-content-footer">
                                <a href="#" data-popup="tooltip"><i class="icon-menu display-block"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="dropdown dropdown-user">
                        <a class="dropdown-toggle" data-toggle="dropdown">
                            <img src="{{ asset('backend/assets/images/image.png') }}" alt="">
                            <span>{{ $userInfo->name }}</span>
                            <i class="caret"></i>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-right">
                            <li><a href="{{route('profile')}}"><i class="icon-user-plus"></i> My profile</a></li>
                            <li class="divider"></li>
                            <li><a href="{{ route('logout') }}" onclick="event.preventDefault();
                                document.getElementById('logout-form').submit();"><i class="icon-switch2"></i> Logout</a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <!-- /main navbar -->
        
        <!-- Page container -->
        <div class="page-container">

            <!-- Page content -->
            <div class="page-content">

                <!-- Main content -->
                <div class="content-wrapper">
                    @if(!empty(@$student_course_info))
                        <div id="timerItem" title="Move Anywhere Just Drag &amp; Drop">
                            <!-- Innter item content -->
                            <div id="timer" class="lfwf-timer">
                                <div class="clock-wrapper lfwf-clock-wrap">
                                    <span class="hours">{{$hour}}</span>
                                    <span class="dots">:</span>
                                    <span class="minutes">{{$minute}}</span>
                                    <span class="dots">:</span>
                                    <span class="seconds">{{$seconds}}</span>
                                    <div class="buttons-wrapper lfwf-button-wrap">

                                        <div class="stage filter-contrast" id="filterContrast"><div class="dot-shuttle"></div></div>
                                        <div class="start-practice" id="startPractice"> </div>
                                        @if($seconds > 0)
                                            <button class="btn lfwf-toggle-btn" id="resume-timer" data-id={{$userInfo->id}}>Start</button>
                                        @else
                                            <button class="btn lfwf-toggle-btn" id="start-cronometer" style="color: black">Start</button>
                                            <button class="btn lfwf-toggle-btn" id="resume-timer" data-id={{$userInfo->id}}>Start</button>
                                        @endif
                                        <button class="btn lfwf-toggle-btn" id="stop-timer" data-id={{$userInfo->id}}>Stop</button>
                                        <!-- Please do it on toggle, if click on Start, it will be chnage to Stop -->
                                        {{-- <button class="btn lfwf-toggle-btn" id="resume-timer" data-id="138">Start</button>
                                        <button class="btn lfwf-toggle-btn" id="stop-timer" data-id="138">Stop</button> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @yield('content')

                </div>
                <!-- /main content -->
                
            </div>
            <!-- /page content -->
        </div>
        <!-- /page container -->
    </div>

    <!-- Load Facebook SDK for JavaScript -->
    <div id="fb-root"></div>
    <script>
        window.fbAsyncInit = function() {
        FB.init({
            xfbml            : true,
            version          : 'v9.0'
        });
        };

        (function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s); js.id = id;
            js.src = 'https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js';
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    </script>

    <!-- Your Chat Plugin code -->
    <div class="fb-customerchat"
        attribution=setup_tool
        page_id="112941143881682"
        logged_in_greeting="Hi! How can we help you? if you have any issue or need help you can message..."
        logged_out_greeting="Hi! How can we help you? if you have any issue or need help you can message...">
    </div>
    
	<!-- Core JS files -->
	<script type="text/javascript" src="{{ asset('backend/assets/js/plugins/loaders/pace.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('backend/assets/js/core/libraries/jquery.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('backend/assets/js/core/libraries/bootstrap.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('backend/assets/js/plugins/loaders/blockui.min.js') }}"></script>
	<!-- /core JS files -->

	<!-- Theme JS files -->
    <script type="text/javascript" src="{{ asset('backend/assets/js/plugins/tables/datatables/datatables.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('backend/assets/summernote/summernote.min.js') }}"></script>
    
    <!-- Stricky Class menu scrollbar -->
    @if (request()->is('class'))
    <script type="text/javascript" src="{{ asset('backend/assets/js/plugins/ui/nicescroll.min.js') }}"></script>
    @endif
    <!-- Horizontal Navbar JS files -->
	<script type="text/javascript" src="{{ asset('backend/assets/js/plugins/ui/drilldown.js') }}"></script>
    
    
    <!-- Sweet Alert JS files -->
    <script type="text/javascript" src="{{ asset('backend/assets/js/plugins/notifications/sweet_alert.min.js') }}"></script>

    <!-- Form JS files -->
    <script type="text/javascript" src="{{ asset('backend/assets/js/plugins/forms/validation/validate.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('backend/assets/js/plugins/forms/selects/bootstrap_multiselect.js') }}"></script>
    <script type="text/javascript" src="{{ asset('backend/assets/js/plugins/forms/inputs/touchspin.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('backend/assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('backend/assets/js/plugins/forms/selects/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('backend/assets/js/plugins/forms/styling/switch.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('backend/assets/js/plugins/forms/styling/switchery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('backend/assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
    <!-- Form JS files -->
    
    <!-- Dashboard JS files -->
    {{-- <script type="text/javascript" src="{{ asset('backend/assets/js/plugins/visualization/d3/d3.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('backend/assets/js/plugins/visualization/d3/d3_tooltip.js') }}"></script>
	<script type="text/javascript" src="{{ asset('backend/assets/js/plugins/forms/styling/switchery.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('backend/assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('backend/assets/js/plugins/forms/selects/bootstrap_multiselect.js') }}"></script>
	<script type="text/javascript" src="{{ asset('backend/assets/js/plugins/ui/moment/moment.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('backend/assets/js/plugins/pickers/daterangepicker.js') }}"></script> --}}
    <!-- Dashboard JS files -->

    <!-- Uploader JS files -->
    <script type="text/javascript" src="{{ asset('backend/assets/js/plugins/uploaders/fileinput.min.js') }}"></script>

    @if (!request()->is('changeProfile'))
    <!-- Chart JS files -->
	{{-- <script type="text/javascript" src="{{ asset('backend/assets/js/plugins/visualization/echarts/echarts.js') }}"></script> --}}
    @endif
	
    <script type="text/javascript" src="{{ asset('backend/assets/js/core/app.js') }}"></script>
	{{-- <script type="text/javascript" src="{{ asset('backend/assets/js/pages/dashboard.js') }}"></script> --}}
    
    <!-- Datatable JS files -->
    <script type="text/javascript" src="{{ asset('backend/assets/js/pages/datatables_advanced.js') }}"></script>

    <!-- Form Validation JS files -->
    <script type="text/javascript" src="{{ asset('backend/assets/js/pages/form_validation.js') }}"></script>

    <!-- Select2 JS files -->
    <script type="text/javascript" src="{{ asset('backend/assets/js/pages/form_select2.js') }}"></script>

    <!-- Uploader JS files -->
    <script type="text/javascript" src="{{ asset('backend/assets/js/pages/uploader_bootstrap.js') }}"></script>
    
    <!-- Stricky Class menu scrollbar -->
    @if (request()->is('class'))
	<script type="text/javascript" src="{{ asset('backend/assets/js/sticky/sidebar_detached_sticky_custom.js') }}"></script>
    @endif
    
    <!-- /Custom JS files -->
    <script type="text/javascript" src="{{ asset('backend/assets/js/custom_frame.js') }}"></script>    

    <!-- Per Page JS files -->
    @stack('javascript')
    <!-- /Per Page JS files -->

    <script type="text/javascript">
    // jillur drag //
        var dragItem = document.querySelector("#timerItem");
        var container = document.querySelector("#app");

        var active = false;
        var currentX;
        var currentY;
        var initialX;
        var initialY;
        var xOffset = 0;
        var yOffset = 0;

        container.addEventListener("touchstart", dragStart, false);
        container.addEventListener("touchend", dragEnd, false);
        container.addEventListener("touchmove", drag, false);

        container.addEventListener("mousedown", dragStart, false);
        container.addEventListener("mouseup", dragEnd, false);
        container.addEventListener("mousemove", drag, false);

        function dragStart(e) {
        if (e.type === "touchstart") {
            initialX = e.touches[0].clientX - xOffset;
            initialY = e.touches[0].clientY - yOffset;
        } else {
            initialX = e.clientX - xOffset;
            initialY = e.clientY - yOffset;
        }

        if (e.target === dragItem) {
            active = true;
        }
        }

        function dragEnd(e) {
        initialX = currentX;
        initialY = currentY;

        active = false;
        }

        function drag(e) {
        if (active) {
        
            e.preventDefault();
        
            if (e.type === "touchmove") {
            currentX = e.touches[0].clientX - initialX;
            currentY = e.touches[0].clientY - initialY;
            } else {
            currentX = e.clientX - initialX;
            currentY = e.clientY - initialY;
            }

            xOffset = currentX;
            yOffset = currentY;

            setTranslate(currentX, currentY, dragItem);
        }
        }

        function setTranslate(xPos, yPos, el) {
        el.style.transform = "translate3d(" + xPos + "px, " + yPos + "px, 0)";
        }

    // end jillur drag //

    $(document).ready(function(){
        // Unseen Message count
        var unseenMsgCount = {{$unseenMsgCount}};
        $('#unseenMsg').text(unseenMsgCount);
        var total_notify = {{$total_notify}};
        var seenHistory = {{$seenHistory}};

        console.log(total_notify);
        console.log(seenHistory);

        if(total_notify <= seenHistory){
            alert('soman');
            console.log('log soman');
            $('#emptyNotifiy').text('You Have No Notifications !!');
        }else{
            alert('not soman');
            console.log('not soman');
        }

        // notify link ajax
        $('.notifyLink').on('click', function(e){
            e.preventDefault;
            var notify_id = $(this).attr('data-id');
            $.ajax
            ({ 
                url : "{{route('notifySeen')}}",
                headers: {
                    'X-CSRF-Token': '{{ csrf_token() }}',
                },
                data: {"notify_id":notify_id},
                type: 'post',
                success: function(response)
                {   
                    console.log(response);
                }
            });
        });
        // end notify link ajax

        $('button#stop-timer').fadeOut(100);
        $('button#resume-timer').fadeOut(100);
        $('#filterContrast').fadeOut(100); //New
        $('#startPractice').fadeIn(100); //New

        const measure = $('select#measure')
        const ammount = $('input#num')
        const timer = $('#timer')
        const s = $(timer).find('.seconds')
        const m = $(timer).find('.minutes')
        const h = $(timer).find('.hours')

        var seconds = {{$seconds}}
        var minutes = {{$minute}}
        var hours = {{$hour}}

        var interval = 0;
        var clockType = 'cronometer';

        if( seconds > 0 ){
            pauseClock();
        } 

        $('button#start-cronometer').on('click', function(){
            $(this).hide();
            $('button#stop-timer').fadeIn(100);
            $('#filterContrast').fadeIn(100); //New
            $('#startPractice').fadeOut(100); //New
            clockType = 'cronometer'
            startClock();
        });

        $('button#stop-timer').on('click', function(e) {
            e.preventDefault();
            $(this).hide();
            var id = $(this).attr("data-id");
            $.ajax
            ({ 
                url : "{{route('parcticeTimeUpdate')}}",
                headers: {
                    'X-CSRF-Token': '{{ csrf_token() }}',
                },
                data: {"hours":hours,"minutes":minutes,"seconds":seconds,"type":1}, //type 1 = stop,type 2 = resu
                type: 'post',
                success: function()
                {
                    
                }
            });

            $('#start-cronometer').hide();
            $('#filterContrast').hide(); //New
            $('#resume-timer').fadeIn(100);
            $('#startPractice').fadeIn(100); //New
            
            pauseClock();
            
        });

        $('button#resume-timer').on('click', function(e) {

            e.preventDefault();
            $(this).hide();
            $('button#stop-timer').fadeIn(100);
            $('#filterContrast').fadeIn(100);
            var id = $(this).attr("data-id");
            $.ajax
            ({ 
                url : "{{route('parcticeTimeUpdate')}}",
                headers: {
                    'X-CSRF-Token': '{{ csrf_token() }}',
                },
                data: {"hours":hours,"minutes":minutes,"seconds":seconds,"type":2},  //type 1 = stop,type 2 = resume
                type: 'post',
                success: function()
                {
                    
                }
            });

            $('button#resume-timer').fadeOut(100);
            $('#filterContrast').fadeIn(); //New
            $('#startPractice').fadeOut(100); //New
            switch (clockType) {
                case 'cronometer':
                    cronometer()
                    break
            default:
                break;
        }
        });

        //run after 1 minutes
        // setInterval(function () {
        //     $.ajax
        //     ({ 
        //         url : "{{route('parcticeTimeUpdate')}}",
        //         headers: {
        //             'X-CSRF-Token': '{{ csrf_token() }}',
        //         },
        //         data: {"hours":hours,"minutes":minutes,"seconds":seconds,"type":3},  //type 1 = stop,type 2 = resume, 3=auto
        //         type: 'post',
        //         success: function()
        //         {
                    
        //         }
        //     });
        // }, 60000); // Execute somethingElse() every 1 minutes.

        function pad(d) {
            return (d < 10) ? '0' + d.toString() : d.toString()
        }

        function startClock() {
            hasStarted = false
            hasEnded = false

            switch ($(measure).val()) {
                case 's':
                    if ($(ammount).val() > 3599) {
                        let hou = Math.floor($(ammount).val() / 3600)
                        hours = hou
                        let min = Math.floor(($(ammount).val() - (hou * 3600)) / 60)
                        minutes = min;
                        let sec = ($(ammount).val() - (hou * 3600)) - (min * 60)
                        seconds = sec
                    }
                    else if ($(ammount).val() > 59) {
                        let min = Math.floor($(ammount).val() / 60)
                        minutes = min
                        let sec = $(ammount).val() - (min * 60)
                        seconds = sec
                    }
                    else {
                        seconds = $(ammount).val()
                    }
                    break
                case 'm':
                    if ($(ammount).val() > 59) {
                        let hou = Math.floor($(ammount).val() / 60)
                        hours = hou
                        let min = $(ammount).val() - (hou * 60)
                        minutes = min
                    }
                    else {
                        minutes = $(ammount).val()
                    }
                    break
                case 'h':
                    hours = $(ammount).val()
                    break
                default:
                    break
            }

            refreshClock()

            $('.input-wrapper').slideUp(350)
            setTimeout(function(){
                $('#timer').fadeIn(350)
                $('#stop-timer').fadeIn(350)

            }, 350)

            switch (clockType) {
                case 'cronometer':
                    cronometer()
                    break
                default:
                    break;
            }
        }

        function pauseClock() {
        clear(interval)
        $('#resume-timer').fadeIn()
        // $('#reset-timer').fadeIn()
        }

        var hasStarted = false
        var hasEnded = false
        if (hours == 0 && minutes == 0 && seconds == 0 && hasStarted == true) {
            hasEnded = true
        }

        function cronometer() {
            hasStarted = true
            interval = setInterval(() => {
                if (seconds < 59) {
                    seconds++
                    refreshClock()
                }
                else if (seconds == 59) {
                    minutes++
                    seconds = 0
                    refreshClock()
                }

                if (minutes == 60) {
                    hours++
                    minutes = 0
                    seconds = 0
                    refreshClock()
                }

            }, 1000)
        }

        function refreshClock() {
            $(s).text(pad(seconds))
            $(m).text(pad(minutes))
            if (hours < 0) {
                $(s).text('00')
                $(m).text('00')
                $(h).text('00')
            } else {
                $(h).text(pad(hours))
            }
        }

        function clear(intervalID) {
            clearInterval(intervalID)
            console.log('cleared the interval called ' + intervalID)
        }

        // end countdown timer
        });
    </script>

</body>
</html>
