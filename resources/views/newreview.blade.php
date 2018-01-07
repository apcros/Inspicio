@extends('layouts.materialize-main')
@section('title', 'New Review')
@section('additional_head')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
<script type="text/javascript" src="{{ secure_asset('js/tinymce.min.js') }}"></script>
@endsection
@section('content')
<form method="POST" action="/reviews/create">
{{ csrf_field() }}
@foreach ($reposPerAccount as $repos)
	<input type='hidden' id="{{$repos['account_id']}}_permission" value="{{json_encode($repos['permission'])}}" />
	@foreach ($repos['repos'] as $repo)
		<input type='hidden' id="{{ $repo->name }}_metadata" value="{{ $repo->language }}" />
	@endforeach
@endforeach
<div class="card">
	<div class="card-content">
		<span class="card-title">Open a new review request</span>
		<div class="row">
				<p>You currently have <b>{{$points}}</b> point(s). Upon creating this review request, one point will be deducted from this total.</p>
				<p>You can do someone else code review to win more points.</p>
			<div class="giants-orange-text" hidden id="warning_no_write">
				<i class="fa fa-exclamation-triangle left" aria-hidden="true"></i>
				<b>The account linked to the repository selected does not have a write permission.
				This means Inspicio can't create the pull request for you.
				You can change the permissions on <a href="/account">your account</b></a>
			</div>
		</div>
		<div class="row">
			<div class="col s12 m6">
				<label for="repository">Select your repository</label>
				<select style="width: 100%" name="repository" id="repository" class="form-control" placeholder="Select a repository">
					@foreach ($reposPerAccount as $repos)
						@foreach ($repos['repos'] as $repo)
						<option value="{{ $repo->name }},{{$repos['account_id']}}">{{ $repo->name }}</option>
						@endforeach
					@endforeach
				</select>
			</div>
			<div class="col s12 m6">
				<label for="language">Language</label>
				<select style="width: 100%" name="language" id="language" class="form-control" placeholder="Select a language">
					<option></option>
					@foreach ($languages as $language)
					<option value="{{$language->id}}">{{$language->name}}</option>
					@endforeach
				</select>
			</div>
			<div class="col s12">
				<label for="pull_request">Select an open pull request</label>
				<select style="width: 100%" name="pull_request" id="pull_request" class="form-control" placeholder="Select an open pull request">
				</select>
			</div>
			<div class="col s12 m6">
				<input type="checkbox" id="new_pull_request">
				<label for="new_pull_request">Or create new one</label>
			</div>
			<div class="col s12" id="new_pull_request_branches_select" hidden>
				<div class="col s6">
					<label for="head_branch">Select head branch</label>
					<select style="width: 100%" name="head_branch" id="head_branch" class="form-control" placeholder="Select the branch where your changes are">
					</select>
				</div>
				<div class="col s6">
					<label for="base_branch">Select base branch</label>
					<select style="width: 100%" name="base_branch" id="base_branch" class="form-control" placeholder="Select the branch where your changes will be pulled">
					</select>
				</div>
			</div>
			</div>
			<div class="row">
			<div class="col s12">
				<label for="title">Title</label>
				<input type="text" class="form-control" name="title" id="title" placeholder="Enter a title for your code review request">
			</div>
			<div class="col s12">
				<label for="description">Description</label>
				<textarea class="form-control" rows="3" name="description" id="description"></textarea>
			</div>
		</div>
	</div>
	<div class="card-action">
		<button type="submit" class="btn btn-info middle-red-purple waves-effect waves-light"><i class="fa fa-plus-square-o left" aria-hidden="true"></i>Post</button>
	</div>
</div>
</form>
<script type="text/javascript" src="{{ secure_asset('js/review-form.js') }}"></script>
@endsection