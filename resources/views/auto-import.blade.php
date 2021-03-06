@extends('layouts.materialize-main')
@section('title', 'Auto-Import setup')

@section('additional_head')
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
	<script type="text/javascript" src="{{ secure_asset('js/auto-import.js') }}"></script>
@endsection

@section('content')
<div class="container">
	  <h2>Auto-Import</h2>
	  @if ($points < 1)
	  	<div class="card-panel yellow">
	  		You don't have any points left.
			This will cause any pull requests on your Git to be ignored at the next auto-import.
			Please review someone else code to get some points.
		</div>
	  @endif
	  	  	<div class="card">
	  		<div class="card-content">
	  			<span class="card-title">Auto imports setup</span>
	  			<ul class="collection">
	  @foreach ($auto_imports as $auto_import)
	  				<li class="collection-item">
	  					<h5>{{$auto_import->repository}}</h5>
	  					<div class="row">
	  						<div class="row col s12 m3">
	  					@if ($auto_import->is_active)
	  						<button onclick="updateAutoImport(false,'{{$auto_import->id}}')" class="col s12 green btn waves-effect waves-light tooltipped" data-position="top" data-delay="50" data-tooltip="Click to disable"><i class="fa fa-check left" aria-hidden="true"></i><b>Active</b></button>
	  					@else
	  						<button onclick="updateAutoImport(true,'{{$auto_import->id}}')" class="col s12 red btn waves-effect waves-light tooltipped" data-position="top" data-delay="50" data-tooltip="Click to enable"><i class="fa fa-exclamation-triangle left" aria-hidden="true"></i><b>Inactive</b></button>
	  					@endif
	  						</div>
	  						<div class="row col s12 m3">
	  					    	<a href="/reviews/auto-import/{{$auto_import->id}}/logs" class="col s12 btn btn-info middle-red-purple waves-effect waves-light"><i class="fa fa-file-text left" aria-hidden="true"></i>Logs</a>
	  						</div>
	  					</div>
	  				</li>
	  @endforeach
	 	</ul>
	</div>
	</div>
	<form method="POST">
		<div class="card">
			<div class="card-content">
				<span class="card-title">Add to auto-import</span>
					{{ csrf_field() }}
				    <label for="repositories">Select your repository</label>
				    <select style="width: 100%" name="repositories[]" id="repositories" class="form-control" placeholder="Repositories available for auto-import" multiple='multiple'>
				    <option></option>
				    @foreach ($reposPerAccount as $repos)
				    	@foreach ($repos['repos'] as $repo)
				    		<option value="{{ $repo->name }},{{$repos['account_id']}}">{{ $repo->name }}</option>
				    	@endforeach
				    @endforeach
					</select>
			</div>
			<div class="card-action">
				<button type="submit" class="btn btn-info giants-orange waves-effect"><i class="fa fa-cloud-download left" aria-hidden="true"></i>Add to auto-import</button>
			</div>
		</div>
	</form>
</div>
@endsection