<?php
namespace Jleagle\Imagga;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Post\PostFile;
use Jleagle\Imagga\Exceptions\ImaggaException;
use Jleagle\Imagga\Helpers\QueryBuilder;

class Imagga
{
  /**
   * The URL to the API
   */
  const API = 'http://api.imagga.com/v1/';

  /**
   * @var string
   */
  private $_apiKey;

  /**
   * @var string
   */
  private $_apiSecret;

  /**
   * @var Client
   */
  private $_client;

  /**
   * @param string $apiKey
   * @param string $apiSecret
   */
  public function __construct($apiKey, $apiSecret)
  {
    $this->_apiKey = $apiKey;
    $this->_apiSecret = $apiSecret;

    $this->_client = new Client(['base_url' => self::API]);
  }

  /**
   * @return array
   */
  public function usage()
  {
    return $this->_get('usage');
  }

  /**
   * @param string|string[] $image
   *
   * @return array
   *
   * @throws ImaggaException
   */
  public function tags($image)
  {
    if(!is_array($image))
    {
      $image = [$image];
    }

    $query = new QueryBuilder();
    $urls = $contents = 0;
    foreach($image as $v)
    {
      if($this->_isContentId($v))
      {
        $contents++;
        $query->add('content', $v);
      }
      else
      {
        $urls++;
        $query->add('url', $v);
      }
    }

    if($urls > 10)
    {
      throw new ImaggaException('You can only request ten URLs');
    }

    if($contents > 30)
    {
      throw new ImaggaException('You can only request thirty content IDs');
    }

    return $this->_get('tagging?' . $query);
  }

  /**
   * @param string          $category
   * @param string|string[] $image
   *
   * @throws ImaggaException
   *
   * @return array
   */
  public function categorize($category, $image)
  {
    if(!is_array($image))
    {
      $image = [$image];
    }

    $query = new QueryBuilder();
    $contents = 0;
    foreach($image as $v)
    {
      if($this->_isContentId($v))
      {
        $contents++;
        $query->add('content', $v);
      }
      else
      {
        $query->add('url', $v);
      }
    }

    if($contents > 30)
    {
      throw new ImaggaException('You can only request thirty content IDs');
    }

    return $this->_get('categorizations/' . $category . '?' . $query);
  }

  /**
   * @return array
   */
  public function categories()
  {
    return $this->_get('categorizers');
  }

  /**
   * @param string|string[] $image
   * @param string|string[] $resolution
   * @param bool            $scale
   *
   * @throws ImaggaException
   *
   * @return array
   */
  public function crop($image, $resolution, $scale = false)
  {
    if(!is_array($image))
    {
      $image = [$image];
    }

    if(!is_array($resolution))
    {
      $resolution = [$resolution];
    }

    $query = new QueryBuilder();
    $contents = 0;
    foreach($image as $v)
    {
      if($this->_isContentId($v))
      {
        $contents++;
        $query->add('content', $v);
      }
      else
      {
        $query->add('url', $v);
      }
    }
    $query->add('resolution', implode(',', $resolution));
    $query->add('no_scaling', $scale ? 0 : 1);

    if($contents > 30)
    {
      throw new ImaggaException('You can only request thirty content IDs');
    }

    return $this->_get('croppings?' . $query);
  }

  /**
   * @param string|string[] $image
   * @param bool            $extractOverallColors
   * @param bool            $extractObjectColors
   *
   * @throws ImaggaException
   *
   * @return array
   */
  public function colors(
    $image, $extractOverallColors = true, $extractObjectColors = true
  )
  {
    if(!is_array($image))
    {
      $image = [$image];
    }

    $query = new QueryBuilder();
    $contents = 0;
    foreach($image as $v)
    {
      if($this->_isContentId($v))
      {
        $contents++;
        $query->add('content', $v);
      }
      else
      {
        $query->add('url', $v);
      }
    }
    $query->add('extract_overall_colors', $extractOverallColors ? 1 : 0);
    $query->add('extract_object_colors', $extractObjectColors ? 1 : 0);

    if($contents > 30)
    {
      throw new ImaggaException('You can only request thirty content IDs');
    }

    return $this->_get('colors?' . $query);
  }

  /**
   * @param string $data
   * @param string $filename
   *
   * @throws ImaggaException
   *
   * @return array
   */
  public function upload($data, $filename = null)
  {
    if(is_array($data))
    {
      throw new ImaggaException('You can only request one URL');
    }

    if(!$filename)
    {
      $filename = time() . '.png';
    }

    $params = [
      'image' => new PostFile($filename, $data)
    ];

    return $this->_post('content', $params);
  }

  /**
   * @param string $contentId
   *
   * @return array
   */
  public function delete($contentId)
  {
    return $this->_delete('content/' . $contentId);
  }

  private function _isContentId($contentId)
  {
    return is_string($contentId) && strlen($contentId) == 32;
  }

  /**
   * @param string $path
   * @param array  $params
   *
   * @return array
   *
   * @throws ImaggaException
   */
  private function _get($path, $params = [])
  {
    try
    {
      $res = $this->_client->get(
        self::API . $path,
        [
          'auth'  => [$this->_apiKey, $this->_apiSecret],
          'query' => $params
        ]
      );
    }
    catch(ClientException $e)
    {
      throw new ImaggaException($e->getResponse()->json()['message']);
    }

    return $res->json();
  }

  /**
   * @param string $path
   * @param array  $params
   *
   * @return array
   *
   * @throws ImaggaException
   */
  private function _post($path, $params = [])
  {
    try
    {
      $res = $this->_client->post(
        self::API . $path,
        [
          'auth' => [$this->_apiKey, $this->_apiSecret],
          'body' => $params
        ]
      );
    }
    catch(ClientException $e)
    {
      throw new ImaggaException($e->getResponse()->json()['message']);
    }

    return $res->json();
  }

  /**
   * @param string $path
   *
   * @return array
   *
   * @throws ImaggaException
   */
  private function _delete($path)
  {
    try
    {
      $res = $this->_client->delete(
        self::API . $path,
        [
          'auth' => [$this->_apiKey, $this->_apiSecret],
        ]
      );
    }
    catch(ClientException $e)
    {
      throw new ImaggaException($e->getResponse()->json()['message']);
    }

    return $res->json();
  }
}
