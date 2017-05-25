@extends('layouts.bootstrap-main')
@section('title', 'View Review Request')

@section('content')
	<h3>My account</h3>
		 <div class="panel panel-default">
		  <div class="panel-heading">
		    <h3 class="panel-title">General info</h3>
		  </div>
		  <div class="panel-body">
		  	<ul>
		  		<li><b>Email :</b> {{$user->email}}</li>
		  		<li><b>Name :</b> {{$user->name}}</li>
		  		<li><b>Points :</b> {{$user->points}}</li>
		  	</ul>
		  </div>
		</div>
	  	<div class="panel panel-default">
		  <div class="panel-heading">
		    <h3 class="panel-title">My GIT accounts</h3>
		  </div>
		  <div class="panel-body">
			  <table class="table table-bordered">
			  	<tr>
			  		<th>Provider</th>
			  		<th>Login</th>
			  		<th>Added on</th>
			  		<th>Last used</th>
			  	</tr>
			  	@foreach ($accounts as $account)
			  	<tr>
			  		<td>
			  			{{$account->provider }}
				  		@if ($account->is_main)
				  			<span class="badge">Main account</span>
				  		@endif
			  		</td>
			  		<td>{{$account->login}}</td>
			  		<td>{{$account->created_at}}</td>
			  		<td>{{$account->updated_at}}</td>
			  	</tr>
			  	@endforeach
			  	</table>
		  </div>
		  <div class="panel-footer">
		  	<a href="/oauth/github/add" class="btn btn-info"><i class="fa fa-github left" aria-hidden="true"></i> Link new GitHub</a>
		  	<a href="#" class="btn btn-info disabled"><i class="fa fa-bitbucket left" aria-hidden="true"></i> Link new Bitbucket</a>
		  	<a href="#" class="btn btn-info disabled"><i class="fa fa-gitlab left" aria-hidden="true"></i> Link new  Gitlab</a>
		  </div>
		</div>

	  	<div class="panel panel-default">
		  <div class="panel-heading">
		    <h3 class="panel-title">My Skills</h3>
		  </div>
		  <div class="panel-body">
		  	<table class="table table-bordered">
			  	<tr>
			  		<th>Name</th>
			  		<th>Level</th>
			  	</tr>
		  	@foreach ($skills as $skill)
		  		<tr>
		  			<td>
		  			{{$skill->name}}
		  			@if ($skill->is_verified)
		  				<span class="badge">Verified</span>
		  			@endif
		  			</td>

		  			<td>{{$skill->level}}</td>
		  		</tr>
		  	@endforeach
		  	</table>
		  </div>
		  	<div class="panel-footer">
		  		<a href="#" class="btn btn-info disabled">Add new skill</a>
		  	</div>
		</div>
@endsection
