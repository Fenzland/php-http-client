<?php

namespace FenzHTTP;

////////////////////////////////////////////////////////////////

class HTTP
{

	/**
	 * Method __callStatic
	 *
	 * @access public
	 *
	 *
	 * @param  string $method
	 * @param  array $params
	 *
	 * @return mixed
	 */
	public static function __callStatic( string$method, array$params )
	{
		$request= new Request();

		try{
			return $request->$method( ...$params );
		}
		catch( \Exception$e )
		{
			throw new \Exception( 'Method '.static::class."::$method() is not exists." );
		}
	}

}
