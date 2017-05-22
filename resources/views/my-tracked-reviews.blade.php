@extends('layouts.bootstrap-main')
@section('title', 'My Review Request')

@section('content')
<h4>Reviews you follow : </h4>
<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
	<div class="panel panel-default">
		<div class="panel-heading" role="tab" id="unapproved-heading">
			<h4 class="panel-title">
				<a role="button" data-toggle="collapse" data-parent="#accordion" href="#unapproved" aria-expanded="true" aria-controls="unapproved">
				Unapproved ({{count($reviews_unapproved)}})
				</a>
			</h4>
		</div>
		<div id="unapproved" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="unapproved-heading">
			<div class="panel-body">
				<div class="list-group">
					@foreach ($reviews_unapproved as $review)
					      		<a href="/reviews/{{$review->id}}/view" target="_blank" class="list-group-item">
					      			<b>{{$review->name}}</b> - {{$review->updated_at}}
					      			<span class="label label-info pull-right">{{$review->language}}</span>
					      		</a>
					@endforeach
				</div>
			</div>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading" role="tab" id="approved-heading">
			<h4 class="panel-title">
				<a role="button" data-toggle="collapse" data-parent="#accordion" href="#approved" aria-expanded="false" aria-controls="approved">
				Approved ({{count($reviews_approved)}})
				</a>
			</h4>
		</div>
		<div id="approved" class="panel-collapse collapse" role="tabpanel" aria-labelledby="approved-heading">
			<div class="panel-body">
				<div class="list-group">
					@foreach ($reviews_approved as $review)
					      		<a href="/reviews/{{$review->id}}/view" target="_blank" class="list-group-item">
					      			<b>{{$review->name}}</b> - {{$review->updated_at}}
					      			<span class="label label-info pull-right">{{$review->language}}</span>
					      		</a>
					@endforeach
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
