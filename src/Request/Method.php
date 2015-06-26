<?php

namespace Improv\Http\Request;

class Method {

  const HEAD    = 'HEAD';
  const OPTIONS = 'OPTIONS';

  const GET     = 'GET';

  const POST    = 'POST';
  const PUT     = 'PUT';
  const PATCH   = 'PATCH';

  const DELETE  = 'DELETE';
  const PURGE   = 'PURGE';

  const TRACE   = 'TRACE';
  const CONNECT = 'CONNECT';

  public static function isValid( $method ) {

    static $cache_map = null;

    if ( $cache_map === null ) {
      $class     = new \ReflectionClass( get_called_class() );
      $cache_map = array_values( $class->getConstants() );
    }

    return in_array( $method, $cache_map );

  }

}
