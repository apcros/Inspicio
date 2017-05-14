@extends('layouts.bootstrap-main')
@section('title', 'View Review Request')

@section('content')
	  	<div class="panel panel-default">
		  <div class="panel-heading">
		    <h3 class="panel-title">{{ $review->name }} <span class="badge">{{$review->language}}</span></h3>
		  </div>
		  <div class="panel-body">
		  	<i>Created at : {{$review->created_at}}, Last updated : {{$review->updated_at}}</i>
		  	<hr>
		    <b>Description :</b>
		    <p>{{$review->description}}</p>
		    <hr>
		    Created by <a href="/members/{{$review->author_id}}/profile">__name__</a>
		    <span class="badge">0 Reviewers</span>
		  </div>
		  <div class="panel-footer">
		  		<a href="#" onclick="alert('not implemented yet')" class="btn btn-info">Follow this review</a> <!-- TODO : Dynamic button, if follow, then it's an approve button!-->
		  		<a href="{{$review->url}}" target="_blank" class="btn btn-info">View</a>
		  </div>
		</div>

@endsection
