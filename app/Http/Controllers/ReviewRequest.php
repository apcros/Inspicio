<?php

namespace App\Http\Controllers;

use App\Classes\GitProviderFactory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use \Ramsey\Uuid\Uuid;

class ReviewRequest extends Controller
{
    //TODO : Move that to a Facade to avoid duplication
    private function getClient($provider) {
        $factory = new GitProviderFactory($provider);
        return $factory->getProviderEngine();
    }

    public function createForm(Request $request) {

        $accounts = $this->availableAccounts();
        $reposPerAccount = array();

        foreach ($accounts as $key => $account) {
            $client = $this->getClient($account->provider);
            $client->setToken($account->token);

            $reposPerAccount[] = array(
                'account_id' => $account->id,
                'repos' => $client->listRepositories()
            );
        }

        return view('newreview',['reposPerAccount' => $reposPerAccount]);
    }
    
    public function getOpenedPullRequestForRepo($owner, $repo, $account_id) {

        $account = DB::table('accounts')->where([
            ['user_id','=', session('user_id')],
            ['id', '=', $account_id]])->first();

        $client = $this->getClient($account->provider);
        $client->setToken($account->token);

        $raw_response = $client->listPullRequestsForRepo($owner, $repo);

        return json_encode($raw_response);
    }

    public function create(Request $request) {
        $title                  = $request->input('title');
        $repository_account     = $request->input('repository');
        $language               = $request->input('language');
        $pull_request_url        = $request->input('pull_request');
        $description            = $request->input('description');

        list($owner_repo, $account_id) = explode(',', $repository_account);

        $account = DB::table('accounts')->where([
            ['user_id','=', session('user_id')],
            ['id', '=', $account_id]])->first();

        if(!$account) {
            return view('home', ['error_message' => 'Unexpected error']);
        }

        if(!$pull_request_url) {
            // No pull request url, we need to create one
            // TODO
        }

        $review_request_id = Uuid::uuid4()->toString();

        try {
            DB::table('requests')->insert([
                'id' => $review_request_id,
                'name' => $title,
                'description' => $description,
                'url' => $pull_request_url,
                'status' => 'open',
                'language' => $language,
                'author_id' => session('user_id'),
                'repository' => $owner_repo,
                'account_id' => $account_id,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error("Error caught while adding Pull request : ".$e->getMessage());
            return view('home', ['error_message' => $e->getMessage()]);
        }

        return 'OK';
        //TODO redirect to the pull request created
    }

    public function approve(Request $request) {

    }

    public function track(Request $request) {

    }

    public function viewAsPublic(Request $request) {

    }

    public function viewAsOwner(Request $request) {

    }

    public function viewAllMine(Request $request) {

    }

    public function viewAllTracked(Request $request) {

    }

    private function availableAccounts() {
        return DB::table('accounts')->where('user_id',session('user_id'))->get();
    }
}