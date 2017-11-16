@extends('layouts.materialize-main')
@section('title',  $review->name .' - View Review Request')

@section('additional_head')
	<meta property="og:title" content="{{ $review->name }}">
	<meta property="og:type" content="article">
	<meta property="og:article:published_time" content="{{$review->created_at}}">
	<meta property="og:article:author" content="{{ $review->nickname }}">
	<meta property="og:url" content="{{env('APP_URL').'/reviews/'.$review->id.'/view'}}">
	<meta property="og:image" content="">
@endsection

@section('content')
<script type="text/javascript" src="{{ secure_asset('js/async-action-reviews.js') }}"></script>
<div class="card">
	<div class="card-content">
		<span class="card-title">{{$review->name}}</span>
            <div class="row">
	              <div class="col s6 m3">
	                  <a class="giants-orange-text" href="/members/{{$review->author_id}}/profile"><i class="fa fa-user left" aria-hidden="true"></i>{{$review->nickname}}</a>
	              </div>
	              <div class="col s6 m3">
	                  <i class="fa fa-code left" aria-hidden="true"></i>{{$review->language}}
	              </div>
	              <div class="col s6 m3">
	                  <i class="fa fa-calendar left" aria-hidden="true"></i>{{$review->created_at}}
	              </div>
	              <div class="col s6 m3">
	                  <i class="fa fa-users left" aria-hidden="true"></i>{{$followers}} follower/reviewer(s)
	              </div>
            </div>
            <div class="row">
            	<b>Description</b>
            <blockquote>{!! $review->description !!}</blockquote>
    </div>
    <div class="card-action">
		  	@if(session('user_id') && $review->status == 'open')
		  		@if (session('user_id') != $review->author_id)
			  		@if (isset($tracked))
			  			@if ($tracked->is_approved)
			  				<button class="btn btn-primary" disabled>Approved</button>
			  			@else
				  			<button onclick="approveReview('{{$review->id}}')" id="review-approve" class="btn btn-primary">Approve</button>
			  			@endif

			  			@if ($tracked->is_active)
			  				<button onclick="unfollowReview('{{$review->id}}')" id="review-follow" class="btn btn-danger">Unfollow this review</button>
			  			@else
			  				<button onclick="followReview('{{$review->id}}')" id="review-follow" class="btn btn-info waves-effect waves-light middle-red-purple">Follow this review</button>
			  			@endif
			  		@else
			  				<button onclick="approveReview('{{$review->id}}')" id="review-approve" class="btn btn-info waves-effect waves-light middle-red-purple disabled" disabled>Approve</button>
				  			<button onclick="followReview('{{$review->id}}')" id="review-follow" class="btn btn-info waves-effect waves-light middle-red-purple">Follow this review</button>
			  		@endif
			  	@endif
			@endif
			@if(session('user_id') == $review->author_id)
					@if ($review->status == 'open')
			  			<a onclick="closeReview('{{$review->id}}')" id="review-close-{{$review->id}}" class="btn btn-warning">Close</a>
			  			<a href="/reviews/{{$review->id}}/edit" id="review-edit-{{$review->id}}" class="btn btn-info">Edit</a>
			  		@else
			  			<a onclick="reopenReview('{{$review->id}}')" id="review-close-{{$review->id}}" class="btn btn-warning">Re-Open</a>
			  			<a disabled href="#" id="review-edit-{{$review->id}}" class="btn btn-info waves-effect waves-light middle-red-purple disabled">Edit</a>
			  		@endif
			@endif
			  		<a href="{{$review->url}}" target="_blank" class="btn btn-info waves-effect waves-light middle-red-purple">View</a>
    </div>
</div>
@endsection