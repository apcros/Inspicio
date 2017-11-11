@extends('layouts.materialize-main')
@section('title', 'Register')

@section('content')
<div class="container">
	<form method="POST" action="/register">
			{{ csrf_field() }}
			<input type="hidden" value="{{ $auth_token }}" name="auth_token"/>
		    <input type="hidden" value="{{ $refresh_token }}" name="refresh_token"/>
		    <input type="hidden" value="{{ $expire_epoch }}" name="expire_epoch"/>
		    <input type="hidden" value="{{ $auth_provider }}" name="auth_provider"/>
		<div class="card">
			<div class="card-content">
				<span class="card-title">Register to Inspicio</span>
				<div class="row">
					<div class="input-field col s12 m6">
						<input placeholder="Your email address" id="email" name="email" type="email" class="validate">
						<label for="email">Email</label>
					</div>
					<div class="input-field col s12 m6">
						<input placeholder="Your name" id="name" name="name" type="text" class="validate">
						<label for="name">Name</label>
					</div>
				</div>
				<div class="row">
						<input type="checkbox" id="accept_tos" name="accept_tos" />
						<label for="accept_tos">I have read and I accept <a class="giants-orange-text" href="/tos">the ToS</a></label>
				</div>
			</div>
			<div class="card-action">
				<button type="submit" class="btn btn-info waves-effect waves-light middle-red-purple"><i class="fa fa-check-square-o left" aria-hidden="true"></i>Register</button>
			</div>
		</div>
	</form>
</div>
@endsection