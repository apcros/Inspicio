@extends('layouts.bootstrap-main')
@section('title', 'About')

@section('content')
    <div class="jumbotron">
        <p>
            <h2>What is Inspicio ?</h2>
            You can see Inspicio as a social network exclusively focused around code reviews.
            As hobbyist developers, it's often quite hard to find experienced people to review your code
            </br>
            That's where Inspicio come in play ! The goal is to put people that want to review code and people that want to get their code reviewed in relation.
        </p>
        <p>
            <h2>How does it work ?</h2>
            You can sign-in with Bitbucket or Github (Gitlab support coming soon) and go create a review.
            Inspicio will load a list of your repo using all your linked Git accounts along with a list of branches and opened pull requests.
            You can create an Inspicio code review request from an existing Bitbucket/Github pull request or create a new one from existing branches.
            </br>
            The actual review platform will be either Github or Bitbucket (Depending of where your repository is hosted)
            Creating a code review request will cost you <b>1 point</b> (You get 5 points upon signup) and will put your code review request on the home page.
            (Your code review can also be searched)
            
            From there, other users might choose to follow your review request, give you feedback and approve your review.
            </br>
            <h4>And how to I get points ?</h4>
            It's easy ! You just have to do someone's review. Inspicio is built around a sharing mindset and it push you as a developer to be exposed
            to code your normally wouldn't.
        </p>
        <p>
            <h2>Skills ?</h2>
            You can add skills to your profile, this is helpful to indicate to other users what language you write in and what's your level
            </br>
            If you do enough reviews or if you request it, you can get a skill verified.
        </p>
    </div>
@endsection