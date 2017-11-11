@extends('layouts.materialize-main')
@section('title', 'View Review Request')
@section('additional_head')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
@endsection
@section('content')
<div id="modal-account-update" class="modal fade" role="dialog">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-body">
				<h3>Update your account</h3>
				<form method="POST" action="/account">
					{{ csrf_field() }}
					<b>Email</b>
					<p><input type="email" class="form-control" name="email" value="{{$user->email}}"></p>
					<b>Name</b>
					<p><input type="name" class="form-control" name="name" value="{{$user->name}}"></p>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary">Confirm</button>
				</form>
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
			</div>
		</div>
	</div>
</div>
<div class="container">
	<div class="card">
		<div class="card-content">
			<span class="card-title">General info</span>
			@if (! $user->is_confirmed)
			<div class="card-panel yellow">
				<b>Your email is not confirmed. Check your inbox</b>
			</div>
			@else
			<div class="card-panel blue">
				<p>Referral link :</p>
				<p><b>{{env('APP_URL').'/choose-auth?referral='.$user->id}}</b></p>
				<p>Win 5 points for each user registering using this link (And they will get 5 points too)</p>
			</div>
			@endif
			<ul class="collection">
		      <li class="collection-item">
		      	<i class="fa fa-envelope left" aria-hidden="true"></i>
		      	<span class="title"><b>Email</b></span>
		      	<p>{{$user->email}}</p>
		      </li>
		      <li class="collection-item">
		      	<i class="fa fa-address-book left" aria-hidden="true"></i>
		      	<span class="title"><b>Name</b></span>
		      	<p>{{$user->name}}</p>
		      </li>
		   	  <li class="collection-item">
		   	  	<i class="fa fa-money left" aria-hidden="true"></i>
		      	<span class="title"><b>Points</b></span>
		      	<p>{{$user->points}}</p>
		      </li>
		    </ul>
		</div>
		<div class="card-action">
			<button class="btn btn-info middle-red-purple waves-effect waves-light" onclick='$("#modal-account-update").modal("show");'><i class="fa fa-pencil-square-o left" aria-hidden="true"></i>Edit</button>
		</div>
	</div>
	<div class="card">
		<div class="card-content">
			<span class="card-title">GIT accounts</span>
			<table class="bordered striped">
		        <thead>
		          <tr>
		              <th>Provider</th>
		              <th>Login</th>
		              <th>Added on</th>
		              <th>Last used</th>
		              <th>Permission level</th>
		              <th></th>
		          </tr>
		        </thead>
		        <tbody>
				    @foreach ($accounts as $account)
					<tr>
						<td>{{$account->provider }}</td>
						<td>{{$account->login}}</td>
						<td>{{$account->created_at}}</td>
						<td>{{$account->updated_at}}</td>
						<td>{{$permissions[$account->provider][$account->permission_level]['description']}}</td>
						<td><button onclick="$('#modal-permissions-{{$account->provider}}').modal('show');" class="btn btn-info middle-red-purple waves-effect waves-light right"><i class="fa fa-cogs" aria-hidden="true"></i></button></div></td>
					</tr>
					@endforeach
		        </tbody>
		    </table>
		</div>
		<div class="card-action">
			<a href="/oauth/github/" class="btn btn-info waves-effect waves-light middle-red-purple"><i class="fa fa-github left" aria-hidden="true"></i> Link new GitHub</a>
			<a href="/oauth/bitbucket/" class="btn btn-info waves-effect waves-light middle-red-purple"><i class="fa fa-bitbucket left" aria-hidden="true"></i> Link new Bitbucket</a>
			<a href="#" class="btn btn-info waves-effect waves-light middle-red-purple disabled"><i class="fa fa-gitlab left" aria-hidden="true"></i> Link new  Gitlab</a>
		</div>
	</div>
	<div class="card">
		<div class="card-content">
			<span class="card-title">Skills</span>
			<table class="bordered striped">
				<thead>
					<tr>
						<th>Name</th>
						<th>Level</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
		<div class="card-action">
			<button onclick="" class="btn btn-info middle-red-purple waves-effect waves-light"><i class="fa fa-plus-square-o left" aria-hidden="true"></i>Add new skill</button>
		</div>
	</div>
	<div class="card">
		<div class="card-content">
			<span class="card-title">Settings</span>
			<ul class="collection">
				@foreach ($settings as $setting)
					@if ($setting->category != 'not_active')
						<li class="collection-item">
							@if ($setting->type == 'boolean')
								@if($setting->value)
									<input type="checkbox" id="{{$setting->key}}" name="setting_{{$setting->key}}" checked="checked">
								@else
									<input type="checkbox" id="{{$setting->key}}" name="setting_{{$setting->key}}">
								@endif
							@endif
							<label for="{{$setting->key}}"><b>{{$setting->name}}</b></label>
						</li>
					@endif
				@endforeach
			</ul>
		</div>
		<div class="card-action">
			<button onclick="updateSettings()" class="btn btn-info waves-effect waves-light middle-red-purple"><i class="fa fa-save left"></i>Save changes</button>
		</div>
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
					You can ask for skill verification to access premium review requests
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="button" onclick="addSkill()" class="btn btn-primary" data-dismiss="modal">Add</button>
			</div>
		</div>
	</div>
</div>
@foreach ($permissions as $git_provider => $git_permissions)
<div id="modal-permissions-{{$git_provider}}" class="modal fade" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h3>Update permissions for {{ucfirst($git_provider)}}</h3>
			</div>
			<div class="modal-body">
				<div class="alert alert-info">
					You can change the permission level of your account by chosing any of the available permissions.
					Please note that you will just be redirected to {{$git_provider}} with the relevant permission upgrade/downgrade request.
					As such, <b>make sure you are logged in to the right {{$git_provider}} account before clicking the button.</b>
				</div>
				<div class="text-center">
					<div class="btn-group-vertical" role="group">
						@foreach ($git_permissions as $permission_key => $permission)
						<a href="/oauth/{{$git_provider}}/?perm_level={{$permission_key}}" class="btn btn-info">{{$permission['description']}}</a>
						@endforeach
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
			</div>
		</div>
	</div>
</div>
@endforeach
<script type="text/javascript">
	$(document).ready(function(){
		$('#skill').select2({placeholder: "Select a skill/language", autocomplete: "on"});
	});
</script>
<script type="text/javascript" src="{{ secure_asset('js/async-action-skills.js') }}"></script>
<script type="text/javascript" src="{{ secure_asset('js/async-action-settings.js') }}"></script>
@endsection