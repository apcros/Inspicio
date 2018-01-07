@extends('layouts.materialize-main')
@section('title', 'Login')

@section('content')
<div class="container">
	<div class="card">
		<div class="card-content">
			<span class="card-title">Login to Inspicio</span>
			<div class="row">
				<div class="center-align">
					<div class="row col m4 s12">
						<a href="/oauth/github" class="col s12 waves-effect waves-light btn btn-info middle-red-purple"><i class="fa fa-github left" aria-hidden="true"></i> Login with GitHub</a>
					</div>
					<div class="row col m4 s12">
						<a href="/oauth/bitbucket" class="col s12 waves-effect waves-light btn btn-info middle-red-purple"><i class="fa fa-bitbucket left" aria-hidden="true"></i> Login with Bitbucket</a>
					</div>
					<div class="row col m4 s12">
						<a href="#" class="col s12 waves-effect waves-light btn btn-info disabled middle-red-purple"><i class="fa fa-gitlab left" aria-hidden="true"></i> Login with Gitlab</a>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection