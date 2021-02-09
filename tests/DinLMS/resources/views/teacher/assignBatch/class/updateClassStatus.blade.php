<form class="form-horizontal form-validate-jquery" action="{{route('teacher.updateClassStatus', [$class_id, $batch_id])}}" method="POST">
@csrf
<div class="panel panel-flat">
    <div class="panel-body" id="modal-container">
        <select class="select2 select-search col-lg-8" id="select_status" name="status" required="">
            <option value="">Select Status</option>
            <option value="1" @if($status == 1) selected="" @endif>Complete</option>
        	<option value="2" @if($status == 2) selected="" @endif>Running</option>
        </select>
    </div>
</div>
</form>
<script type="text/javascript">
	$("#select_status").select2({ dropdownParent: "#modal-container" });
</script>
