<?php
/**
 * @name jsonrpc
 * @version 1.0-4
 * @description Communicates with JSON-RPC server
 * @depends dbglog (>= 1.0)
 **/
class JSONRPC
{
	private $debug;
	private $url;
	private $id;
	private $notification = false;

	public function __construct($url, $sessionId = false, $debug = false) {
		# server URL
		$this->url = $url;
		empty($proxy) ? $this->proxy = "" : $this->proxy = $proxy;
		empty($debug) ? $this->debug = false : $this->debug = true;
		$this->id = 1;
		$this->sessionId = $sessionId;
	}

	public function setRPCNotification($notification) {
		empty($notification) ? $this->notification = false : $this->notification = true;
	}

	public function call($method, $params, $getRaw = false) {
		if (!is_scalar($method)) {
			Dbg::log("Method name has no scalar value");
		}

		if (is_array($params)) {
			$params = array_values($params);
		} else {
			Dbg::log("Params must be given as array");
		}

		if ($this->notification) {
			$currentId = NULL;
		} else {
			$currentId = $this->id;
		}

		$request = array(
			"method" => $method,
			"params" => $params,
			"id" => $currentId
		);

		if ($this->sessionId) $sessionId = $this->sessionId;

		if ($sessionId) {
			$request["context"] = array("session" => $sessionId);
		}

		$request = json_encode($request);

		$opts = array ("http" => array (
			"method" => "POST",
			"header" => "Content-type: application/json; charset=utf-8",
			"content" => $request
		));

		if ($this->debug) {
			Dbg::log($request);
		}

		$context = stream_context_create($opts);
		if ($fp = fopen($this->url, "r", false, $context)) {
			$response = "";
			while($row = fgets($fp)) {
				$response.= trim($row)."\n";
			}
			$response = json_decode($response, true);
		} else {
			Dbg::log("Unable to connect to ".$this->url);
		}

		if (!$this->notification && isset($response)) {
			if ($response["id"] != $currentId) {
				Dbg::log("Incorrect response id (request id: ".$currentId.", response id: ".$response["id"].")");
			}

			if ($getRaw) return $response;

			# V případě chyby vrátíme false
			if ($response["error"]) {
				$err = $response["error"];
				Dbg::log(sprintf("[error] status: %s | statusMessage: %s", $err["status"], $err["statusMessage"]));
			}

			return $response["result"];
		} else {
			return true;
		}
	}
}
?>
