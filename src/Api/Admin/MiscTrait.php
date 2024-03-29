<?php
/**
 * This file is part of the Cloudinary Media Management PHP package.
 *
 * (c) Cloudinary
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cloudinary\MediaManagement\Api\Admin;

use Cloudinary\MediaManagement\Api\ApiClient;
use Cloudinary\MediaManagement\Api\ApiResponse;
use Cloudinary\MediaManagement\Api\Exception\ApiError;
use Cloudinary\ArrayUtils;
use Cloudinary\MediaManagement\Asset\AssetType;
use Cloudinary\MediaManagement\Utils;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Represents several Admin API methods.
 *
 * @property ApiClient $apiClient Defined in AdminApi class.
 *
 * @api
 */
trait MiscTrait
{
    /**
     * Tests the reachability of the Cloudinary API.
     *
     * @return ApiResponse
     *
     * @see AdminApi::pingAsync()
     *
     * @see https://cloudinary.com/documentation/admin_api#ping
     */
    public function ping(): ApiResponse
    {
        return $this->pingAsync()->wait();
    }

    /**
     * Tests the reachability of the Cloudinary API asynchronously.
     *
     * @return PromiseInterface
     *
     * @see https://cloudinary.com/documentation/admin_api#ping
     */
    public function pingAsync(): PromiseInterface
    {
        return $this->apiClient->getAsync(ApiEndPoint::PING);
    }

    /**
     * Gets cloud usage details.
     *
     * Returns a report detailing your current Cloudinary cloud usage details, including
     * storage, bandwidth, requests, number of assets, and add-on usage.
     * Note that numbers are updated periodically.
     *
     * @param array $options The optional parameters. See the
     * <a href=https://cloudinary.com/documentation/admin_api#usage target="_blank"> Admin API</a> documentation.
     *
     * @return ApiResponse
     *
     * @throws ApiError
     *
     * @see https://cloudinary.com/documentation/admin_api#usage
     */
    public function usage($options = []): ApiResponse
    {
        $uri = [ApiEndPoint::USAGE, Utils::formatDate(ArrayUtils::get($options, 'date'))];

        return $this->apiClient->get($uri);
    }

    /**
     * Lists all the tags currently used for a specified asset type.
     *
     *
     * @param array $options The optional parameters. See the
     * <a href=https://cloudinary.com/documentation/admin_api#get_tags target="_blank"> Admin API</a> documentation.
     *
     * @return ApiResponse
     *
     * @throws ApiError
     *
     * @see https://cloudinary.com/documentation/admin_api#get_tags
     */
    public function tags($options = []): ApiResponse
    {
        $assetType = ArrayUtils::get($options, AssetType::KEY, AssetType::IMAGE);
        $uri       = [ApiEndPoint::TAGS, $assetType];

        $params = ArrayUtils::whitelist($options, ['next_cursor', 'max_results', 'prefix']);

        return $this->apiClient->get($uri, $params);
    }
}
