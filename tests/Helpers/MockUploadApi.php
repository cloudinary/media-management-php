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

use Cloudinary\MediaManagement\Api\Upload\UploadApi;

/**
 * Class MockUploadApi
 */
class MockUploadApi extends UploadApi
{
    use MockApiTrait;

    /**
     * MockUploadApi constructor.
     *
     * @param mixed $configuration
     */
    public function __construct($configuration = null)
    {
        parent::__construct($configuration);

        $this->apiClient = new MockUploadApiClient($configuration);
    }
}
