@extends('layouts.materialize-main')
@section('title', 'Search code reviews')
@section('additional_head')
<meta name="description" content="A social hub for code reviews. Get your code reviewed !">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
<script type="text/javascript" src="{{ secure_asset('js/vuejs-utils.js') }}"></script>
<script type="text/javascript" src="{{ secure_asset('js/search.js') }}"></script>
@endsection
@section('content')
<nav class="middle-red-purple">
    <div class="nav-wrapper">
        <ul>
            <li><a href="/">Latest reviews</a></li>
            <li><a href="/trending">Trending</a></li>
            <li class="active"><a href="/reviews/search">Search</a></li>
        </ul>
    </div>
</nav>
<div class="card">
	<div class="card-content">
		<span class="card-title">Search reviews</span>
        <div class="row">
			<div class="input-field col s12 m6">
				<input placeholder="Eg: Frontend, Database..etc" id="review-keywords" type="text" class="validate">
				<label for="review-keywords">Keyword(s)</label>
			</div>
			<div class="input-field col s12 m6">
				<select multiple="multiple" id="review-language" style="width: 100%">
					@foreach ($languages as $language)
						<option value="{{$language->id}}">{{$language->name}}</option>
					@endforeach
				</select>
			</div>
		</div>
		<div class="row">
				<input type="checkbox" id="review-can-be-closed" />
				<label for="review-can-be-closed">Include closed reviews ?</label>
		</div>
	</div>
	<div class="card-action">
		<button id="start-search-btn" onclick="search(1)" class="btn btn-info waves-effect waves-light middle-red-purple"><i class="fa fa-search left" aria-hidden="true"></i>Search</button>
	</div>
</div>
<div v-cloak class="container" id="reviews-search-result">
	@include('vuejs.search-reviews')
</div>
@endsection