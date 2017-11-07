<!DOCTYPE html>
<html>

	<head>
		<title>Inspicio Code Reviews - @yield('title')</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name=viewport content="width=device-width, initial-scale=1">
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<link href="https://fonts.googleapis.com/css?family=Molengo" rel="stylesheet">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/css/materialize.min.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="{{ secure_asset('css/snackbar.min.css') }}">
		<link rel="stylesheet" href="{{ secure_asset('css/snackbar-material.css') }}">
		<link rel="stylesheet" href="{{ secure_asset('css/inspicio.css') }}">
		<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/js/materialize.min.js"></script>
		<script type="text/javascript" src="{{ secure_asset('js/snackbar.min.js') }}"></script>
		<script type="text/javascript" src="{{ secure_asset('js/popout.js') }}"></script>
		@if (App::environment('production'))
			<script type="text/javascript" src="{{secure_asset('js/vue.min.js')}}"></script>
		@else
			<script type="text/javascript" src="{{secure_asset('js/vue.js')}}"></script>
		@endif
		<script type="text/javascript">
			$(document).ready(function(){
				$(".dropdown-button").dropdown();
				$(".button-collapse").sideNav();
			});
		</script>
		@yield('additional_head')
		<nav class="raisin-black">
			<div class="nav-wrapper">
				<a href="/" class="brand-logo"><img class="navbar-logo" src="{{secure_asset('img/logo_navbar.png')}}"></a>
				<a href="#" data-activates="mobile-menu" class="button-collapse"><i class="fa fa-bars" aria-hidden="true"></i></a>
				<ul id="nav-mobile" class="right hide-on-med-and-down">
				@if (Session::has('user_email'))
					<ul id="account" class="dropdown-content">
					  <li><a href="/account">My account<i class="fa fa-user" aria-hidden="true"></i></a></li>
					  <li><a href="/logout">Logout<i class="fa fa-sign-out" aria-hidden="true"></i></a></li>
					</ul>
					<ul id="reviews-actions" class="dropdown-content">
						<li><a href="/reviews/create">Create<i class="fa fa-plus-square-o" aria-hidden="true"></i></a></li>
			            <li><a href="/reviews/mine">Mine<i class="fa fa-cube" aria-hidden="true"></i></a></li>
			            <li><a href="/reviews/tracked">Show tracked<i class="fa fa-eye" aria-hidden="true"></i></a></li>
			            <li><a href="/reviews/bulk-import">Bulk import<i class="fa fa-cubes" aria-hidden="true"></i></a></li>
			            <li><a href="/reviews/auto-import">Auto import<i class="fa fa-magic" aria-hidden="true"></i></a></li>
					</ul>
					<li><a class="dropdown-button" href="#!" data-activates="account">{{Session::get('user_email')}}<i class="fa fa-chevron-down right" aria-hidden="true"></i></a></li>
					<li><a class="dropdown-button" href="#!" data-activates="reviews-actions">Reviews actions<i class="fa fa-chevron-down right" aria-hidden="true"></i></a></li>
				@else
					<li><a href="/choose-auth" class="giants-orange-text"><b>Login</b></a></li>
				@endif
			         <li><a href="/about">About</a></li>
			        <li><a href="https://github.com/apcros/Inspicio/issues">Bugs / Suggestions</a></li>
				</ul>
				<ul class="side-nav" id="mobile-menu">
				@if (Session::has('user_email'))
						<li><a class="force-orange-highlight" href="/logout">Logout<i class="fa fa-sign-out force-orange-highlight" aria-hidden="true"></i></a></li>
					  	<li><a href="/account">{{Session::get('user_email')}}<i class="fa fa-user" aria-hidden="true"></i></a></li>
					  	<div class="divider"></div>
						<li><a href="/reviews/create">Create review<i class="fa fa-plus-square-o" aria-hidden="true"></i></a></li>
			            <li><a href="/reviews/mine">My reviews<i class="fa fa-cube" aria-hidden="true"></i></a></li>
			            <li><a href="/reviews/tracked">Tracked reviews<i class="fa fa-eye" aria-hidden="true"></i></a></li>
			            <li><a href="/reviews/bulk-import">Bulk import<i class="fa fa-cubes" aria-hidden="true"></i></a></li>
			            <li><a href="/reviews/auto-import">Auto import<i class="fa fa-magic" aria-hidden="true"></i></a></li>
				@else
					<li><a href="/choose-auth" class="force-orange-highlight"><b>Login</b><i class="fa fa-sign-in force-orange-highlight" aria-hidden="true"></i></a></li>
				@endif
					 <div class="divider"></div>
			         <li><a href="/about">About<i class="fa fa-info" aria-hidden="true"></i></a></li>
			         <li><a href="https://github.com/apcros/Inspicio/issues">Bugs / Suggestions<i class="fa fa-bug" aria-hidden="true"></i></a></li>
			      </ul>
			</div>
		</nav>
	</head>

	<body>
		<div class="col s12">
			<div class="row">
				@if (isset($error_message))
					<div class="card red darken-1">
						<div class="card-content white-text">
					    	<span class="card-title">An error ocurred</span>
					        <p>{{ $error_message }}</p>
					        @if (isset($error_html))
					           {!! $error_html !!}
					        @endif
						</div>
					</div>
				@endif
				@if (isset($info_message))
					<div class="card">
						<div class="card-content blue lighten-2 raisin-black-text">
					    	<span class="card-title"><b>Success</b></span>
					        <p>{{ $info_message }}</p>
					        @if (isset($info_html))
					           {!! $info_html !!}
					        @endif
						</div>
					</div>
				@endif
				@yield('content')
			</div>
		</div>
	</body>
</html>