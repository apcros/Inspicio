@extends('layouts.materialize-main')
@section('title', 'About')

@section('content')
     <div class="row center-align landing-block-1">
        <h2>Write better code !</h2>
        <p>Pump up your personal projects to the next level !</p>
        <p>Listen to experienced developers from all around the world and give your
        Code the attention it deserves.</p>
        <h5 class="middle-red-purple-text"><b>Gain Inspicio points by reviewing code and use theses points to put up code review requests</b></h5>
     </div>
     <div class="row center-align raisin-black white-text landing-block-2">
        <br>
        <h2>What is Inspicio ?</h2>
           <div class="col s12 m4">
                <div class="row">
                    <div class="col s12 landing-icon giants-orange-text"><i class="fa fa-users" aria-hidden="true"></i></div>
                    <div class="col s12">
                        <h5>Take part in the community</h5>
                        <p>Look at new languages, be involved in other developers projects.</p>
                        <p>Build up your profile and your skills,
                        The more skills you rack up and the more reviews you do, the most likely you are to be selected to be able to review private content and become a premium user</p>
                    </div>
                </div>
          </div>
           <div class="col s12 m4">
                <div class="row">
                    <div class="col s12 landing-icon giants-orange-text"><i class="fa fa-lock" aria-hidden="true"></i></div>
                    <div class="col s12">
                        <h5>Sensitive Content ?</h5>
                                                <p>If you don’t want all eyes on your code you can create private reviews* using our premium plans. You can then rely on the verified professional developers using Inspicio to analyze in details your code.</p>
                    </div>
                </div>
          </div>
          <div class="col s12 m4">
                <div class="row">
                    <div class="col s12 landing-icon giants-orange-text"><i class="fa fa-bell" aria-hidden="true"></i></div>
                    <div class="col s12">
                        <h5>Reminders & Recommendations</h5>
                        <p>Never forget a pull request or chase a co-worker again !</p>
                        <p>Inspicio will remind you of forgotten pull requests</p>
                        <p>If you're bored, you can enable the PR recommandation to be suggested with code you might be interested in</p>
                    </div>
                </div>
          </div>
          <p class="left">     <i>*Available soon</i></p>
     </div>
     <div class="row center-align landing-block-3">
     @if (!Session::has('user_email'))
     
            <h2>Sign in now</h2>
            <h5>It's easy, you just need a Github or Bitbucket account and you're all set !</h5>
            <a href="/choose-auth" class="btn btn-large middle-red-purple action-btn-orange waves-effect waves-light"><i class="fa fa-sign-in left" aria-hidden="true"></i>Sign in</a>
     @endif
     </div>
@endsection