<?php 
namespace Contao;
if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  360fusion  2011
 * @author     Darrell Martin <darrell@360fusion.co.uk>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Handle Paypal HTTPRequests
 * 
 * @extends Payment
 */
 
 
class HTTPRequest 
{

	private $host;
	private $path;
	private $data;
	private $method;
	private $port;
	private $rawhost;

	private $header;
	private $content;
	private $parsedHeader;

	function __construct($host, $path, $method = 'POST', $ssl = false, $port = 0) {
		$this->host = $host;
		$this->rawhost = $ssl ? ("ssl://".$host) : $host;
		$this->path = $path;
		$this->method = strtoupper($method);
		if ($port) {
			$this->port = $port;
		} else {
			if (!$ssl) $this->port = 80; else $this->port = 443;
		}
	}

	public function connect( $data = ''){
		$fp = fsockopen($this->rawhost, $this->port);
		if (!$fp) return false;
		fputs($fp, "$this->method $this->path HTTP/1.1\r\n");
		fputs($fp, "Host: $this->host\r\n");
		//fputs($fp, "Content-type: $contenttype\r\n");
		fputs($fp, "Content-length: ".strlen($data)."\r\n");
		fputs($fp, "Connection: close\r\n");
		fputs($fp, "\r\n");
		fputs($fp, $data);

		$responseHeader = '';
		$responseContent = '';

		do
		{
			$responseHeader.= fread($fp, 1);
		}
		while (!preg_match('/\\r\\n\\r\\n$/', $responseHeader));
			
			
		if (!strstr($responseHeader, "Transfer-Encoding: chunked"))
		{
			while (!feof($fp))
			{
				$responseContent.= fgets($fp, 128);
			}
		}
		else
		{

			while ($chunk_length = hexdec(fgets($fp))) {
				$responseContentChunk = '';
				 
				$read_length = 0;
				 
				while ($read_length < $chunk_length)
				{
					$responseContentChunk .= fread($fp, $chunk_length - $read_length);
					$read_length = strlen($responseContentChunk);
				}

				$responseContent.= $responseContentChunk;
				 
				fgets($fp);
				 
			}
			 
		}

		$this->header = chop($responseHeader);
		$this->content = $responseContent;
		$this->parsedHeader = $this->headerParse();
		
		$code = intval(trim(substr($this->parsedHeader[0], 9)));

		return $code;
	}

	function headerParse(){
		$h = $this->header;
		$a=explode("\r\n", $h);
		$out = array();
		foreach ($a as $v){
			$k = strpos($v, ':');
			if ($k) {
				$key = trim(substr($v,0,$k));
				$value = trim(substr($v,$k+1));
				if (!$key) continue;
				$out[$key] = $value;
			} else
			{
				if ($v) $out[] = $v;
			}
		}
		return $out;
	}

	public function getContent() {return $this->content;}
	public function getHeader() {return $this->parsedHeader;}	

}
?>