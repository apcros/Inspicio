@extends('layouts.bootstrap-main')
@section('title', 'Register')

@section('additional_head')
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
@endsection

@section('content')
	  <h2>Auto-Import</h2>

	  @foreach ($statuses as $status)
	  	<div class="panel panel-default">
			<div class="panel-heading" role="tab" id="{{$status['auto_import']->id}}">
				<h4 class="panel-title">
					<a role="button" data-toggle="collapse" data-parent="#{{$status['auto_import']->id}}" href="#{{$status['auto_import']->id}}_result" aria-expanded="true" aria-controls="{{$status['auto_import']->id}}_result">
					{{$status['auto_import']->repository}}
					@if ($status['auto_import']->is_active)
						<span class="label label-success pull-right">Active</span>
					@else
						<span class="label label-danger pull-right">Disabled</span>
					@endif
					</a>
				</h4>
			</div>
			<div id="{{$status['auto_import']->id}}_result" class="panel-collapse collapse" role="tabpanel" aria-labelledby="{{$status['auto_import']->id}}">
				<div class="panel-body">
					<div class="list-group">
						@foreach ($status['results'] as $result)
							<b>{{$result->created_at}}</b>
						      			@if ($result->is_success)
						      				<span class="label label-success pull-right">Imported</span>
						      				<a href="/reviews/{{$result->request_id}}/view" target="_blank" class="list-group-item">View</a>
						      			@else
						      				<span class="label label-danger pull-right">Failed</span>
						      				<div class="alert alert-danger">
						      					{{$result->error}}
						      				</div>
						      			@endif
						@endforeach
					</div>
				</div>
			</div>
		</div>
	  @endforeach
	  <hr>
	  <form method="POST">
		<div class="form-group">
		{{ csrf_field() }}
	    <label for="repositories">Select your repository</label>
	    <select name="repositories[]" id="repositories" class="form-control" placeholder="Repositories available for auto-import" multiple='multiple'>
	    <option></option>
	    @foreach ($reposPerAccount as $repos)
	    	@foreach ($repos['repos'] as $repo)
	    		<option value="{{ $repo->name }},{{$repos['account_id']}}">{{ $repo->name }}</option>
	    	@endforeach
	    @endforeach
		</select>
	  </div>
	  <button class="btn btn-primary" type="submit">Add to auto-import</button>
	</form>
	<script type="text/javascript">
		$(document).ready(function(){
			$('#repositories').select2();
		});
	</script>
@endsection