@extends('layouts.bootstrap-main')
@section('title', 'Homepage')

@section('content')
	@if (isset($hot_reviews))
<div>
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#hot_reviews" aria-controls="hot_reviews" role="tab" data-toggle="tab">Hot reviews</a></li>
    <li role="presentation"><a href="#latest_reviews" aria-controls="latest_reviews" role="tab" data-toggle="tab">Latest reviews</a></li>
    <li role="presentation"><a href="#search" aria-controls="search" role="tab" data-toggle="tab" disabled>Search</a></li>
  </ul>
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="hot_reviews">
    @foreach ($hot_reviews as $review)
    	<div class="panel panel-default">
    		<div class="panel-body">
    			<h4>{{$review->name}}</h4> By <b>{{$review->author}}</b> - {{$review->created_at}}
    			<hr>
    			<span class="label label-primary">{{$review->language}}</span>
    			<span class="label label-primary">{{$review->followers}} Followers</span>
    		</div>
    		<div class="panel-footer">
    			<a href="/reviews/{{$review->id}}/view" class="btn btn-primary">See more...</a>
    		</div>
    	</div>
    @endforeach
    </div>
    <div role="tabpanel" class="tab-pane" id="latest_reviews">
    @foreach ($latest_reviews as $review)
    	<div class="panel panel-default">
    		<div class="panel-body">
    			<h4>{{$review->name}}</h4> By <b>{{$review->author}}</b> - {{$review->created_at}}
    			<hr>
    			<span class="label label-primary">{{$review->language}}</span>
    			<span class="label label-primary">{{$review->followers}} Followers</span>
    		</div>
    		<div class="panel-footer">
    			<a href="/reviews/{{$review->id}}/view" class="btn btn-primary">See more...</a>
    		</div>
    	</div>
    @endforeach
    </div>

  </div>
</div>
	@endif
@endsection