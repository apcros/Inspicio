@extends('layouts.materialize-main')
@section('title', 'Login')

@section('content')
<div class="container">
	<div class="card">
		<div class="card-content">
			<span class="card-title">Login to Inspicio</span>
			<div class="row">
				<div class="center-align">
					<a href="/oauth/github" class="waves-effect waves-light btn btn-info middle-red-purple"><i class="fa fa-github left" aria-hidden="true"></i> Login with GitHub</a>
					<a href="/oauth/bitbucket" class="waves-effect waves-light btn btn-info middle-red-purple"><i class="fa fa-bitbucket left" aria-hidden="true"></i> Login with Bitbucket</a>
					<a href="#" class="waves-effect waves-light btn btn-info disabled middle-red-purple"><i class="fa fa-gitlab left" aria-hidden="true"></i> Login with Gitlab</a>
				</div>
			</div>
		</div>
	</div>
@endsection