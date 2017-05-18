<?php
namespace App\Classes;

class UserAgent {
	private $curl;

	private $headers;

	public function __construct() {
		$this->curl = curl_init();
		$this->headers = array();
		$this->setOpt(CURLOPT_RETURNTRANSFER, 1);
	}

	public function addHeader($header) {
		$this->headers[] = $header;
		$this->setOpt(CURLOPT_HTTPHEADER, $this->headers);
	}

	public function get($url) {

		$this->setOpt(CURLOPT_HTTPGET, 1);
		$this->setOpt(CURLOPT_URL, $url);

		return $this->do_curl();
	}

	public function getHeaders() {
		return $this->headers;
	}

	/*
	Takes $data and post it to $url using curl
	 */
	public function post($url, $data) {

		$this->setOpt(CURLOPT_POST, 1);
		$this->setOpt(CURLOPT_POSTFIELDS, $data);
		$this->setOpt(CURLOPT_URL, $url);

		return $this->do_curl();
	}

	public function setHeaders($headers) {
		$this->headers = $headers;
		$this->setOpt(CURLOPT_HTTPHEADER, $this->headers);
	}

	private function do_curl() {
		$raw_result = curl_exec($this->curl);

		if ($raw_result === FALSE) {
			throw new \Exception(curl_error($this->curl) . ' - ' . curl_errno($this->curl), 1);
		}

		return $raw_result;
	}

	/*
	A short cut to avoid having to repass the curl handler
	 */
	private function setOpt($option, $val) {
		curl_setopt($this->curl, $option, $val);
	}

}
