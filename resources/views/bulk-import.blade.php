@extends('layouts.materialize-main')
@section('title', 'Bulk import')

@section('additional_head')
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
	<script type="text/javascript" src="{{ secure_asset('js/vuejs-utils.js') }}"></script>
@endsection

@section('content')
	  <h2>Bulk import reviews</h2>
	  <div class="card">
	  	<form method="POST">
	  	<div class="card-content">
		  		{{ csrf_field() }}
			  	<p>You have <b>{{$user->points}} points</b> left, which means you can import up to {{$user->points}} pull requests to Inspicio</p>
			  	<div id="available_prs" v-cloak>
			  		<div v-if="data.is_loading">
			  			<div class="center-align">
			  				<h4>Loading PRs..</h4>
			  				<i class="fa fa-refresh fa-spin fa-5x fa-fw"></i>
			  			</div>
			  		</div>
				  	<div v-else>
						<select name='prs_selected[]' id='prs_selected' multiple='multiple' style="width: 100%">
							<template v-for="repository in data.repositories">
								<template v-for="pr in repository.pull_requests">
									<option :value="pr.url+','+repository.account_id">@{{pr.name}} (<b>@{{repository.object.name}}</b>)</option>
								</template>
							</template>
						</select>
					</div>
					<div v-if="data.error_message" class="card-panel red white-text">
						<b>@{{data.error_message}}</b>
					</div>
			</div>
	  	</div>
	  	<div class="card-action">
	  		<button type="submit" class="btn btn-info giants-orange waves-effect" id="import-btn" disabled><i class="fa fa-cloud-download left" aria-hidden="true"></i>Import selected pull requests</button>
	  	</div>
	  	</form>
	  </div>
	  <script type="text/javascript" src="{{ secure_asset('js/bulk-import-form.js') }}"></script>
@endsection