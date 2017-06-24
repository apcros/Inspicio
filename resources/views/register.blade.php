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
		    <input type="hidden" value="{{ $refresh_token }}" name="refresh_token"/>
		    <input type="hidden" value="{{ $expire_epoch }}" name="expire_epoch"/>
		    <input type="hidden" value="{{ $auth_provider }}" name="auth_provider"/>
		    <input type="email" class="form-control" name="email" id="email" placeholder="Email">
		  </div>
		  <div class="form-group">
		    <label for="name">Name</label>
		    <input type="text" class="form-control" name="name" id="name" placeholder="Your name">
		  </div>
		  <div class="form-group">
		  	<label for="accept_tos">I have read and I accept <a href="/tos">the ToS</a></label>
		  	<input type="checkbox" name="accept_tos" id="accept_tos">
		  </div>
		  <button type="submit" class="btn btn-default">Register</button>
		</form>
	  </div>
	</div>
@endsection