<?php

namespace App\Http\Controllers;

use App\Classes\GitProviderFactory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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