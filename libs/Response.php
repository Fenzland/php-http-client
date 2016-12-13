<?php

namespace FenzHTTP;

////////////////////////////////////////////////////////////////

class Response
{

	/**
	 * Var handle
	 *
	 * @access protected
	 *
	 * @var    resource
	 */
	protected $handle;

	/**
	 * Var version
	 *
	 * @access protected
	 *
	 * @var    string
	 */
	protected $version= '1.1';

	/**
	 * Var statusCode
	 *
	 * @access protected
	 *
	 * @var    int
	 */
	protected $statusCode= 100;

	/**
	 * Var statusMessage
	 *
	 * @access protected
	 *
	 * @var    string
	 */
	protected $statusMessage= '';

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
	 * Method __construct
	 *
	 * @access public
	 *
	 * @param  resource $handle
	 */
	public function __construct( $handle )
	{
		$this->handle= $handle;

		$this->parsMessageLine(trim(fgets($this->handle)));

		while( $headerLine= trim(fgets($this->handle)) ){
			$this->parseHeader($headerLine);
		}

		$this->readBody();
	}

	/**
	 * Method parsMessageLine
	 *
	 * @access private
	 *
	 * @param  string $messageLine
	 *
	 * @return void
	 */
	private function parsMessageLine( string$messageLine )
	{
		list( $this->version, $this->statusCode, $this->statusMessage )= explode( ' ', str_replace( 'HTTP/', '', $messageLine ) );
	}

	/**
	 * Method parseHeader
	 *
	 * @access private
	 *
	 * @param  string $headerLine
	 *
	 * @return void
	 */
	private function parseHeader( string$headerLine )
	{
		list( $key, $value, )= explode(': ', $headerLine);

		$this->headers[ucwords( strtolower($key), '-' )]= $value;
	}

	/**
	 * Method readBody
	 *
	 * @access private
	 *
	 * @return void
	 */
	private function readBody()
	{
		$odd= true;

		while( !feof($this->handle) )
		{
			$line= fgets($this->handle);

			if( substr( $line, -2 )==="\r\n" )
			{
				if( !$odd )
				{
					$this->body.= substr( $line, 0, -2 );
				}

				$odd= !$odd;
			}else{
				$this->body.= $line;
			}
		}
	}

}
