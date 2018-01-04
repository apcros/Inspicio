@extends('layouts.materialize-main')
@section('title', 'Trending reviews')
@section('additional_head')
<meta name="description" content="A social hub for code reviews. Get your code reviewed !">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
@endsection
@section('content')
    @if (isset($reviews))
        <nav class="middle-red-purple">
            <div class="nav-wrapper">
                <ul>
                    <li><a href="/">Latest reviews</a></li>
                    <li class="active"><a href="/trending">Trending</a></li>
                    <li><a href="/reviews/search">Search</a></li>
                </ul>
            </div>
        </nav>
        <div class="container">
                    @foreach ($reviews as $review)
                    <div class="card">
                        <div class="card-content">
                            <span class="card-title">{{$review->name}}</span>
                            <div class="row">
                                <div class="col s6 m3">
                                    <i class="fa fa-user left" aria-hidden="true"></i>{{$review->author}}
                                </div>
                                <div class="col s6 m3">
                                    <i class="fa fa-code left" aria-hidden="true"></i>{{$review->language}}
                                </div>
                                <div class="col s6 m3">
                                    <i class="fa fa-calendar left" aria-hidden="true"></i>{{$review->created_at}}
                                </div>
                                <div class="col s6 m3">
                                    <i class="fa fa-users left" aria-hidden="true"></i>{{$review->followers}} follower(s)
                                </div>
                            </div>
                        </div>
                        <div class="card-action">
                                <a href="/reviews/{{$review->id}}/view" class="action-btn-orange btn btn-flat waves-effect waves-light"><i class="fa fa-info-circle left" aria-hidden="true"></i>See more</a>
                        </div>
                    </div>
                    @endforeach
                    {{$reviews->links()}}
        </div>
    @endif
@endsection