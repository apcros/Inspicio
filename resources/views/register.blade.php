@extends('layouts.bootstrap-main')
@section('title', 'Register')

@section('content')
	<div class="jumbotron">
	  <h2>Register to Inspicio </h2>
	  <div class="center-align">
		<form method="POST" action="/register">
		 {{ csrf_field() }}
		  <div class="form-group">
		    <label for="email">Email address</label>
		    <input type="hidden" value="{{ $auth_token }}" name="auth_token"/>
		    <input type="hidden" value="{{ $auth_provider }}" name="auth_provider"/>
		    <input type="email" class="form-control" name="email" id="email" placeholder="Email">
		  </div>
		  <div class="form-group">
		    <label for="name">Name</label>
		    <input type="text" class="form-control" name="name" id="name" placeholder="Your name">
		  </div>
		  <button type="submit" class="btn btn-default">Register</button>
		</form>
	  </div>
	</div>
@endsection