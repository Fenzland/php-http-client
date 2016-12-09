<?php

namespace Fenzland\HTTP;

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
	 * Method __construct
	 *
	 * @access public
	 *
	 * @param  resource $handle
	 */
	public function __construct( $handle )
	{
		$this->handle= $handle;

		$header=$body= '';

		while( $line=trim(fgets($this->handle)) ){
			$header.= $line;
		}
		while( !feof($this->handle) ){
			$body.= fgets($this->handle);
		}

		echo $header.$body;
	}

}
