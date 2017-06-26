@extends('layouts.bootstrap-main')
@section('title', 'New Review')

@section('additional_head')
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
	<script type="text/javascript" src="{{ secure_asset('js/tinymce.min.js') }}"></script>
@endsection

@section('content')
	  <h2>Edit code review request</h2>
	  <div class="container-fluid center-align">
		<form method="POST" action="/reviews/{{$review->id}}/edit">
		 {{ csrf_field() }}
		  <div class="form-group">
		    <label for="language">Language</label>
		    <select name="language" id="language" class="form-control" placeholder="Select a language">
		    	<option></option>
		    @foreach ($languages as $language)
		    		<option value="{{$language->id}}">{{$language->name}}</option>
		    @endforeach
			</select>
		  </div>
		  <div class="form-group">
		    <label for="title">Title</label>
		    <input type="text" class="form-control" name="title" id="title" placeholder="Enter a title for your code review request">
		  </div>		
		  <div class="form-group">
		    <label for="description">Description</label>
		    <textarea class="form-control" rows="3" name="description" id="description"></textarea>
		  </div>
		  	<div class="form-group">
			  <label for="update_on_git">Update on {{$review->provider}} ?</label>
			  <input type="checkbox" id="update_on_git" name="update_on_git"/>
			</div>
		  <button type="submit" class="btn btn-default">Edit</button>
		</form>
	  </div>
	  <script type="text/javascript" src="{{ secure_asset('js/edit-review-form.js') }}"></script>
@endsection
