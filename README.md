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
// Most methods can take an array of either URLs or content IDs.
$images = [
  'http://playground.imagga.com/static/img/example_photo.jpg',
  'http://playground.imagga.com/static/img/example_photos/japan-605234_1280.jpg'
];

// Gets image tags
$tags = $imagga->tags($images);

// Get your current API usage levels
$usage = $imagga->usage();

// Get the colors of an image
$colors = $imagga->colors($images);
```
