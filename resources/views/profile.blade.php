@extends('layouts.materialize-main')
@section('title', 'View Review Request')

@section('content')
<div class="container">
	<h3>{{$user->nickname}} - Profile</h3>
	<div class="card">
		<div class="card-content">
			<span class="card-title">Informations</span>
			<ul class="collection">
				<li class="collection-item"><i class="fa fa-envelope left" aria-hidden="true"></i> {{$user->email}}</li>
				<li class="collection-item"><i class="fa fa-address-book left" aria-hidden="true"></i> {{$user->name}} ({{$user->nickname}})</li>
			</ul>
		</div>
	</div>
	<div class="card">
		<div class="card-content">
			<span class="card-title">Skills</span>
			@if (count($skills))
				<ul class="collection">
				@foreach ($skills as $skill)
					@if ($skill->is_verified)
						<li class="collection-item giants-orange-text tooltipped"data-position="left" data-delay="50" data-tooltip="Skill verified" aria-hidden="true">
		  					<i class="fa fa-certificate"></i>
		  			@else
		  				<li class="collection-item">
		  			@endif
						<b>{{$skill->name}}</b>
						<br>
		  			@if ($skill->level == 1)
		  				Beginner/Junior
		  			@elseif ($skill->level == 2)
		  				Intermediate
		  			@else
		  				Advanced/Senior
		  			@endif						 
					</li>
				@endforeach
				</ul>
			@else
				<b>This user has no skills added to their account</b>
			@endif
		</div>
	</div>
	<div class="card">
		<div class="card-content">
			<span class="card-title">Open reviews</span>
			<ul class="collection">
				@foreach ($reviews as $review)
					<a href="/reviews/{{$review->id}}/view" class="collection-item black-text">
						<div class="row">
				            <div class="col s6">
				                <b><i class="fa fa-code left" aria-hidden="true"></i>{{$review->language}}</b>
				            </div>
				            <div class="col s6">
				                <b><i class="fa fa-calendar left" aria-hidden="true"></i>{{$review->created_at}}</b>
				            </div>
				        </div>
				        {{$review->name}}
					</a>
				@endforeach
			</ul>
		</div>
	</div>
</div>
@endsection
