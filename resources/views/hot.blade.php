@extends('layouts.bootstrap-main')
@section('title', 'Trending code reviews')
@section('additional_head')
<meta name="description" content="A social hub for code reviews. Get your code reviewed !">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
@endsection
@section('content')
<ul class="nav nav-tabs" role="tablist">
	<li role="presentation"><a href="/">Latest reviews</a></li>
	<li role="presentation" class="active"><a href="/trending">Trending</a></li>
	<li role="presentation"><a href="/reviews/search">Search</a></li>
</ul>
@foreach ($reviews as $review)
<div class="tab-content">
	<div role="tabpanel" class="tab-pane active">
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
	</div></div>
	@endforeach
	{{$reviews->links()}}
	@endsection