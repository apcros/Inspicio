@extends('layouts.bootstrap-main')
@section('title', 'View Review Request')

@section('content')
	<h3>Public profile for {{$user->nickname}}</h3>
		 <div class="panel panel-default">
		  <div class="panel-heading">
		    <h3 class="panel-title">General info</h3>
		  </div>
		  <div class="panel-body">
		  	<ul>
		  		<li><b>Email :</b> {{$user->email}}</li>
		  		<li><b>Name :</b> {{$user->name}}</li>
		  	</ul>
		  </div>
		</div>
	  	<div class="panel panel-default">
		  <div class="panel-heading">
		    <h3 class="panel-title">Skills</h3>
		  </div>
		  <div class="panel-body">
		  	<table class="table table-bordered">
			  	<tr>
			  		<th>Name</th>
			  		<th>Level</th>
			  	</tr>
		  	@foreach ($skills as $skill)
		  		<tr>
		  			<td>
		  			{{$skill->name}}
		  			@if ($skill->is_verified)
		  				<span class="badge">Verified</span>
		  			@endif
		  			</td>

		  			<td>
		  			@if ($skill->level == 1)
		  				Beginner/Junior
		  			@elseif ($skill->level == 2)
		  				Intermediate
		  			@else
		  				Advanced/Senior
		  			@endif
		  			</td>
		  		</tr>
		  	@endforeach
		  	</table>
		  </div>
		</div>
		<div class="panel panel-default">
		  <div class="panel-heading">
		    <h3 class="panel-title">Open reviews</h3>
		  </div>
		  <div class="panel-body">
		  	<table class="table table-bordered">
		  	@foreach ($reviews as $review)
		  		<tr>
		  			<td>{{$review->name}}</td>
		  			<td><a class="btn btn-info" href="/reviews/{{$review->id}}/view">View</a></td>
		  		</tr>
		  	@endforeach
		  	</table>
		  </div>
		</div>
@endsection
