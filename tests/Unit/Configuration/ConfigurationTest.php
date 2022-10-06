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

use Cloudinary\MediaManagement\Configuration\ApiConfig;
use Cloudinary\MediaManagement\Configuration\Configuration;
use Cloudinary\MediaManagement\Test\Unit\UnitTestCase;
use Cloudinary\MediaManagement\Utils;

/**
 * Class ConfigTest
 */
class ConfigurationTest extends UnitTestCase
{
    const OAUTH_TOKEN          = 'NTQ0NjJkZmQ5OTM2NDE1ZTZjNGZmZj17';
    const URL_WITH_OAUTH_TOKEN = 'cloudinary://' . self::CLOUD_NAME . '?cloud[oauth_token]=' . self::OAUTH_TOKEN;

    public function testConfigFromUrl()
    {
        $config = new Configuration($this->cloudinaryUrl);

        self::assertEquals(self::CLOUD_NAME, $config->cloud->cloudName);
        self::assertEquals(self::API_KEY, $config->cloud->apiKey);
        self::assertEquals(self::API_SECRET, $config->cloud->apiSecret);
    }

    /**
     * Should allow passing Cloudinary URL that starts with a 'CLD_MEDIA_MANAGEMENT=' prefix, which is technically illegal,
     * but we are permissive.
     */
    public function testConfigFromFullUrl()
    {
        $config = new Configuration(Configuration::CLOUDINARY_URL_ENV_VAR . '=' . $this->cloudinaryUrl);

        self::assertEquals(self::CLOUD_NAME, $config->cloud->cloudName);
        self::assertEquals(self::API_KEY, $config->cloud->apiKey);
        self::assertEquals(self::API_SECRET, $config->cloud->apiSecret);
    }

    public function testConfigFromUrlsWithoutKeyAndSecretButWithOAuthToken()
    {
        $config = new Configuration(self::URL_WITH_OAUTH_TOKEN);

        self::assertEquals(self::CLOUD_NAME, $config->cloud->cloudName);
        self::assertEquals(self::OAUTH_TOKEN, $config->cloud->oauthToken);
        self::assertNull($config->cloud->apiKey);
        self::assertNull($config->cloud->apiSecret);
    }

    public function testConfigFromUrlsWitKeyAndSecretAndOAuthToken()
    {
        $config = new Configuration($this->cloudinaryUrl . '?cloud[oauth_token]=' . self::OAUTH_TOKEN);

        self::assertEquals(self::CLOUD_NAME, $config->cloud->cloudName);
        self::assertEquals(self::API_KEY, $config->cloud->apiKey);
        self::assertEquals(self::API_SECRET, $config->cloud->apiSecret);
        self::assertEquals(self::OAUTH_TOKEN, $config->cloud->oauthToken);
    }

    public function testConfigNoEnv()
    {
        self::clearEnvironment();

        $config = new Configuration();

        $config->cloud->cloudName(self::CLOUD_NAME);

        self::assertEquals(self::CLOUD_NAME, $config->cloud->cloudName);
    }

    public function testConfigToString()
    {
        $config = Configuration::fromCloudinaryUrl($this->cloudinaryUrl);

        $config->cloud->signatureAlgorithm(Utils::ALGO_SHA256);

        self::assertStrEquals(
            $this->cloudinaryUrl . '?cloud[signature_algorithm]=sha256',
            $config
        );
    }

    public function testConfigToStringWithOAuthToken()
    {
        $config = Configuration::fromCloudinaryUrl($this->cloudinaryUrl);

        $config->cloud->oauthToken = self::OAUTH_TOKEN;

        self::assertStrEquals(
            $this->cloudinaryUrl . '?cloud[oauth_token]=' . self::OAUTH_TOKEN,
            $config
        );
    }

    public function testConfigToStringWithMultipleQueryParams()
    {
        $config = Configuration::fromCloudinaryUrl($this->cloudinaryUrl);

        $config->api->timeout       = 60;
        $config->api->uploadTimeout = 300;

        $config->cloud->oauthToken = self::OAUTH_TOKEN;

        self::assertStrEquals(
            $this->cloudinaryUrl .
            '?cloud[oauth_token]=' . self::OAUTH_TOKEN .
            '&api[timeout]=60&api[upload_timeout]=300',
            $config
        );
    }

    public function testConfigKeyExplicitlySet()
    {
        $config = Configuration::fromCloudinaryUrl($this->cloudinaryUrl);

        self::assertTrue($config->cloud->isExplicitlySet('cloud_name'));

        self::assertEquals(ApiConfig::DEFAULT_TIMEOUT, $config->api->timeout); // configuration default is set to true.
        self::assertFalse($config->api->isExplicitlySet('timeout')); // it was not set by user.

        // set the property
        $config->api->timeout = 61;

        self::assertTrue($config->api->isExplicitlySet('timeout'));
    }

    public function testConfigJsonSerialize()
    {
        $jsonConfig = json_encode(Configuration::fromCloudinaryUrl($this->cloudinaryUrl));

        $expectedJsonConfig = '{"version":' . Configuration::VERSION . ',"cloud":{' .
                              '"cloud_name":"' . self::CLOUD_NAME . '","api_key":"' . self::API_KEY . '","api_secret":"'
                              . self::API_SECRET . '"}}';

        self::assertEquals(
            $expectedJsonConfig,
            $jsonConfig
        );

        self::assertEquals(
            $expectedJsonConfig,
            json_encode(Configuration::fromJson($expectedJsonConfig))
        );
    }
}
