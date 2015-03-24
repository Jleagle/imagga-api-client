imagga-api-client
=================

A package to upload images to, and retrieve API information from Imagga.

Instantiate the package using your API details:

```php
$imagga = new Imagga(
  $apiKey,
  $apiSecret
);
```

Example API calls:

```php
// Most API calls can handle either an array of images or a single image.
$images = [
  'http://playground.imagga.com/static/img/example_photo.jpg',
  'http://playground.imagga.com/static/img/example_photos/japan-605234_1280.jpg'
];

// Gets image tags
$tags = $imagga->getTagsByUrl($images);

// Get your current API usage levels
$usage = $imagga->getUsage();

// Get the colors of an image
$colors = $imagga->getColorsByUrl($images);
```
