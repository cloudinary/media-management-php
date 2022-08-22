[![Build Status](https://github.com/cloudinary/media-management-php/actions/workflows/test.yaml/badge.svg)](https://github.com/cloudinary/media-management-php/actions/workflows/test.yaml)
[![license](https://img.shields.io/github/license/cloudinary/media-management-php.svg?maxAge=2592000)](https://github.com/cloudinary/media-management-php/blob/master/LICENSE)
[![Packagist](https://img.shields.io/packagist/v/cloudinary/media-management.svg?maxAge=2592000)](https://packagist.org/packages/cloudinary/media-management)
[![Packagist](https://img.shields.io/packagist/dt/cloudinary/media-management.svg?maxAge=2592000)](https://packagist.org/packages/cloudinary/media-management/stats)

Cloudinary Media Management PHP SDK
==================
## About
The Cloudinary Media Management PHP SDK allows you to quickly and easily integrate your application with Cloudinary.
Effortlessly upload and manage your cloud's assets.


#### Note
This Readme provides basic installation and usage information.
For the complete documentation, see the [Media Management SDK Guide](https://cloudinary.com/documentation/media_management_api).

## Table of Contents
- [Key Features](#key-features)
- [Version Support](#Version-Support)
- [Installation](#installation)
- [Usage](#usage)
    - [Setup](#Setup)
    - [Upload](#Upload)


## Key Features
- [Asset Management](https://cloudinary.com/documentation/media_management_asset_management).


## Version Support
| SDK Version | PHP < 7.3 | PHP 7.4 | PHP 8.x |
|-------------|-----------|---------|---------|
| 0.x         | x         | v       | v       |


## Installation
```bash
composer require "cloudinary/media-management"
```

# Usage

### Setup
```php
use Cloudinary\MediaManagement\Cloudinary;

$cloudinary = new Cloudinary();
```

### Upload
- [See full documentation](https://cloudinary.com/documentation/media_management_upload_api_reference).

```php
$cloudinary->uploadApi->upload('my_image.jpg');
```

## Contributions
- Ensure tests run locally
- Open a PR and ensure Travis tests pass


## Get Help
If you run into an issue or have a question, you can either:
- Issues related to the SDK: [Open a GitHub issue](https://github.com/cloudinary/media-management-php/issues).
- Issues related to your account: [Open a support ticket](https://cloudinary.com/contact)

## About Cloudinary
Cloudinary is a powerful media API for websites and mobile apps alike, Cloudinary enables developers to efficiently 
manage, transform, optimize, and deliver images and videos through multiple CDNs. Ultimately, viewers enjoy responsive 
and personalized visual-media experiences—irrespective of the viewing device.

## Licence
Released under the MIT license.
