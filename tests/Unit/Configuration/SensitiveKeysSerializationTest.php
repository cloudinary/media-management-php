<?php
/**
 * This file is part of the Cloudinary Media Management PHP package.
 *
 * (c) Cloudinary
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cloudinary\MediaManagement\Test\Unit\Configuration;

use Cloudinary\MediaManagement\Configuration\CloudConfig;
use Cloudinary\MediaManagement\Test\Unit\UnitTestCase;

/**
 * Class SensitiveKeysSerializationTest
 */
class SensitiveKeysSerializationTest extends UnitTestCase
{
    private $cloud;

    public function setUp(): void
    {
        parent::setUp();

        $this->cloud = CloudConfig::fromCloudinaryUrl($this->cloudinaryUrl);
    }

    public function testExcludedKeysAreNotSerialized()
    {
        self::assertStrEquals(
            'cloud[cloud_name]=test123&cloud[api_key]=' . self::API_KEY,
            $this->cloud->toString([CloudConfig::API_SECRET])
        );

        self::assertStrEquals(
            'cloud[cloud_name]=test123&cloud[api_key]=' . self::API_KEY . '&cloud[api_secret]=' . self::API_SECRET,
            $this->cloud->toString()
        );
    }
}
