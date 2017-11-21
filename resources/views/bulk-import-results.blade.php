@extends('layouts.materialize-main')
@section('title', 'Register')

@section('content')
	  <h2>Bulk import result</h2>
	  <div class="container">
	  @foreach ($results as $result)
	  	@if ($result['success'] == 1)
			<div class="card">
				<div class="card-content">
			    	<span class="card-title green-text"><i class="fa fa-check left" aria-hidden="true"></i><b>{{$result['title']}}</b></span>
			        <p>Imported with success !</p>
				</div>
				<div class="card-action">
					<a href="{{$result['url']}}" target="_blank" class="btn btn-info middle-red-purple waves-effect waves-light"><i class="fa fa-external-link left" aria-hidden="true"></i>View on {{$result['provider']}}</a>
 					<a href="/reviews/{{$result['message']}}/view" target="_blank" class="btn btn-info giants-orange waves-effect waves-light"><i class="fa fa-info-circle left" aria-hidden="true"></i>View on Inspicio</a>
				</div>
			</div>
	  	@else
			<div class="card">
				<div class="card-content red-text">
			    	<span class="card-title"><i class="fa fa-exclamation-triangle left" aria-hidden="true"></i><b>{{$result['title']}}</b></span>
			        <p>An error ocurred while importing your PR</p>
			        <p>{{$result['message']}}</p>
				</div>
				<div class="card-action">
					<a href="{{$result['url']}}" target="_blank" class="btn btn-info middle-red-purple waves-effect waves-light"><i class="fa fa-external-link left" aria-hidden="true"></i>View on {{$result['provider']}}</a>
				</div>
			</div>
	  	@endif

	  	
	  @endforeach
	  </div>
@endsection