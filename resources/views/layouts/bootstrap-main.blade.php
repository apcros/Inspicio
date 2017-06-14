<!DOCTYPE html>
<html>

	<head>
		<title>Inspicio - @yield('title')</title>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<link href="https://fonts.googleapis.com/css?family=Molengo" rel="stylesheet">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="{{ asset('css/snackbar.min.css') }}">
		<link rel="stylesheet" href="{{ asset('css/snackbar-material.css') }}">
		<link rel="stylesheet" href="{{ asset('css/inspicio.css') }}">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
		<script type="text/javascript" src="{{ asset('js/snackbar.min.js') }}"></script>
		<script type="text/javascript" src="{{ asset('js/popout.js') }}"></script>
		@yield('additional_head')
		<nav class="navbar navbar-default">
		  <div class="container-fluid">
		    <div class="navbar-header">
		      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
		        <span class="sr-only">Toggle navigation</span>
		        <span class="icon-bar"></span>
		        <span class="icon-bar"></span>
		        <span class="icon-bar"></span>
		      </button>
		      <a class="navbar-brand" href="/">Inspicio</a>
		    </div>
		    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
		      <ul class="nav navbar-nav">
		        @if (Session::has('user_email'))
			        <li class="dropdown">
			          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{{ Session::get('user_email') }}<span class="caret"></span></a>
			          <ul class="dropdown-menu">
			            <li><a href="/account">My account</a></li>
			            <li><a href="/logout">Logout</a></li>
			          </ul>
			        </li>
			        <li class="dropdown">
			          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Review requests <span class="caret"></span></a>
			          <ul class="dropdown-menu">
			            <li><a href="/reviews/mine">Show mine</a></li>
			            <li><a href="/reviews/tracked">Show tracked</a></li>
			            <li><a href="/reviews/create">Create new</a></li>
			          </ul>
			        </li>
		        @else
		        	<li><a href="/choose-auth">Login</a></li>
		        @endif
		        	<li class="dropdown">
			          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">More info <span class="caret"></span></a>
			          <ul class="dropdown-menu">
			            <li><a href="/about">About</a></li>
			            <li><a href="https://github.com/apcros/Inspicio/issues">Bugs / Suggestions</a></li>
			          </ul>
			        </li>
		      </ul>
		    </div>
		  </div>
		</nav>
	</head>

	<body>
		<div class="container-fluid">
			@if (isset($error_message))
				<div class="alert alert-danger">
					<h3>An error ocurred !</h3>
				    {{ $error_message }}
				</div>
			@endif
			@if (isset($info_message))
				<div class="alert alert-info">
				    {{ $info_message }}
				</div>
			@endif
			@yield('content')
		</div>
	</body>

</html>