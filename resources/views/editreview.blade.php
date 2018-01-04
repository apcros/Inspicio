@extends('layouts.materialize-main')
@section('title', 'Edit Review')

@section('additional_head')
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
	<script type="text/javascript" src="{{ secure_asset('js/tinymce.min.js') }}"></script>
@endsection

@section('content')
<form method="POST" action="/reviews/{{$review->id}}/edit">
{{ csrf_field() }}
<div class="card">
	<div class="card-content">
		<span class="card-title">Edit code review request ({{$review->repository}})</span>
		<div class="row">
			<div class="col s12 m6">
				<label for="title">Title</label>
				<input type="text" class="form-control" name="title" id="title" value="{{$review->name}}" placeholder="Enter a title for your code review request">
			</div>
			<div class="col s12 m6">
				<label for="language">Language</label>
				<select style="width: 100%" name="language" id="language" class="form-control" placeholder="Select a language">
					<option></option>
					@foreach ($languages as $language)
				    	@if ($language->id == $review->skill_id)
				    		<option selected="selected" value="{{$language->id}}">{{$language->name}}</option>
				    	@else
				    		<option value="{{$language->id}}">{{$language->name}}</option>
				    	@endif
					@endforeach
				</select>
			</div>
			</div>
			<div class="row">
			<div class="col s12">
				<label for="description">Description</label>
				<textarea class="form-control" rows="3" name="description" id="description">{!! $review->description !!}</textarea>
			</div>
			</div>
			<div class="row">
				@if($permission['can_create_pr'])
					<div class="col s12">
						<input type="checkbox" name="update_on_git" id="update_on_git">
						<label for="update_on_git">Update on {{$provider}} ?</label>
					</div>
				@else
					<div class="giants-orange-text">
						<i class="fa fa-exclamation-triangle left" aria-hidden="true"></i>
						<b>The account linked to this pull request does not have a write permission.
						This means Inspicio can't update the pull request for you on {{$provider}}.
						You can change the permissions on <a href="/account">your account</b></a>
					</div>
				@endif
			</div>
	</div>
	<div class="card-action">
		<button type="submit" class="btn btn-info middle-red-purple waves-effect waves-light"><i class="fa fa-plus-square-o left" aria-hidden="true"></i>Edit</button>
	</div>
</div>
</form>
<script type="text/javascript" src="{{ secure_asset('js/edit-review-form.js') }}"></script>
@endsection
