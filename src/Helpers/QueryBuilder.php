<?php
namespace Jleagle\Imagga\Helpers;

class QueryBuilder
{
  private $_parts = [];

  public function add($key, $value)
  {
    $this->_parts[] = [
      'key' => $key,
      'value' => $value
    ];
  }

  private function _build($separator = '&', $equals = '=')
  {
    $queryString = [];

    foreach($this->_parts as $part)
    {
      $queryString[] =
        urlencode($part['key']) . $equals . urlencode($part['value']);
    }

    return implode($separator, $queryString);
  }

  public function __toString()
  {
    return $this->_build();
  }
}
