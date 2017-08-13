@extends('layouts.bootstrap-main')
@section('title', 'Register')

@section('additional_head')
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
	<script type="text/javascript" src="{{ secure_asset('js/auto-import.js') }}"></script>
@endsection

@section('content')
	  <h2>Auto-Import</h2>
	  @if ($points < 1)
	  	<div class="alert alert-warning">
	  		You don't have any points left.
			This will cause any pull requests on your Git to be ignored at the next auto-import.
			Please review someone else code to get some points.
		</div>
	  @endif
	  @foreach ($statuses as $status)
	  	<div class="panel panel-default">
			<div class="panel-heading" role="tab" id="{{$status['auto_import']->id}}">
				<h4 class="panel-title">
					@if ($status['auto_import']->is_active)
						<span class="label label-success pull-left">Active</span>
						<button class="btn btn-danger btn-xs pull-right" onclick="updateAutoImport(false,'{{$status['auto_import']->id}}')"><span class="glyphicon glyphicon-remove-sign"></span> Disable ?</button>
					@else
						<span class="label label-danger pull-right">Disabled</span>
						<button class="btn btn-danger btn-xs pull-right" onclick="updateAutoImport(true,'{{$status['auto_import']->id}}')"><span class="glyphicon glyphicon-ok"></span> Enable ?</button>
					@endif
					&nbsp;
					<a role="button" data-toggle="collapse" data-parent="#{{$status['auto_import']->id}}" href="#{{$status['auto_import']->id}}_result" aria-expanded="true" aria-controls="{{$status['auto_import']->id}}_result">
					{{$status['auto_import']->repository}}
					</a>
				</h4>
			</div>
			<div id="{{$status['auto_import']->id}}_result" class="panel-collapse collapse" role="tabpanel" aria-labelledby="{{$status['auto_import']->id}}">
				<div class="panel-body">
					<div class="list-group">
						@foreach ($status['results'] as $result)
						    @if ($result->is_success)
						    	<a class="list-group-item" target="_blank" href="/reviews/{{$result->request_id}}/view">Imported at <b>{{$result->created_at}}</b>
						      	<i>(Click to view on Inspicio)</i>
						      	<span class="label label-success pull-right">Imported</span>
						    @else
						    	<a class="list-group-item" disabled href="#">
						    	Attempted at <b>{{$result->created_at}}</b>
						    	<span class="label label-danger pull-right">Failed</span>
						      	<div class="alert alert-danger">{{$result->error}}</div>
						    @endif
						 </a>
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
@endsection