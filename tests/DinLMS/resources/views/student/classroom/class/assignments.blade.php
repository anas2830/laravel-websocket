<div class="content">
    <input type="hidden" id="assign_batch_class_id" value="{{$assign_batch_class_id}}" />
    @if(count($assignments) > 0)
        @foreach ($assignments as $assignment)
            <div class="panel panel-white assignment-post-box">
                <div class="panel-heading">
                    <div class="">
                        <h5 class="panel-title">{{$assignment->class_name}} [ {{$assignment->title}} ] </h5>
                        <small class="display-block">{{$assignment->name}} || {{ date("jS F, Y", strtotime($assignment->start_date)) }}
                        </small>
                    </div>
                    <div class="heading-elements" id="answer_mark">
                        <span class="heading-text mr-10">{{ date("jS F, Y", strtotime($assignment->due_date))}} , {{Helper::timeGia($assignment->due_time)}}</span>
                        @if(!empty($assignment->submitted) && $assignment->submitted->late_submit == 0)
                            @if (!empty($assignment->teacherComment))
                                <span class="btn border-success text-success btn-flat btn-icon btn-rounded btn-sm" title="Assignment Submitted"> {{$assignment->submitted->mark}}/100</span>
                            @endif
                        @elseif(!empty($assignment->submitted) && $assignment->submitted->late_submit == 1)
                            @if (!empty($assignment->teacherComment))
                                <span class="btn border-success text-success btn-flat btn-icon btn-rounded btn-sm" title="Late Submitted">  {{$assignment->submitted->mark}}/100</span>
                            @endif
                        @else
                        <span class="btn border-danger text-danger btn-flat btn-icon btn-rounded btn-sm" title="Not Submit Yet"><i class="icon-cross2"></i></span>
                        @endif
                    </div>
                </div>

                <div id="errorMsgDiv" style="padding:5px 20px">
                    <div id="newsletterMsg"></div>
                </div>
        
                <div class="panel-body">
                    <p class="content-group">{!! $assignment->overview !!}</p>
                    @if(!empty($assignment->attachment->extention))
                    <p class="text-semibold">Given Attachments</p>
                    <div class="grid-demo">
                        <div class="row show-grid">
                            <div class="col-md-4">
                                <ul class="list-group border-left-info border-left-lg">
                                    <li class="list-group-item">
                                        <a href="javascript:window.open('{{url('uploads/assignment/teacherAttachment/'.$assignment->attachment->file_name)}}')" title="Click to Download">
                                            <h6 class="list-group-item-heading">
                                                <img src="{{ asset(Helper::getFileThumb($assignment->attachment->extention)) }}" alt="" height="35" width="40">
                                                {{$assignment->attachment->file_original_name}} 
                                                <span class="label bg-teal-400 pull-right">{{Helper::fileSizeConvert($assignment->attachment->size)}}</span>
                                            </h6>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(empty($assignment->submitted))
                        @if($assignment->completeStatus != 1 )
                            <form class="form-horizontal form-validate-jquery assignmentForm" id="form_{{$assignment->id}}" action="#" method="POST" enctype="multipart/form-data">
                                @csrf
                                <fieldset>
                                    <input type="hidden" value="{{$assignment->id}}" name="assignment_id">
                                    <legend class="text-bold"></legend>
                                    <div class="form-group">
                                        <div class="col-lg-12">
                                            <input type="file" class="file-input" name="attachment">
                                            <span class="help-block">Allow extensions: <code>jpg/jpeg</code> , <code>png</code>, <code>pdf</code> , <code>doc</code>, <code>docx</code> and  <code>zip</code>and  Allow Size: <code>5 MB</code> Only</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-lg-12">
                                            <input type="hidden" name="submit_type" value="1">
                                            <textarea class="form-control" placeholder="Add Assignment Url, You can add multiple Url and comment" name="comment" rows="5" cols="5"></textarea>
                                        </div>
                                    </div>
                                    <span class="input-group-btn">
                                        <button class="btn bg-teal" type="submit" class="submit_assignment">Submit</button>
                                    </span>
                                </fieldset>
                            </form>
                            @else 
                            <legend class="text-bold"></legend>
                            <p class="text-semibold text-center" style="color: red;">This Class Already Completed !</p>
                        @endif
                    @else 
                        <legend class="text-bold"></legend>
                        <p class="text-semibold">Submitted Attachment</p>
                        @if (!empty($assignment->submittedAttachment))
                            <div class="grid-demo">
                                <div class="row show-grid">
                                    <div class="col-md-4">
                                        <ul class="list-group border-left-info border-left-lg">
                                            <li class="list-group-item">
                                                <a href="javascript:window.open('{{url('uploads/assignment/studentAttachment/'.@$assignment->submittedAttachment->file_name)}}')" title="Click to Download">
                                                    <h6 class="list-group-item-heading">
                                                        <img src="{{ asset(Helper::getFileThumb(@$assignment->submittedAttachment->extention)) }}" alt="" height="35" width="40">
                                                        {{@$assignment->submittedAttachment->file_original_name}}
                                                        <span class="label bg-teal-400 pull-right">{{Helper::fileSizeConvert(@$assignment->submittedAttachment->size)}}</span>
                                                    </h6>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($assignment->completeStatus != 1 )
                        <form class="form-horizontal form-validate-jquery assignmentFormUpdate" id="form_{{$assignment->id}}" action="#" method="POST">
                            @csrf
                            <fieldset>
                                <input type="hidden" value="{{$assignment->id}}" name="assignment_id">
                                <legend class="text-bold"></legend>
                                <div class="form-group">
                                    <div class="col-lg-12">
                                        <input type="hidden" name="submit_type" value="2">
                                        <textarea class="form-control" placeholder="Add Assignment Url, You can add multiple Url and comment" name="comment" rows="5" cols="5">{!! $assignment->submitted->comment !!}</textarea>
                                    </div>
                                </div>
                                <span class="input-group-btn">
                                    <button class="btn bg-teal" type="submit" class="submit_assignment">Update</button>
                                </span>
                            </fieldset>
                        </form>
                        @else 
                            <div class="form-group">
                                <div class="col-lg-12">
                                    <p class="text-semibold"> {!! $assignment->submitted->comment !!} </p>
                                </div>
                            </div>
                            <legend class="text-bold"></legend>
                            <p class="text-semibold text-center" style="color: red;">This Class Already Completed.That's why update not available !</p>
                        @endif
                        
                    @endif

                    @if (!empty($assignment->teacherComment))
                        <legend class="text-bold"></legend>
                        <p class="content-group text-bold">Reviewed by {{@$assignment->teacherComment->teacher_name}}</p>
                        <p class="content-group">Comment: {!! @$assignment->teacherComment->comment !!}</p>
                        @if (!empty($assignment->teacherComment->file_name))
                            <p class="text-semibold">Given Attachments</p>
                            <div class="grid-demo">
                                <div class="row show-grid">
                                    <div class="col-md-4">
                                        <ul class="list-group border-left-info border-left-lg">
                                            <li class="list-group-item">
                                                <a href="javascript:window.open('{{url('uploads/assignment/teacherComment/'.@$assignment->teacherComment->file_name)}}')" title="Click to Download">
                                                    <h6 class="list-group-item-heading">
                                                        <img src="{{ asset('uploads/assignment/teacherComment/thumb/'.@$assignment->teacherComment->file_name) }}" alt="" height="35" width="40">
                                                        <span class="label bg-teal-400 pull-right">{{Helper::fileSizeConvert(@$assignment->teacherComment->size)}}</span>
                                                    </h6>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <div class="panel panel-white">
            <div class="panel-body">
                <h6 class="panel-title text-center">Assignment Not Found !!!</h6>
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

{{-- @push('javascript') --}}
<script type="text/javascript" src="{{ asset('backend/assets/js/pages/uploader_bootstrap.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function(){
        // setTimeout(function() {$('#newsletterMsg').hide()}, 4000);
    });

    $('.assignment-post-box').on('submit', '.assignmentForm', function(e) {
        e.preventDefault();
        var $form = $(this);
        var postData = new FormData(this);  
        $.ajax({
            url : "{{route('submitAssignment')}}",
            type: "POST",
            data: postData,
            cache:false,
            contentType: false,
            processData: false,
            success:function(data){
                var status = parseInt(data.status);
                if(status==1) {
                    $("input[name='comment']").val('');
                    $form.parent().parent().find('#newsletterMsg').html('<div class="alert alert-success fade in"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button> <i class="fa fa-adjust alert-icon"></i> '+data.messege+'</div>');

                    setTimeout(function() {
                        $('#newsletterMsg').hide();
                        $form.parent().parent().find('#errorMsgDiv').html('<div id="newsletterMsg"></div>');
                    }, 6000);

                    $('#newsletterMsg').on('click', '#close_icon', function() {
                        $form.parent().parent().find('#errorMsgDiv').html('<div id="newsletterMsg"></div>');
                    });

                    $form.parent().find('.assignmentForm').remove();  
                    var activeHref = $('#sub_menu .active a').attr('href');
                    if (activeHref == 'assignments') {
                        $('#sub_menu .active a').trigger('click');
                    }
                    // $form.hide();
                } else {
                    $("input[name='comment']").val('');
                    $('#newsletterMsg').html('<div class="alert alert-danger fade in"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button> <i class="fa fa-adjust alert-icon"></i> '+data.messege+'</div>');

                    setTimeout(function() {
                        $('#newsletterMsg').hide();
                        $form.parent().parent().find('#errorMsgDiv').html('<div id="newsletterMsg"></div>');
                    }, 6000);

                    $('#newsletterMsg').on('click', '#close_icon', function() {
                        $form.parent().parent().find('#errorMsgDiv').html('<div id="newsletterMsg"></div>');
                    });
                }
            }
        });
    });
    // Assignment Form Update
    $('.assignment-post-box').on('submit', '.assignmentFormUpdate', function(e) {
        e.preventDefault();
        var $form = $(this);
        var postData = new FormData(this);  
        $.ajax({
            url : "{{route('submitAssignment')}}",
            type: "POST",
            data: postData,
            cache:false,
            contentType: false,
            processData: false,
            success:function(data){
                var status = parseInt(data.status);
                if(status==1) {
                    $("input[name='comment']").val('');
                    $form.parent().parent().find('#newsletterMsg').html('<div class="alert alert-success fade in"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button> <i class="fa fa-adjust alert-icon"></i> '+data.messege+'</div>');

                    setTimeout(function() {
                        $('#newsletterMsg').hide();
                        $form.parent().parent().find('#errorMsgDiv').html('<div id="newsletterMsg"></div>');
                    }, 6000);

                    $('#newsletterMsg').on('click', '#close_icon', function() {
                        $form.parent().parent().find('#errorMsgDiv').html('<div id="newsletterMsg"></div>');
                    });

                    // $form.parent().find('.assignmentFormUpdate').remove();

                } else {
                    $("input[name='comment']").val('');
                    $('#newsletterMsg').html('<div class="alert alert-danger fade in"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button> <i class="fa fa-adjust alert-icon"></i> '+data.messege+'</div>');

                    setTimeout(function() {
                        $('#newsletterMsg').hide();
                        $form.parent().parent().find('#errorMsgDiv').html('<div id="newsletterMsg"></div>');
                    }, 6000);

                    $('#newsletterMsg').on('click', '#close_icon', function() {
                        $form.parent().parent().find('#errorMsgDiv').html('<div id="newsletterMsg"></div>');
                    });
                }
            }
        });
    });
    // For fist video thumb show End
</script>    
{{-- @endpush --}}
