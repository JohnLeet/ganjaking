<?php
/**
 * Based on TinyHttp from https://gist.github.com/618157.
 * Copyright 2011, Neuman Vong. BSD License.
 */

class Services_Twilio_TinyHttpException extends ErrorException {}

/**
 * An HTTP client that makes requests
 *
 * :param string $uri: The base uri to use for requests
 * :param array $kwargs: An array of additional arguments to pass to the
 *  library. Accepted arguments are:
 *
 *      - **debug** - Print the HTTP request before making it to Twilio
 *      - **curlopts** - An array of keys and values that are passed to
 *          ``curl_setopt_array``.
 *
 * Here's an example. This is the default HTTP client used by the library.
 *
 * .. code-block:: php
 *
 *     $_http = new Services_Twilio_TinyHttp(
 *         "https://api.twilio.com",
 *         array("curlopts" => array(
 *             CURLOPT_USERAGENT => self::USER_AGENT,
 *             CURLOPT_HTTPHEADER => array('Accept-Charset: utf-8'),
 *             CURLOPT_CAINFO => dirname(__FILE__) . '/cacert.pem',
 *         ))
 *     );
 */
class Services_Twilio_TinyHttp {
	public $user; 
		public $pass;
		public $scheme;
		public $host;
		public $port; 
		public $debug;
		public $curlopts;

	public function __construct( $uri = '', $kwargs = array()) {
		foreach (parse_url($uri) as $name => $value) {
			$this->$name = $value;
		}
		$this->debug = isset($kwargs['debug']) ? !!$kwargs['debug'] : null;
		$this->curlopts = isset($kwargs['curlopts']) ? $kwargs['curlopts'] : array();
	}

	public function __call( $name, $args) {
		list($res, $req_headers, $req_body) = $args + array(0, array(), '');

		$opts = $this->curlopts + array(
		CURLOPT_URL => "$this->scheme://$this->host$res",
		CURLOPT_HEADER => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_INFILESIZE => -1,
		CURLOPT_POSTFIELDS => null,
		CURLOPT_TIMEOUT => 60,
		);

		foreach ($req_headers as $k => $v) {
			$opts[CURLOPT_HTTPHEADER][] = "$k: $v";
		}
		if ($this->port) {
			$opts[CURLOPT_PORT] = $this->port;
		}
		if ($this->debug) {
			$opts[CURLINFO_HEADER_OUT] = true;
		}
		if ($this->user && $this->pass) {
			$opts[CURLOPT_USERPWD] = "$this->user:$this->pass";
		}
		switch ($name) {
			case 'get':
				  $opts[CURLOPT_HTTPGET] = true;
				break;
			case 'post':
				$opts[CURLOPT_POST] = true;
				$opts[CURLOPT_POSTFIELDS] = $req_body;
				break;
			case 'put':
				$opts[CURLOPT_PUT] = true;
				if (strlen($req_body)) {
										$buf = fopen('php://memory', 'w+');
					if ($buf) {
						fwrite($buf, $req_body);
						fseek($buf, 0);
						$opts[CURLOPT_INFILE] = $buf;
						$opts[CURLOPT_INFILESIZE] = strlen($req_body);
					} else {
						throw new Services_Twilio_TinyHttpException('unable to open temporary file');
					}
				}
				break;
			case 'head':
				$opts[CURLOPT_NOBODY] = true;
				break;
			default:
				$opts[CURLOPT_CUSTOMREQUEST] = strtoupper($name);
				break;
		}
		try {
						$curl = curl_init();
			if ($curl) {
				if (curl_setopt_array($curl, $opts)) {
										$response = curl_exec($curl);
					if ($response) {
						$parts = explode("\r\n\r\n", $response, 3);
						list($head, $body) = ( 'HTTP/1.1 100 Continue' == $parts[0] )
						? array($parts[1], $parts[2])
						: array($parts[0], $parts[1]);
						$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
						if ($this->debug) {
							  error_log(
							curl_getinfo($curl, CURLINFO_HEADER_OUT) .
							$req_body
								  );
						}
						$header_lines = explode("\r\n", $head);
						array_shift($header_lines);
						foreach ($header_lines as $line) {
							list($key, $value) = explode(':', $line, 2);
							$headers[$key] = trim($value);
						}
						curl_close($curl);
						if (isset($buf) && is_resource($buf)) {
							fclose($buf);
						}
						return array($status, $headers, $body);
					} else {
						throw new Services_Twilio_TinyHttpException(curl_error($curl));
					}
				} else {
					throw new Services_Twilio_TinyHttpException(curl_error($curl));
				}
			} else {
				throw new Services_Twilio_TinyHttpException('unable to initialize cURL');
			}
		} catch (ErrorException $e) {
			if (is_resource($curl)) {
				curl_close($curl);
			}
			if (isset($buf) && is_resource($buf)) {
				fclose($buf);
			}
			throw $e;
		}
	}

	public function authenticate( $user, $pass) {
		$this->user = $user;
		$this->pass = $pass;
	}
}
