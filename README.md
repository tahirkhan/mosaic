Mosaic
============

What is it?
--------------------------------------

A small drop-in file to turn boring default directory list into a beautiful list of images. It generates smaller thumbnails for preview and stores locally.

Getting Started
--------------------------------------

Installation
--------------------------------------

- [Download](https://github.com/tahirkhan/mosaic/archive/master.zip) and extract index.php
- Drop it in your folder of images

Authentication
--------------------------------------

#### Step 1. set $isRestricted to true

```php
$isRestricted = TRUE;
```

#### Step 2. set users and passwords

```php
// use the following format for adding mutiple users 
// user => password
$users = array("myuser" => "secret");
```

Thumbnail Sizes
--------------------------------------

you can use $imageWidth and $imageHeight to control the default thumbnail sizes
