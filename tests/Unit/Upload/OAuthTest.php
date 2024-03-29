<?php
/**
 * This file is part of the Cloudinary Media Management PHP package.
 *
 * (c) Cloudinary
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cloudinary\MediaManagement\Test\Unit\Upload;

use Cloudinary\MediaManagement\Api\Exception\ApiError;
use Cloudinary\MediaManagement\Configuration\Configuration;
use Cloudinary\MediaManagement\Test\Helpers\MockUploadApi;
use Cloudinary\MediaManagement\Test\Helpers\RequestAssertionsTrait;
use Cloudinary\MediaManagement\Test\Integration\IntegrationTestCase;
use InvalidArgumentException;

/**
 * Class OAuthTest
 */
final class OAuthTest extends IntegrationTestCase
{
    use RequestAssertionsTrait;

    const FAKE_OAUTH_TOKEN = 'MTQ0NjJkZmQ5OTM2NDE1ZTZjNGZmZjI4';
    const API_TEST_PRESET = 'api_test_upload_preset';

    /**
     * Should upload an asset using an Oauth Token.
     *
     * @throws ApiError
     */
    public function testOauthTokenUploadApi()
    {
        $config = new Configuration(Configuration::instance());
        $config->cloud->oauthToken(self::FAKE_OAUTH_TOKEN);

        $uploadApi = new MockUploadApi($config);
        $uploadApi->upload(self::TEST_BASE64_IMAGE);
        $lastRequest = $uploadApi->getMockHandler()->getLastRequest();

        self::assertRequestHeaderSubset(
            $lastRequest,
            [
                'Authorization' => ['Bearer ' . self::FAKE_OAUTH_TOKEN]
            ]
        );
    }

    /**
     * Should upload an asset using `apiKey` and `apiSecret` if an Oauth Token is absent.
     *
     * @throws ApiError
     */
    public function testKeyAndSecretUploadApi()
    {
        $config = new Configuration(Configuration::instance());
        $config->cloud->oauthToken(null);

        $uploadApi = new MockUploadApi($config);
        $uploadApi->upload(self::TEST_BASE64_IMAGE);

        $params = $uploadApi->getApiClient()->getRequestMultipartOptions();

        self::assertArrayHasKey('api_key', $params);
        self::assertArrayHasKey('signature', $params);
    }

    /**
     * Should be thrown an exception if `apiKey` and `apiSecret` or an Oauth Token are absent.
     *
     * @throws ApiError
     */
    public function testMissingCredentialsUploadApi()
    {
        $config = new Configuration(Configuration::instance());
        $config->cloud->oauthToken(null);
        $config->cloud->apiKey = null;
        $config->cloud->apiSecret = null;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Must supply apiKey');

        $uploadApi = new MockUploadApi($config);
        $uploadApi->upload(self::TEST_BASE64_IMAGE);
    }
}
