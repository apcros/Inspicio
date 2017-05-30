@extends('layouts.bootstrap-main')
@section('title', 'View Review Request')

@section('additional_head')
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
@endsection


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
		  	<a href="/oauth/github/" class="btn btn-info"><i class="fa fa-github left" aria-hidden="true"></i> Link new GitHub</a>
		  	<a href="/oauth/bitbucket/" class="btn btn-info"><i class="fa fa-bitbucket left" aria-hidden="true"></i> Link new Bitbucket</a>
		  	<a href="#" class="btn btn-info disabled"><i class="fa fa-gitlab left" aria-hidden="true"></i> Link new  Gitlab</a>
		  </div>
		</div>

	  	<div class="panel panel-default">
		  <div class="panel-heading">
		    <h3 class="panel-title">My Skills</h3>
		  </div>
		  <div class="panel-body">
		  	<table class="table table-bordered" id="skill_list">
			  	<tr>
			  		<th>Name</th>
			  		<th>Level</th>
			  	</tr>
		  	@foreach ($skills as $skill)
		  		<tr id="skill-{{$skill->id}}">
		  			<td>
		  			{{$skill->name}}
		  			@if ($skill->is_verified)
		  				<span class="badge">Verified</span>
		  			@endif
		  			</td>

		  			<td>
		  			@if ($skill->level == 1)
		  				Beginner/Junior
		  			@elseif ($skill->level == 2)
		  				Intermediate
		  			@else
		  				Advanced/Senior
		  			@endif
		  			<button onclick="deleteSkill('{{$skill->id}}')" class="btn btn-danger pull-right">Delete</button>
		  			</td>
		  		</tr>
		  	@endforeach
		  	</table>
		  </div>
		  	<div class="panel-footer">
		  		<a onclick="$('#modal-skill').modal('show');" class="btn btn-info">Add new skill</a>
		  	</div>
		</div>
	<div id="modal-skill" class="modal fade" role="dialog">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	    	<div class="modal-header">
	    		 <h3>Add a new skill/language</h3>
	    	</div>
	    	<div class="modal-body">
		      		<div class="form-group">
			      	<select name="skill" id="skill" class="form-control" placeholder="Select a skill/language" style="width: 100%">
				    	<option></option>
				    @foreach ($available_skills as $skill)
				    	<option value="{{$skill->id}}">{{$skill->name}}</option>
				    @endforeach
					</select>
					</div>
					<div class="form-group">
					 <select name="level" id="level" class="form-control" placeholder="Language Level">
					  	<option value="1">Beginner/Junior</option>
					  	<option value="2">Intermediate</option>
					  	<option value="3">Advanced/Senior</option>
					 </select>
					 </div>
				  	<div class="alert alert-info">
				  	Once you skill is added and you've done few reviews in that skill/language, 
				  	You can ask for skill verification to access to premium review requests
				  	</div>
	      </div>
		<div class="modal-footer">
	      <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
	      <button type="button" onclick="addSkill()" class="btn btn-primary" data-dismiss="modal">Add</button>
	    </div>
	    </div>
	  </div>
	</div>
	<script type="text/javascript">
		$(document).ready(function(){
			$('#skill').select2({placeholder: "Select a skill/language", autocomplete: "on"});
		});
	</script>
	<script type="text/javascript" src="{{ asset('js/async-action-skills.js') }}"></script>
@endsection
