@extends('layouts.bootstrap-main')
@section('title', 'Search code reviews')
@section('additional_head')
<meta name="description" content="A social hub for code reviews. Get your code reviewed !">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
<script type="text/javascript" src="{{ secure_asset('js/vuejs-utils.js') }}"></script>
<script type="text/javascript" src="{{ secure_asset('js/search.js') }}"></script>
@endsection
@section('content')
<ul class="nav nav-tabs" role="tablist">
	<li role="presentation"><a href="/">Latest reviews</a></li>
	<li role="presentation"><a href="/trending">Trending</a></li>
	<li role="presentation" class="active"><a href="/reviews/search">Search</a></li>
</ul>
<div class="tab-content">
	<div role="tabpanel" class="tab-pane active">
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
				<button onclick="search(1)" class="btn btn-info">Search</button>
			</div>
		</div>
		<ul class="list-group" id="reviews-list">
		</ul>
		<ul v-cloak class="list-group" id="reviews-search-result">
			@include('vuejs.search-reviews')
		</ul>
	</div></div>
	@endsection