@extends('layouts.bootstrap-main')
@section('title', 'Register')

@section('additional_head')
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
@endsection

@section('content')
	  <h2>Bulk import reviews</h2>
	  <div class="alert">
	  	<p>You have <b>{{$user->points}} points</b> left, which means you can import up to {{$user->points}} pull requests to Inspicio</p>
	  	<select class="form-control" name="prs_selected" id ="prs_selected">
	  	</select>
	  	<hr>
	  	<button type="submit" class="btn btn-primary" disabled>Import selected pull requests</button>
	  </div>
	  <script type="text/javascript" src="{{ secure_asset('js/bulk-import-form.js') }}"></script>
@endsection