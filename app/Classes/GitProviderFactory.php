<?php
namespace App\Classes;

class GitProviderFactory {
	private $app_secret;

	private $client_id;

	private $provider;

	public function __construct($provider) {
		$this->provider = $provider;
		$this->client_id = env(strtoupper($this->provider) . '_CLIENT_ID');
		$this->app_secret = env(strtoupper($this->provider) . '_SECRET');
	}

	public function getProviderEngine() {
		$provider_class = 'App\Classes\GitProviders\\' . ucfirst($this->provider);

		return new $provider_class($this->client_id, $this->app_secret);

	}
}