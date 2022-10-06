<?php
/**
 * This file is part of the Cloudinary Media Management PHP package.
 *
 * (c) Cloudinary
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cloudinary\MediaManagement\Test\Unit\Cloudinary;

use Cloudinary\MediaManagement\CldMediaManagement;
use Cloudinary\MediaManagement\Configuration\Configuration;
use Cloudinary\MediaManagement\Test\Unit\UnitTestCase;
use InvalidArgumentException;

/**
 * Class CloudinaryTest
 */
class CloudinaryTest extends UnitTestCase
{
    public function testCloudinaryUrlFromEnv()
    {
        $c = new CldMediaManagement();

        self::assertNotNull($c->configuration->cloud->cloudName);
        self::assertNotNull($c->configuration->cloud->apiKey);
        self::assertNotNull($c->configuration->cloud->apiSecret);
    }

    public function testCloudinaryUrlNotSet()
    {
        self::clearEnvironment();

        $this->expectException(InvalidArgumentException::class);

        new CldMediaManagement(); // Boom!
    }

    public function testCloudinaryFromOptions()
    {
        $c = new CldMediaManagement(
            [
                'cloud' => [
                    'cloud_name' => self::CLOUD_NAME,
                    'api_key'    => self::API_KEY,
                    'api_secret' => self::API_SECRET,
                ],
            ]
        );

        self::assertEquals(self::CLOUD_NAME, $c->configuration->cloud->cloudName);
        self::assertEquals(self::API_KEY, $c->configuration->cloud->apiKey);
        self::assertEquals(self::API_SECRET, $c->configuration->cloud->apiSecret);
    }

    public function testCloudinaryFromUrl()
    {
        $c = new CldMediaManagement($this->cloudinaryUrl);

        self::assertEquals(self::CLOUD_NAME, $c->configuration->cloud->cloudName);
        self::assertEquals(self::API_KEY, $c->configuration->cloud->apiKey);
        self::assertEquals(self::API_SECRET, $c->configuration->cloud->apiSecret);
    }

    public function testCloudinaryFromConfiguration()
    {
        self::clearEnvironment();

        $config = new Configuration($this->cloudinaryUrl);

        $c = new CldMediaManagement($config);

        self::assertEquals(self::CLOUD_NAME, $c->configuration->cloud->cloudName);
        self::assertEquals(self::API_KEY, $c->configuration->cloud->apiKey);
        self::assertEquals(self::API_SECRET, $c->configuration->cloud->apiSecret);
    }
}
