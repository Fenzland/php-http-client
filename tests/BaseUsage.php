<?php

require __DIR__.'/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Fenzland\HTTP\HTTP;

////////////////////////////////////////////////////////////////

class BaseUsage extends TestCase
{

	/**
	 * Method testUse
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function testUse()
	{
		HTTP::scheme('http')->host('fenzland.com')->port(80)->query('/')->headers([ 'key'=>'value', ])->method('GET')->version('1.1')->send();
	}

}
