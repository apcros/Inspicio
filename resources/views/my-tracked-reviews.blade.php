@extends('layouts.materialize-main')
@section('title', 'Reviews you follow')

@section('content')
<div class="row">
      <ul class="tabs tabs-fixed-width">
        <li class="tab col s6"><a class="active" href="#pending">Pending reviews ({{count($active)}})</a></li>
        <li class="tab col s6"><a href="#archived">Archived (Closed and/or Approved)</a></li>
      </ul>
</div>
<div class="container">
    <div id="pending" class="col s12">
    	@foreach ($active as $review)
    		@include('single-review-partial')
    	@endforeach
    </div>
    <div id="archived" class="col s12">
    	@foreach ($archived as $review)
    		@include('single-review-partial')
    	@endforeach
    </div>
</div>
@endsection
