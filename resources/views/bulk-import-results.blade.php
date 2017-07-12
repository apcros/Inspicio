@extends('layouts.bootstrap-main')
@section('title', 'Register')

@section('content')
	  <h2>Bulk import result</h2>
	  @foreach ($results as $result)
	  	{{$result['message']}}
	  @endforeach
@endsection