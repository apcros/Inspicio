<?php
namespace App\Classes;

class UserAgent {
	private $curl;

	private $headers;

	public function __construct() {
		$this->curl    = curl_init();
		$this->headers = array();
		$this->setOpt(CURLOPT_RETURNTRANSFER, 1);
	}

	public function addHeader($header) {
		$this->headers[] = $header;
		$this->setOpt(CURLOPT_HTTPHEADER, $this->headers);
	}

	public function get($url, $get_headers = false) {

		$this->setOpt(CURLOPT_HTTPGET, 1);
		$this->setOpt(CURLOPT_URL, $url);

		if (!$get_headers) {
			return $this->do_curl();
		}

		$this->setOpt(CURLOPT_HEADER, 1);
		$response = $this->do_curl();

		$header_size = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);

		return $this->parseHeadersBody($response, $header_size);

	}

	public function parseHeadersBody($response, $header_size) {
		$headers = substr($response, 0, $header_size);
		$body    = substr($response, $header_size);

		$headers_lines = explode("\n", $headers);

		$headers = [];

		foreach ($headers_lines as $header_line) {
			$header_parts = explode(': ', $header_line);

			if (count($header_parts) == 2) {
				$headers[$header_parts[0]] = str_replace(["\n", "\r"], '', $header_parts[1]);
			}

		}

		return [
			'headers' => $headers,
			'body'    => $body,
		];
	}

	private function custom_http_verb_request($url, $data, $verb) {
		$this->setOpt(CURLOPT_CUSTOMREQUEST, $verb);
		$this->setOpt(CURLOPT_URL, $url);
		$this->setOpt(CURLOPT_POSTFIELDS, $data);

		return $this->do_curl();
	}

	public function patch($url, $data) {
		return $this->custom_http_verb_request($url, $data, 'PATCH');
	}

	public function put($url, $data) {
		return $this->custom_http_verb_request($url, $data, 'PUT');
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
