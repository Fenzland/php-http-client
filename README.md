Fenzland HTTP
================================

A lightweight HTTP client with PHP7.


# Usage

```php

HTTP::scheme('http')->host('example.org')->port(80)->query('/example/')->headers([ 'key'=>'value', ])->method('GET')->version('1.1')->send();
HTTP::withScheme('http')->withHost('example.org')->withPort(80)->withQuery('/example/')->withHeaders([ 'key'=>'value', ])->withMethod('GET')->withVersion('1.1')->send();

HTTP::url('http://example.org/example/?key=value')->get();
HTTP::url('http://example.org/example/?key=value')->head();
HTTP::url('http://example.org/example/?key=value')->option();
HTTP::url('http://example.org/example/?key=value')->post(  'id=1&name=Fenz' );
HTTP::url('http://example.org/example/?key=value')->put(   'id=1&name=Fenz' );
HTTP::url('http://example.org/example/?key=value')->patch( 'id=1&name=Fenz' );
```
