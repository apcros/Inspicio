@extends('layouts.bootstrap-main')
@section('title', 'Homepage')

@section('additional_head')
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
@endsection


@section('content')
	@if (isset($hot_reviews))
	<script type="text/javascript" src="{{ secure_asset('js/search.js') }}"></script>
<div>
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#hot_reviews" aria-controls="hot_reviews" role="tab" data-toggle="tab">Hot reviews</a></li>
    <li role="presentation"><a href="#latest_reviews" aria-controls="latest_reviews" role="tab" data-toggle="tab">Latest reviews</a></li>
    <li role="presentation"><a href="#search" aria-controls="search" role="tab" data-toggle="tab">Search</a></li>
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
    <div role="tabpanel" class="tab-pane" id="search">
    	<div class="panel panel-default">
    		<div class="panel-body">
    			<div class="input-group">
			    	<div class="input-group-addon">Keyword(s)</div>
			      	<input type="text" class="form-control" id="review-keywords" placeholder="Search">
			    </div>
			    <p>
				    <div class="form-group">
					    <select multiple="multiple" id="review-language" style="width: 100%">
				    		@foreach ($languages as $language)
				    			<option value="{{$language->id}}">{{$language->name}}</option>
				    		@endforeach
				    	</select>
				    </div>
			    </p>
			    <div class="checkbox">
				    <label>
				      <input type="checkbox" id="review-can-be-closed"> Include closed reviews ?
				    </label>
				</div>
	    		<button onclick="search()" class="btn btn-info">Search</button>
	    	</div>
    	</div>

    	<ul class="list-group" id="reviews-list">
		</ul>
  </div>
</div>
	@endif
@endsection