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
    	<div class="row">
            <a href="/reviews/{{$review->id}}/view" class="action-btn-orange btn btn-flat waves-effect waves-light"><i class="fa fa-info-circle left" aria-hidden="true"></i>More info</a>
    		<a href="/reviews/{{$review->id}}/view" class="btn btn-info waves-effect waves-light giants-orange"><i class="fa fa-pencil-square left" aria-hidden="true"></i>Edit</a>
    	</div>
   </div>
</div>
@endforeach
</div>
@endsection
