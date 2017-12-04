@extends('layouts.materialize-main')
@section('title', 'Reviews you follow')

@section('content')
<div class="row">
      <ul class="tabs tabs-fixed-width">
        <li class="tab col s6"><a class="active" href="#pending">Pending reviews</a></li>
        <li class="tab col s6"><a href="#archived">Archived (Closed and/or Approved)</a></li>
      </ul>
</div>
<div class="container">
    <div id="pending" class="col s12">
    	@foreach ($reviews_unapproved as $review)
			<div class="card">
			    <div class="card-content">
			    	<span class="card-title">{{$review->name}}</span>
			    	<div class="row">
			    	@if ($review->status == 'closed')
			    		<p class="red-text"><i class="fa fa-lock left" aria-hidden="true"></i><b>Closed</b></p>
			    	@endif
			    	@if (true)
			    		<p class="green-text"><i class="fa fa-check left" aria-hidden="true"></i>Approved</p>
			    	@else
			    		<p class="giants-orange-text"><i class="fa fa-clock-o left" aria-hidden="true"></i>Pending approval</p>
			    	@endif
			    </div>
			           <div class="row">
			              <div class="col s6 m3">
			                  <a class="giants-orange-text" href="/members/{{$review->author_id}}/profile"><i class="fa fa-user left" aria-hidden="true"></i>{{$review->author_id}}</a>
			              </div>
			              <div class="col s6 m3">
			                  <i class="fa fa-code left" aria-hidden="true"></i>{{$review->language}}
			              </div>
			              <div class="col s6 m3">
			                  <i class="fa fa-calendar left" aria-hidden="true"></i>{{$review->created_at}}
			              </div>
			              <div class="col s6 m3">
			                  <i class="fa fa-users left" aria-hidden="true"></i>?? follower/reviewer(s)
			              </div>
			           </div>
			    </div>
			    <div class="card-action">
			            <a href="/reviews/{{$review->id}}/view" class="action-btn-orange btn btn-flat waves-effect waves-light"><i class="fa fa-info-circle left" aria-hidden="true"></i>More info</a>
			   </div>
			</div>
    	@endforeach
    </div>
    <div id="archived" class="col s12">
    </div>
</div>
@endsection
