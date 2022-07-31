<?php
/**
 * This file is part of the Cloudinary Media Management PHP package.
 *
 * (c) Cloudinary
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cloudinary\MediaManagement\Test\Helpers;

use Cloudinary\MediaManagement\Api\UploadApiClient;

/**
 * Class MockApiClient
 */
class MockUploadApiClient extends UploadApiClient
{
    use MockApiClientTrait;
}
