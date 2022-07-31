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

use Cloudinary\MediaManagement\Api\Admin\AdminApi;

/**
 * Class MockAdminApi
 */
class MockAdminApi extends AdminApi
{
    use MockApiTrait;

    /**
     * MockAdminApi constructor.
     *
     * @param mixed $configuration
     */
    public function __construct($configuration = null)
    {
        parent::__construct($configuration);

        $this->apiClient = new MockApiClient($configuration);
    }
}
