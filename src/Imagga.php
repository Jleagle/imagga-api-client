<?php
namespace Jleagle\Imagga;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Jleagle\Imagga\Exceptions\ImaggaException;
use Jleagle\Imagga\Helpers\QueryBuilder;

class Imagga
{
  /**
   * The URL to the API
   */
  const API = 'http://api.imagga.com/v1';

  /**
   * @var string
   */
  private $_apiKey;
  /**
   * @var string
   */
  private $_apiSecret;

  /**
   * @param string $apiKey
   * @param string $apiSecret
   */
  function __construct($apiKey, $apiSecret)
  {
    $this->_apiKey = $apiKey;
    $this->_apiSecret = $apiSecret;
  }

  /**
   * @return array
   */
  public function getUsage()
  {
    return $this->_get('/usage');
  }

  /**
   * @param string|string[] $imageUrl
   *
   * @return array
   *
   * @throws ImaggaException
   */
  public function getTagsByUrl($imageUrl)
  {
    if(!is_array($imageUrl))
    {
      $imageUrl = [$imageUrl];
    }

    if(count($imageUrl) > 10)
    {
      throw new ImaggaException('You can only request ten URLs');
    }

    $query = new QueryBuilder();
    foreach($imageUrl as $url)
    {
      $query->add('url', $url);
    }

    return $this->_get('/tagging?' . $query);
  }

  /**
   * @param string|string[] $contentId
   *
   * @return array
   *
   * @throws ImaggaException
   */
  public function getTagsByContentId($contentId)
  {
    if(!is_array($contentId))
    {
      $contentId = [$contentId];
    }

    if(count($contentId) > 30)
    {
      throw new ImaggaException('You can only request thirty URLs');
    }

    $query = new QueryBuilder();
    foreach($contentId as $content)
    {
      $query->add('content', $content);
    }

    return $this->_get('/tagging?' . $query);
  }

  /**
   * @param string          $category
   * @param string|string[] $imageUrl
   *
   * @return array
   */
  public function categorizeByUrl($category, $imageUrl)
  {
    if(!is_array($imageUrl))
    {
      $imageUrl = [$imageUrl];
    }

    $query = new QueryBuilder();
    foreach($imageUrl as $url)
    {
      $query->add('url', $url);
    }

    return $this->_get('/categorizations/' . $category . '?' . $query);
  }

  /**
   * @param string          $category
   * @param string|string[] $contentId
   *
   * @return array
   *
   * @throws ImaggaException
   */
  public function categorizeByContentId($category, $contentId)
  {
    if(!is_array($contentId))
    {
      $contentId = [$contentId];
    }

    if(count($contentId) > 30)
    {
      throw new ImaggaException('You can only request thirty URLs');
    }

    $query = new QueryBuilder();
    foreach($contentId as $content)
    {
      $query->add('content', $content);
    }

    return $this->_get('/categorizations/' . $category . '?' . $query);
  }

  /**
   * @return array
   */
  public function getCategories()
  {
    return $this->_get('/categorizers');
  }

  /**
   * @param string|string[] $imageUrl
   * @param string|string[] $resolution
   * @param bool            $allowScale
   *
   * @return array
   */
  public function cropByUrl($imageUrl, $resolution, $allowScale = false)
  {
    if(!is_array($imageUrl))
    {
      $imageUrl = [$imageUrl];
    }

    if(!is_array($resolution))
    {
      $resolution = [$resolution];
    }

    $query = new QueryBuilder();
    foreach($imageUrl as $url)
    {
      $query->add('url', $url);
    }
    $query->add('resolution', implode(',', $resolution));
    $query->add('no_scaling', $allowScale ? 0 : 1);

    return $this->_get('/croppings?' . $query);
  }

  /**
   * @param string|string[] $contentId
   * @param string|string[] $resolution
   * @param bool            $allowScale
   *
   * @return array
   *
   * @throws ImaggaException
   */
  public function cropByContentId($contentId, $resolution, $allowScale = false)
  {
    if(!is_array($contentId))
    {
      $contentId = [$contentId];
    }

    if(count($contentId) > 30)
    {
      throw new ImaggaException('You can only request thirty URLs');
    }

    if(!is_array($resolution))
    {
      $resolution = [$resolution];
    }

    $query = new QueryBuilder();
    foreach($contentId as $content)
    {
      $query->add('content', $content);
    }
    $query->add('resolution', implode(',', $resolution));
    $query->add('no_scaling', $allowScale ? 0 : 1);

    return $this->_get('/croppings?' . $query);
  }

  /**
   * @param string|string[] $imageUrl
   * @param bool            $extractOverallColors
   * @param bool            $extractObjectColors
   *
   * @return array
   */
  public function getColorsByUrl(
    $imageUrl, $extractOverallColors = true, $extractObjectColors = true
  )
  {
    if(!is_array($imageUrl))
    {
      $imageUrl = [$imageUrl];
    }

    $query = new QueryBuilder();
    foreach($imageUrl as $image)
    {
      $query->add('url', $image);
    }
    $query->add('extract_overall_colors', $extractOverallColors ? 1 : 0);
    $query->add('extract_object_colors', $extractObjectColors ? 1 : 0);

    return $this->_get('/colors?' . $query);
  }

  /**
   * @param string|string[] $contentId
   * @param bool            $extractOverallColors
   * @param bool            $extractObjectColors
   *
   * @return array
   *
   * @throws ImaggaException
   */
  public function getColorsByContentId(
    $contentId, $extractOverallColors = true, $extractObjectColors = true
  )
  {
    if(!is_array($contentId))
    {
      $contentId = [$contentId];
    }

    if(count($contentId) > 30)
    {
      throw new ImaggaException('You can only request thirty URLs');
    }

    $query = new QueryBuilder();
    foreach($contentId as $content)
    {
      $query->add('content', $content);
    }
    $query->add('extract_overall_colors', $extractOverallColors ? 1 : 0);
    $query->add('extract_object_colors', $extractObjectColors ? 1 : 0);

    return $this->_get('/colors?' . $query);
  }

  /**
   * @param string $url
   *
   * @return array
   */
  public function contentUpload($url)
  {
    $params = [
      'image' => "@{$url};filename=" . basename($url)
    ];

    return $this->_post('/content', $params);
  }

  /**
   * @param string $contentId
   *
   * @return array
   */
  public function contentDelete($contentId)
  {
    return $this->_delete('/content/' . $contentId);
  }

  /**
   * @param string $path
   * @param array  $params
   *
   * @return array
   * @throws ImaggaException
   */
  private function _get($path, $params = [])
  {
    $client = new Client();
    try
    {
      $res = $client->get(
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
   * @throws ImaggaException
   */
  private function _post($path, $params = [])
  {
    $client = new Client();
    try
    {
      $res = $client->post(
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
   * @throws ImaggaException
   */
  private function _delete($path)
  {
    $client = new Client();
    try
    {
      $res = $client->delete(
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
