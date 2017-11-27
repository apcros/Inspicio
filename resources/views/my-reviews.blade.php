@extends('layouts.materialize-main')
@section('title', 'My reviews')

@section('content')
<div class="container">
@if (count($reviews) == 0)
	<div class="card-panel giants-orange-text">
		<h4>Oh noes, you don't have any reviews !</h4>
		<div class="divider"></div>
		<p>Don't panic, you can create one easily, either from scratch or from an existing Git pull request</p>
		<a class="btn btn-info middle-red-purple waves-effect waves-light" href="/reviews/create"><i class="fa fa-plus-square-o left" aria-hidden="true"></i>Create/Import code review requests</a>
		<p>Alternatively, if you want to import several in a single action, you can use the bulk import feature : </p>
		<a class="btn btn-info middle-red-purple waves-effect waves-light" href="/reviews/bulk-import"><i class="fa fa-cubes left" aria-hidden="true"></i>Bulk import code review requests</a>
	</div>
@endif

@foreach ($reviews as $review)
<div class="card">
    <div class="card-content">
    	@if ($review->status == 'closed')
    		 <span class="card-title red-text"><i class="fa fa-lock left" aria-hidden="true"></i>{{$review->name}} (Closed)</span>
    	@else
    		 <span class="card-title">{{$review->name}}</span>
    	@endif
        <div class="row">
            <div class="col s6">
                <i class="fa fa-code left" aria-hidden="true"></i>{{$review->language}}
            </div>
            <div class="col s6">
                <i class="fa fa-calendar left" aria-hidden="true"></i>{{$review->created_at}}
            </div>
        </div>
        <div class="row">
      	@if (count($followers[$review->id]))
         <ul class="collapsible" data-collapsible="accordion">
		    <li>
		      <div class="collapsible-header"><i class="fa fa-users left" aria-hidden="true"></i>{{count($followers[$review->id])}} follower(s)</div>
		      <div class="collapsible-body">
		      	
		      	  <div class="collection">
		      	 			@foreach ($followers[$review->id] as $follower)
					      		<a href="/members/{{$follower->id}}/profile" target="_blank" class="collection-item black-text">{{$follower->nickname}}
					      			@if ($follower->is_approved)
					      				<div class="green-text right">Approved</div>
					      			@else
					      				<div class="blue-text right"><i class="fa fa-question-circle left" aria-hidden="true"></i><b>Pending approval</b></div>
					      			@endif
					      		</a>
					      	@endforeach
			        
			      </div>
		      </div>
		    </li>
		 </ul>
		 @else
		 	<div class="regalia-text"><b>No followers yet</b></div>
		 @endif
		</div>
    </div>
    <div class="card-action">
            <a href="/reviews/{{$review->id}}/view" class="action-btn-orange btn btn-flat waves-effect waves-light"><i class="fa fa-info-circle left" aria-hidden="true"></i>More info</a>
    		<a href="/reviews/{{$review->id}}/view" class="btn btn-info waves-effect waves-light giants-orange"><i class="fa fa-pencil-square left" aria-hidden="true"></i>Edit</a>
   </div>
</div>
@endforeach

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
					      			@if ($follower->is_approved)
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
				<a href="/reviews/{{$review->id}}/edit" id="review-edit-{{$review->id}}" class="btn btn-info">Edit</a>
			@else 
				<a onclick="reopenReview('{{$review->id}}')" id="review-close-{{$review->id}}" class="btn btn-warning">Re-Open</a>
				<a disabled href="#" id="review-edit-{{$review->id}}" class="btn btn-info">Edit</a>
			@endif
		</li>
	@endforeach
</ul>
<script type="text/javascript" src="{{ secure_asset('js/async-action-reviews.js') }}"></script>
</div>
@endsection
