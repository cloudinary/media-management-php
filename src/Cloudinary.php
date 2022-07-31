<?php
/**
 * This file is part of the Cloudinary Media Management PHP package.
 *
 * (c) Cloudinary
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cloudinary\MediaManagement;

use Cloudinary\MediaManagement\Api\Admin\AdminApi;
use Cloudinary\MediaManagement\Api\Search\SearchApi;
use Cloudinary\MediaManagement\Api\Upload\UploadApi;
use Cloudinary\MediaManagement\Configuration\Configuration;

/**
 * Defines the Cloudinary instance.
 *
 * @api
 */
class Cloudinary
{
    /**
     * The current version of the SDK.
     *
     * @var string VERSION
     */
    const VERSION = '0.1.1-beta';

    /**
     * Defines the Cloudinary cloud details and other global configuration options.
     *
     * @var Configuration $configuration
     */
    public $configuration;

    /**
     * Cloudinary constructor.
     *
     * @param Configuration|string|array|null $config The Configuration source.
     */
    public function __construct($config = null)
    {
        $this->configuration = new Configuration($config);
        $this->configuration->validate();
    }

    /**
     * Creates a new AdminApi instance using the current configuration instance.
     *
     * @return AdminApi
     */
    public function adminApi(): AdminApi
    {
        return new AdminApi($this->configuration);
    }

    /**
     * Creates a new UploadApi instance using the current configuration instance.
     *
     * @return UploadApi
     */
    public function uploadApi(): UploadApi
    {
        return new UploadApi($this->configuration);
    }

    /**
     * Creates a new SearchApi instance using the current configuration instance.
     *
     * @return SearchApi
     */
    public function searchApi(): SearchApi
    {
        return new SearchApi($this->configuration);
    }
}
