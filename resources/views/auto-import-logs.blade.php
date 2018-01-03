@extends('layouts.materialize-main')
@section('title', 'Auto-import logs')

@section('additional_head')
	<script type="text/javascript" src="{{ secure_asset('js/auto-import.js') }}"></script>
@endsection

@section('content')
<div class="container">

	<div class="card-panel">
		<a href="../" class="btn-flat waves-effect waves-light"><i class="fa fa-arrow-left left" aria-hidden="true"></i>Back</a>
	@if ($setup->is_active)
		<div class="green-text">
			<h4>Logs for <b>{{$setup->repository}}</b></h4>
			<p><i class="fa fa-check left" aria-hidden="true"></i>Currently active</p>
	  		<button onclick="updateAutoImport(false,'{{$setup->id}}')" class="red btn waves-effect waves-light"><i class="fa fa-exclamation-triangle left" aria-hidden="true"></i><b>Disable</b></button>	
		</div>
	@else
		<div class="red-text">
			<h4>Logs for <b>{{$setup->repository}}</b></h4>
			<p><i class="fa fa-exclamation-triangle left" aria-hidden="true"></i>Currently disabled</p>
			<button onclick="updateAutoImport(true,'{{$setup->id}}')" class="green btn waves-effect waves-light"><i class="fa fa-check left" aria-hidden="true"></i><b>Enable</b></button>
		</div>
	@endif
	</div>
	  @foreach ($imports as $import)
	  	@if ($import->is_success)
			<div class="card">
				<div class="card-content">
			    	<span class="card-title green-text"><i class="fa fa-check left" aria-hidden="true"></i><b>{{$import->review_name}}</span>
			        <p>Imported with success at <b>{{$import->updated_at}} !</b></p>
				</div>
				<div class="card-action">
					<a href="{{$import->review_url}}" target="_blank" class="btn btn-info middle-red-purple waves-effect waves-light"><i class="fa fa-external-link left" aria-hidden="true"></i>View on Git</a>
 					<a href="/reviews/{{$import->review_id}}/view" target="_blank" class="btn btn-info giants-orange waves-effect waves-light"><i class="fa fa-info-circle left" aria-hidden="true"></i>View on Inspicio</a>
				</div>
			</div>
	  	@else
			<div class="card">
				<div class="card-content red-text">
			    	<span class="card-title"><i class="fa fa-exclamation-triangle left" aria-hidden="true"></i><b>Import failure</b> (ID : {{$import->id}})</b></span>
			        <p>Failed at <b>{{$import->created_at}}</p>
			        <p>{{$import->error}}</p>
				</div>
			</div>
	  	@endif
	  @endforeach
	  </div>
</div>
@endsection