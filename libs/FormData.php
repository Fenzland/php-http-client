<?php

namespace FenzHTTP;

////////////////////////////////////////////////////////////////

class FormData
{

	/**
	 * Var boundary
	 *
	 * @access protected
	 *
	 * @var    string
	 */
	protected $boundary;

	/**
	 * Var data
	 *
	 * @access protected
	 *
	 * @var    array
	 */
	protected $data= [];

	/**
	 * Var files
	 *
	 * @access protected
	 *
	 * @var    array
	 */
	protected $files= [];

	/**
	 * Var request
	 *
	 * @access protected
	 *
	 * @var    Request
	 */
	protected $request;

	/**
	 * Method __construct
	 *
	 * @access public
	 *
	 * @param  array $data
	 */
	public function __construct( array$data=null )
	{
		$this->createBoundary();

		foreach( $data as $key=>$value )
		{
			$this->set( $key, "$value" );
		}
	}

	/**
	 * Method set
	 *
	 * @access public
	 *
	 * @param  string $key
	 * @param  string $value
	 *
	 * @return self
	 */
	public function set( string$key, string$value ):self
	{
		$this->data[$key]= $value;

		return $this;
	}

	/**
	 * Method setFile
	 *
	 * @access public
	 *
	 * @param  string $key
	 * @param  string $fileName
	 *
	 * @return self
	 */
	public function setFile( string$key, string$fileName ):self
	{
		if( !file_exists($fileName) || is_dir($fileName) || is_link($fileName) ){
			throw new Exception("$fileName is not a regular file.");
		}
		$this->files[$key]= [
			'fileName'=> $fileName,
			'contentType'=> mime_content_type($fileName),
			'content'=> file_get_contents($fileName),
		];

		return $this;
	}

	/**
	 * Method setFileRaw
	 *
	 * @access public
	 *
	 * @param  string $key
	 * @param  string $contentType
	 * @param  string $content
	 * @param  string $fileName
	 *
	 * @return self
	 */
	public function setFileRaw( string$key, string$contentType, string$content, string$fileName=null ):self
	{
		$this->files[$key]= [
			'fileName'=> $fileName??md5($content),
			'contentType'=> $contentType,
			'content'=> $content,
		];

		return $this;
	}

	/**
	 * Method setRequest
	 *
	 * @access public
	 *
	 * @param  Request $request
	 *
	 * @return void
	 */
	public function setRequest( Request$request )
	{
		$this->request= $request;
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
		$this->request->header( 'Content-Type:', "multipart/form-data, boundary={$this->boundary}" );

		$body= '';

		foreach( $this->data as $key=>$value )
		{
			$body.= "--{$this->boundary}\r\n"
			       ."Content-Disposition: form-data; name=\"{$key}\"\r\n\r\n"
			       ."{$value}\r\n";
		}

		foreach( $this->files as $key=>$value )
		{
			$body.= "--{$this->boundary}\r\n"
			       ."Content-Disposition: form-data; name=\"{$key}\"; filename=\"{$value['fileName']}\"\r\n"
			       ."Content-Type: {$value['contentType']}\r\n\r\n"
			       ."{$value['content']}\r\n";

		}

		$body.= "--{$this->boundary}--\r\n";

		return $body;
	}

	/**
	 * Method createBoundary
	 *
	 * @access private
	 *
	 * @return void
	 */
	private function createBoundary()
	{
		$this->boundary= str_repeat( '-', 0x10 ).md5(mt_rand(0,0x1000));
	}

}
