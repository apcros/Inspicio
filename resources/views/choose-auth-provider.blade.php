@extends('layouts.bootstrap-main')
@section('title', 'Login')

@section('content')
	<div class="jumbotron">
	  <h2>Login to Inspicio </h2>
	  <div class="center-align">
		  <a href="/oauth/github" class="btn btn-info"><i class="fa fa-github left" aria-hidden="true"></i> Login with GitHub</a>
		  <a href="/oauth/bitbucket" class="btn btn-info"><i class="fa fa-bitbucket left" aria-hidden="true"></i> Login with Bitbucket</a>
		  <a href="#" class="btn btn-info disabled"><i class="fa fa-gitlab left" aria-hidden="true"></i> Login with Gitlab</a>
	  </div>
	</div>
@endsection