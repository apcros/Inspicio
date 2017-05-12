@extends('layouts.bootstrap-main')
@section('title', 'New Review')

@section('additional_head')
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
@endsection

@section('content')
	  <h2>Open a new review request</h2>
	  	    @foreach ($reposPerAccount as $repos)
		    	@foreach ($repos['repos'] as $repo)
		    		<input type='hidden' id="{{$repo['name']}}_metadata" value="{{$repo['language']}}">
		    	@endforeach
		    @endforeach
	  <div class="container-fluid center-align">
		<form method="POST" action="/reviews/create">
		 {{ csrf_field() }}
		  <div class="form-group">
		    <label for="title">Title</label>
		    <input type="text" class="form-control" name="title" id="title" placeholder="Enter a title for your code review request">
		  </div>
		  <div class="form-group">
		    <label for="repository">Select your repository</label>
		    <select name="repository" id="repository" class="form-control">
		    @foreach ($reposPerAccount as $repos)
		    	@foreach ($repos['repos'] as $repo)
		    		<option value="{{$repo['name']}},{{$repos['account_id']}}">{{$repo['name']}}</option>
		    	@endforeach
		    @endforeach
			</select>
		  </div>  
		  <div class="form-group">
		    <label for="language">Language</label>
		    <input type="text" class="form-control" name="language" id="language" placeholder="PHP, Perl, JavaScript...">
		  </div>
		  <div class="form-group">
		    <label for="pull_request">Select an open pull request</label>
		    <select name="pull_request" id="pull_request" class="form-control">
			</select>
		  </div>
		  <div class="checkbox">
			  <label>
			    <input type="checkbox" id="new_pull_request">
			    Or create a new one
			  </label>
			</div>
		  <div class="form-group">
		    <label for="description">Description</label>
		    <textarea class="form-control" rows="3" name="description" id="description"></textarea>
		  </div>
		  <button type="submit" class="btn btn-default">Post</button>
		</form>
	  </div>
	  <script type="text/javascript" src="{{ asset('js/review-form.js') }}"></script>
@endsection
