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
<script type="text/javascript" src="{{ secure_asset('js/vuejs-utils.js') }}"></script>
<script type="text/javascript" src="{{ secure_asset('js/async-action-reviews.js') }}"></script>
<input type="hidden" id="review-id" value="{{$review->id}}"/>
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
	              <div class="col s12 m3">
	                  <i class="fa fa-calendar left" aria-hidden="true"></i>{{$review->created_at}}
	              </div>
	              <div class="col s12 m3">
	                  <i class="fa fa-users left" aria-hidden="true"></i>{{$followers}} follower(s)
	              </div>
            </div>
            <div class="row">
            	<b>Description</b>
            <blockquote>{!! $review->description !!}</blockquote>
    </div>
    <div class="card-action" v-cloak id="review-actions">
    	<div class="row center-align">
    		@include('vuejs.review-action')
    	</div>
    </div>
</div>
<div id="confirm_modal_vue">
	@include('vuejs.modal-confirm')
</div>
@endsection