<?php

namespace FenzHTTP;

////////////////////////////////////////////////////////////////

class Request
{

	/**
	 * Var scheme
	 *
	 * @access protected
	 *
	 * @var    string
	 */
	protected $scheme= 'http';

	/**
	 * Var host
	 *
	 * @access protected
	 *
	 * @var    string
	 */
	protected $host;

	/**
	 * Var port
	 *
	 * @access protected
	 *
	 * @var    int
	 */
	protected $port;

	/**
	 * Var user
	 *
	 * @access protected
	 *
	 * @var    string
	 */
	protected $user;

	/**
	 * Var pass
	 *
	 * @access protected
	 *
	 * @var    string
	 */
	protected $pass;

	/**
	 * Var path
	 *
	 * @access protected
	 *
	 * @var    string
	 */
	protected $path= '/';

	/**
	 * Var query
	 *
	 * @access protected
	 *
	 * @var    string
	 */
	protected $query;

	/**
	 * Var fragment
	 *
	 * @access protected
	 *
	 * @var    string
	 */
	protected $fragment;

	/**
	 * Var method
	 *
	 * @access protected
	 *
	 * @var    string
	 */
	protected $method= 'GET';

	/**
	 * Var version
	 *
	 * @access protected
	 *
	 * @var    string
	 */
	protected $version= '1.1';

	/**
	 * Var headers
	 *
	 * @access protected
	 *
	 * @var    array
	 */
	protected $headers= [];

	/**
	 * Var body
	 *
	 * @access protected
	 *
	 * @var    string
	 */
	protected $body= '';

	/**
	 * Var handle
	 *
	 * @access private
	 *
	 * @var    resource
	 */
	private $handle;

	/**
	 * Var raw
	 *
	 * @access private
	 *
	 * @var    string
	 */
	private $raw;

	/**
	 * Method __destruct
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function __destruct()
	{
		$this->closeHandle();
	}

	/**
	 * Method version
	 *
	 * @access public
	 *
	 * @param  string $version
	 *
	 * @return self
	 */
	public function version( string$version ):self
	{
		$this->version= $version;

		return $this;
	}

	/**
	 * Method url
	 *
	 * @access public
	 *
	 * @param  string $url
	 *
	 * @return self
	 */
	public function url( string$url ):self
	{
		$url= parse_url($url);

		if( !is_array($url) )
			throw new \Exception("Url '$url' is invalid.");

		foreach( $url as $key=>$value )
		{
			$this->$key= $value;
		}

		return $this;
	}

	/**
	 * Method method
	 *
	 * @access public
	 *
	 * @param  string $method
	 *
	 * @return self
	 */
	public function method( string$method ):self
	{
		$this->method= strtoupper($method);

		return $this;
	}

	/**
	 * Method headers
	 *
	 * @access public
	 *
	 * @param  array $headers
	 *
	 * @return self
	 */
	public function headers( array$headers ):self
	{
		foreach( $headers as $key=>$value ){
			$this->header( $key, $value );
		}

		return $this;
	}

	/**
	 * Method header
	 *
	 * @access public
	 *
	 *
	 * @param  string $key
	 * @param  string $value
	 *
	 * @return self
	 */
	public function header( string$key, string$value ):self
	{
		$this->headers[ucwords( strtolower($key), '-' )]= $value;

		return $this;
	}

	/**
	 * Method send
	 *
	 * @access public
	 *
	 * @param  string $body
	 *
	 * @return Response
	 */
	public function send( string$body=null ):Response
	{
		$this->justSend($body);

		return new Response( $this, $this->handle );
	}

	/**
	 * Method sendFile
	 *
	 * @access public
	 *
	 * @param  FormData $body
	 *
	 * @return Response
	 */
	public function sendFormData( FormData$body ):Response
	{
		if(!( in_array( $this->method, [ 'POST', 'PUT', 'PATCH', ] ) )){
			throw new \Exception('FormData must be send with method POST, PUT or PATCH');
		}

		$body->setRequest( $this );

		return $this->send("$body");
	}

	/**
	 * Method sendAndClose
	 *
	 * @access public
	 *
	 * @param  string $body
	 *
	 * @return void
	 */
	public function sendAndClose( string$body=null )
	{
		$this->justSend($body);

		$this->closeHandle();
	}

	/**
	 * Method __call
	 *
	 * @access public
	 *
	 * @param  string $method
	 * @param  array $params
	 *
	 * @return self
	 */
	public function __call( string$method, array$params ):self
	{
		$real= 0===strpos( $method, 'with' )? lcfirst(substr( $method, 4 )) : $method;

		if( empty($params) )
		{
			$method= static::class."::$method()";

			throw new \Exception("Param 1 of $method is required.");
		}

		if( in_array( $real, [ 'version', 'url', 'header', 'headers', 'method', ] ) )
		{
			return $this->$real( ...$params );
		}
		elseif( property_exists( $this, $real ) && !in_array( $real, [ 'handle', ] ) )
		{
			$this->$real= $params[0];

			return $this;
		}else{
			$method= static::class."::$method()";

			throw new \Exception( "Method $method is not exists." );
		}
	}

	/**
	 * Method get
	 *
	 * @access public
	 *
	 * @return Response
	 */
	public function get():Response
	{
		return $this->method('GET')->send();
	}

	/**
	 * Method head
	 *
	 * @access public
	 *
	 * @return Response
	 */
	public function head():Response
	{
		return $this->method('HEAD')->send();
	}

	/**
	 * Method delete
	 *
	 * @access public
	 *
	 * @return Response
	 */
	public function delete():Response
	{
		return $this->method('DELETE')->send();
	}

	/**
	 * Method post
	 *
	 * @access public
	 *
	 * @param  string $body
	 *
	 * @return Response
	 */
	public function post( string$body ):Response
	{
		return $this->method('POST')->send($body);
	}

	/**
	 * Method postFields
	 *
	 * @access public
	 *
	 * @param  mixed $fields
	 *
	 * @return Response
	 */
	public function postFields( $fields ):Response
	{
		return $this->header( 'Content-Type', 'application/x-www-form-urlencoded' )->method('POST')->send(http_build_query($fields));
	}

	/**
	 * Method put
	 *
	 * @access public
	 *
	 * @param  string $body
	 *
	 * @return Response
	 */
	public function put( string$body ):Response
	{
		return $this->method('PUT')->send($body);
	}

	/**
	 * Method putFields
	 *
	 * @access public
	 *
	 * @param  mixed $fields
	 *
	 * @return Response
	 */
	public function putFields( $fields ):Response
	{
		return $this->header( 'Content-Type', 'application/x-www-form-urlencoded' )->method('PUT')->send(http_build_query($fields));
	}

	/**
	 * Method patch
	 *
	 * @access public
	 *
	 * @param  string $body
	 *
	 * @return Response
	 */
	public function patch( string$body ):Response
	{
		return $this->method('Patch')->send($body);
	}

	/**
	 * Method patchFields
	 *
	 * @access public
	 *
	 * @param  mixed $fields
	 *
	 * @return Response
	 */
	public function patchFields( $fields ):Response
	{
		return $this->header( 'Content-Type', 'application/x-www-form-urlencoded' )->method('PATCH')->send(http_build_query($fields));
	}

	/**
	 * Method options
	 *
	 * @access public
	 *
	 * @return Response
	 */
	public function options():Response
	{
		return $this->method('OPTIONS')->send();
	}

	/**
	 * Method justSend
	 *
	 * @access protected
	 *
	 * @param  string $body
	 *
	 * @return void
	 */
	protected function justSend( string$body=null )
	{
		$this->body= $body;

		if( $length= strlen($body) )
		{
			$this->header( 'Content-Length', $length );
		}

		$this->header( 'Connection', 'close' );

		$this->sendHeader();

		$this->sendBody($body);
	}

	/**
	 * Method sendHeader
	 *
	 * @access protected
	 *
	 * @return void
	 */
	protected function sendHeader()
	{
		$this->openHandle();

		$target= $this->path.($this->query? '?'.$this->query : '' ).($this->fragment? '#'.$this->fragment : '' );

		$this->writeLine("{$this->method} {$target} HTTP/{$this->version}");
		$this->writeLine("Host: {$this->host}");

		foreach( $this->headers as $key=>$value ){
			$this->writeLine("$key: $value");
		}
		$this->writeLine('');
	}

	/**
	 * Method openHandle
	 *
	 * @access private
	 *
	 * @return void
	 */
	private function openHandle()
	{
		if( !$this->host )
			throw new \Exception('Host is not defined.');

		$this->handle= ($this->scheme==='https'?
			fsockopen( 'ssl://'.$this->host, $this->port?:443 )
		:
			fsockopen( $this->host, $this->port?:80 )
		);
	}

	/**
	 * Method closeHandle
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function closeHandle()
	{
		if( $this->handle ){
			fclose($this->handle);

			$this->handle= null;
		}
	}

	/**
	 * Method sendBody
	 *
	 * @access public
	 *
	 * @param  string $body
	 *
	 * @return void
	 */
	public function sendBody( string$body=null )
	{
		isset($body) and $this->write($body);
	}

	/**
	 * Method __toString
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function __toString():string
	{
		return $this->raw;
	}

	/**
	 * Method writeLine
	 *
	 * @access private
	 *
	 * @param  string $line
	 *
	 * @return void
	 */
	private function writeLine( string$line )
	{
		$this->write("$line\r\n");
	}

	/**
	 * Method write
	 *
	 * @access private
	 *
	 * @param  string $content
	 *
	 * @return void
	 */
	private function write( string$content )
	{
		$this->raw.= $content;

		fwrite( $this->handle, $content );
	}

}
