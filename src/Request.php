<?php

namespace Fenzland\HTTP;

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
	protected $scheme= 'https';

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
	protected $port=-1;

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
	 * Var handle
	 *
	 * @access private
	 *
	 * @var    resource
	 */
	private $handle;

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

		return new Response($this->handle);
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
	 * Method stream
	 *
	 * @access public
	 *
	 * @return Stream
	 */
	public function stream():Stream
	{
		$this->sendHeader();

		return new StreamHandle($this->handle);
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

		if( in_array( $real, [ 'url', 'header', 'headers', 'method', ] ) )
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
		if( $length= strlen($body) )
		{
			$this->header( 'Content-Length', $length );
		}

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

		$this->handle= fsockopen( $this->host, $this->port );
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
		fclose($this->handle);
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
		fwrite( $this->handle, $content );
	}

}
