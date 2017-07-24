@extends('layouts.bootstrap-main')
@section('title', 'Register')

@section('content')
	  <h2>Bulk import result</h2>
	  @foreach ($results as $result)
	  	@if ($result['success'] == 1)
	  		<div class="alert alert-info">
	  			<h3>{{$result['title']}}<h3>
	  			<h4>Imported with success !</h4>
	  			<p>
	  				<a href="{{$result['url']}}" target="_blank" class="btn btn-info">View on {{$result['provider']}}</a>
	  				<a href="/reviews/{{$result['message']}}/view" target="_blank" class="btn btn-info">View on Inspicio</a>
	  			</p>
	  		</div>
	  	@else
	  		<div class="alert alert-danger">
	  			<h3>{{$result['title']}}<h3>
	  			<h4>An error ocurred while importing your PR</h4>
	  			<div class="well">
	  				{{$result['message']}}
	  			</div>
	  			<p>
	  				<a href="{{$result['url']}}" target="_blank" class="btn btn-info">View on {{$result['provider']}}</a>
	  			</p>
	  		</div>
	  	@endif

	  	
	  @endforeach
@endsection