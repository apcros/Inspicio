@extends('layouts.bootstrap-main')
@section('title', 'My Review Request')

@section('content')

<ul class="list-group">
	@foreach ($reviews as $review)
		<li class="list-group-item">
			<p><h4>{{ $review->name }}</h4>{{ $review->created_at }}</p>
			<p>
				<span class="label label-primary">{{$review->language}}</span>&nbsp;
				<span class="label label-primary">{{$review->repository}}</span>
			</p>
			@if ($review->status == 'closed')
				<div class="alert alert-warning"><b>This review is closed</b></div>
			@endif
			<p>
				<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
				  <div class="panel panel-default">
				    <div class="panel-heading" role="tab" id="followers-{{$review->id}}-heading">
				      <h4 class="panel-title">
				        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#followers-{{$review->id}}" aria-expanded="false" aria-controls="followers-{{$review->id}}">
				          Followers ({{count($followers[$review->id])}})
				        </a>
				      </h4>
				    </div>
				    <div id="followers-{{$review->id}}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="followers-{{$review->id}}-heading">
				      <div class="panel-body">
				      	<div class="list-group">
					      	@foreach ($followers[$review->id] as $follower)
					      		<a href="/members/{{$follower->id}}/profile" target="_blank" class="list-group-item">
					      			<b>{{$follower->nickname}}</b>
					      			@if ($follower->status == 'approved')
					      				<span class="label label-success pull-right">Approved !</span>
					      			@else
					      				<span class="label label-default pull-right">Pending approval</span>
					      			@endif
					      		</a>
					      	@endforeach
				      	</div>
				      </div>
				    </div>
				  </div>
				</div>
			</p>
			<a class="btn btn-info" href="/reviews/{{$review->id}}/view" target="_blank">View</a>
			@if ($review->status == 'open')
				<a onclick="closeReview('{{$review->id}}')" id="review-close-{{$review->id}}" class="btn btn-warning">Close</a>
			@endif
		</li>
	@endforeach
</ul>
<script type="text/javascript" src="{{ secure_asset('js/async-action-reviews.js') }}"></script>
@endsection
