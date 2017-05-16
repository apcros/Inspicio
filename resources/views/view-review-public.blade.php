@extends('layouts.bootstrap-main')
@section('title', 'View Review Request')

@section('content')
	  	<div class="panel panel-default">
		  <div class="panel-heading">
		    <h3 class="panel-title">{{ $review->name }} <span class="badge">{{$review->language}}</span></h3>
		  </div>
		  <div class="panel-body">
		  	<i>Created at : {{$review->created_at}}, Last updated : {{$review->updated_at}}</i>
		  	<hr>
		    <b>Description :</b>
		    <p>{{$review->description}}</p>
		    <hr>
		    Created by <a href="/members/{{$review->author_id}}/profile">__name__</a>
		    <span class="badge">0 Reviewers</span>
		  </div>
		  <div class="panel-footer">
		  		@if (session('user_id') != $review->author_id)

			  		@if (isset($tracked))
			  			@if ($tracked->status == 'approved')
			  				<button class="btn btn-primary" disabled>Approved</button>
			  			@else
				  			<form class="pull-left" method="POST" action="/reviews/{{$review->id}}/approve">
				  				<button class="btn btn-primary">Approve</button>{{csrf_field()}}
				  			</form>
			  			@endif

			  		@else
			  			<form class="pull-left" method="POST" action="/reviews/{{$review->id}}/track">
				  			<button class="btn btn-info">Follow this review</button>{{csrf_field()}}
				  		</form>
			  		@endif

			  		<a href="{{$review->url}}" target="_blank" class="btn btn-info">View</a>
			  	@endif
			  </div>
		</div>

@endsection
