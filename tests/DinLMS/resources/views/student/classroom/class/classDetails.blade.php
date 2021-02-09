<!-- Simple panel -->
<div class="panel panel-white">
    <input type="hidden" id="assign_batch_class_id" value="{{$class_overview->id}}" />
    <div class="panel-heading">
        <h6 class="panel-title">{{@$class_overview->class_name}} Overview</h6>
    </div>

    <div class="panel-body">
        <p class="content-group">
            {!! @$class_overview->class_overview !!}
        </p>
    </div>
</div>
<!-- /simple panel -->