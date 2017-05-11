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
        $repos = array();

        foreach ($accounts as $key => $account) {
            $client = $this->getClient($account->provider);
            $client->setToken($account->token);
            $repos[$account->provider.'_'.$account->login] = $client->listRepositories();
        }

        return var_dump($repos);
    }
    
    public function getOpenedPullRequestForRepo(Request $request) {

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