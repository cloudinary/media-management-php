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
use Cloudinary\MediaManagement\Api\ApiUtils;
use Cloudinary\MediaManagement\Api\Exception\ApiError;
use Cloudinary\ArrayUtils;
use Cloudinary\MediaManagement\Asset\AssetType;
use Cloudinary\MediaManagement\Asset\DeliveryType;
use Cloudinary\MediaManagement\Asset\ModerationStatus;

/**
 * Enables you to manage the assets in your cloud.
 *
 * **Learn more**: <a
 * href=https://cloudinary.com/documentation/admin_api#resources target="_blank">
 * Resources method - Admin API</a>
 *
 * @property ApiClient $apiClient Defined in AdminApi class.
 *
 * @api
 */
trait AssetsTrait
{
    /**
     * Lists all uploaded assets filtered by any specified options.
     *
     * @param array $options The optional parameters. See the
     * <a href=https://cloudinary.com/documentation/admin_api#get_resources target="_blank"> Admin API</a> documentation.
     *
     * @return ApiResponse
     *
     * @see https://cloudinary.com/documentation/admin_api#get_resources
     */
    public function assets($options = []): ApiResponse
    {
        $assetType = ArrayUtils::get($options, AssetType::KEY, AssetType::IMAGE);
        $uri       = [ApiEndPoint::ASSETS, $assetType];
        ArrayUtils::appendNonEmpty($uri, ArrayUtils::get($options, DeliveryType::KEY));

        $params = ArrayUtils::whitelist(
            $options,
            [
                'next_cursor',
                'max_results',
                'prefix',
                'tags',
                'context',
                'moderations',
                'direction',
                'start_at',
                'metadata',
            ]
        );

        return $this->apiClient->get($uri, $params);
    }

    /**
     * Lists assets with the specified tag.
     *
     * This method does not return matching deleted assets, even if they have been backed up.
     *
     * @param string $tag     The tag value.
     * @param array  $options The optional parameters. See the
     * <a href=https://cloudinary.com/documentation/admin_api#get_resources_by_tag target="_blank"> Admin API</a> documentation.
     *
     * @return ApiResponse
     *
     * @see https://cloudinary.com/documentation/admin_api#get_resources_by_tag
     */
    public function assetsByTag($tag, $options = []): ApiResponse
    {
        $assetType = ArrayUtils::get($options, AssetType::KEY, AssetType::IMAGE);
        $uri       = [ApiEndPoint::ASSETS, $assetType, 'tags', $tag];
        $params    = ArrayUtils::whitelist(
            $options,
            ['next_cursor', 'max_results', 'tags', 'context', 'moderations', 'direction', 'metadata']
        );

        return $this->apiClient->get($uri, $params);
    }

    /**
     * Lists assets with the specified contextual metadata.
     *
     * This method does not return matching deleted assets, even if they have been backed up.
     *
     * @param string $key     Only assets with this context key are returned.
     * @param string $value   Only assets with this context value for the specified context key are returned.
     *                        If this parameter is not provided, all assets with the specified context key are returned,
     *                        regardless of the key value.
     * @param array  $options The optional parameters. See the
     * <a href=https://cloudinary.com/documentation/admin_api#get_resources_by_context target="_blank"> Admin API</a> documentation.
     *
     * @return ApiResponse
     *
     * @see https://cloudinary.com/documentation/admin_api#get_resources_by_context
     */
    public function assetsByContext($key, $value = null, $options = []): ApiResponse
    {
        $assetType       = ArrayUtils::get($options, AssetType::KEY, AssetType::IMAGE);
        $uri             = [ApiEndPoint::ASSETS, $assetType, 'context'];
        $params          = ArrayUtils::whitelist(
            $options,
            ['next_cursor', 'max_results', 'tags', 'context', 'moderations', 'direction', 'metadata']
        );
        $params['key']   = $key;
        $params['value'] = $value;

        return $this->apiClient->get($uri, $params);
    }

    /**
     * Lists assets currently in the specified moderation queue and status.
     *
     * @param string $kind    Type of image moderation queue to list.
     *                        Valid values:  "manual", "webpurify", "aws_rek", or "metascan".
     * @param string $status  Only assets with this moderation status will be returned.
     *                        Valid values: "pending", "approved", "rejected".
     * @param array  $options The optional parameters. See the
     * <a href=https://cloudinary.com/documentation/admin_api#get_resources_in_moderation_queues target="_blank"> Admin API</a> documentation.
     *
     * @return ApiResponse
     *
     * @see https://cloudinary.com/documentation/admin_api#get_resources_in_moderation_queues
     */
    public function assetsByModeration($kind, $status, $options = []): ApiResponse
    {
        $assetType = ArrayUtils::get($options, AssetType::KEY, AssetType::IMAGE);
        $uri       = [ApiEndPoint::ASSETS, $assetType, 'moderations', $kind, $status];

        $params = ArrayUtils::whitelist(
            $options,
            ['next_cursor', 'max_results', 'tags', 'context', 'moderations', 'direction', 'metadata']
        );

        return $this->apiClient->get($uri, $params);
    }

    /**
     * Lists assets with the specified public IDs.
     *
     * @param string|array $publicIds The requested public_ids (up to 100).
     * @param array        $options   The optional parameters. See the
     * <a href=https://cloudinary.com/documentation/admin_api#get_resources target="_blank"> Admin API</a> documentation.
     *
     * @return ApiResponse
     *
     * @see https://cloudinary.com/documentation/admin_api#get_resources
     */
    public function assetsByIds($publicIds, $options = []): ApiResponse
    {
        $assetType = ArrayUtils::get($options, AssetType::KEY, AssetType::IMAGE);
        $type      = ArrayUtils::get($options, DeliveryType::KEY, DeliveryType::UPLOAD);
        $uri       = [ApiEndPoint::ASSETS, $assetType, $type];

        $params               = ArrayUtils::whitelist($options, ['public_ids', 'tags', 'moderations', 'context']);
        $params['public_ids'] = $publicIds;

        return $this->apiClient->get($uri, $params);
    }

    /**
     * Lists assets with the specified asset IDs.
     *
     * @param string|array $assetIds  The requested asset IDs.
     * @param array        $options   The optional parameters. See the
     * <a href=https://cloudinary.com/documentation/admin_api#get_resources target="_blank"> Admin API</a> documentation.
     *
     * @return ApiResponse
     *
     * @see https://cloudinary.com/documentation/admin_api#get_resources
     */
    public function assetsByAssetIds($assetIds, $options = []): ApiResponse
    {
        $uri = [ApiEndPoint::ASSETS, 'by_asset_ids'];

        $params              = ArrayUtils::whitelist($options, ['public_ids', 'tags', 'moderations', 'context']);
        $params['asset_ids'] = $assetIds;

        return $this->apiClient->get($uri, $params);
    }

    /**
     * Returns the details of the specified asset and all its derived assets.
     *
     *
     * Note that if you only need details about the original asset,
     * you can also use the Uploader::upload or Uploader::explicit methods, which return the same information and
     * are not rate limited.
     *
     * @param string $publicId The public ID of the asset.
     * @param array  $options  The optional parameters. See the
     * <a href=https://cloudinary.com/documentation/admin_api#get_the_details_of_a_single_resource target="_blank"> Admin API</a> documentation.
     *
     * @return ApiResponse
     *
     * @see https://cloudinary.com/documentation/admin_api#get_the_details_of_a_single_resource
     */
    public function asset($publicId, $options = []): ApiResponse
    {
        $assetType = ArrayUtils::get($options, AssetType::KEY, AssetType::IMAGE);
        $type      = ArrayUtils::get($options, DeliveryType::KEY, DeliveryType::UPLOAD);
        $uri       = [ApiEndPoint::ASSETS, $assetType, $type, $publicId];

        $params = self::prepareAssetDetailsParams($options);

        return $this->apiClient->get($uri, $params);
    }

    /**
     * Returns the details of the specified asset and all its derived assets by asset id.
     *
     *
     * Note that if you only need details about the original asset,
     * you can also use the Uploader::upload or Uploader::explicit methods, which return the same information and
     * are not rate limited.
     *
     * @param string $assetId The Asset ID of the asset.
     * @param array  $options The optional parameters. See the
     *                        <a
     *                        href=https://cloudinary.com/documentation/admin_api#get_the_details_of_a_single_resource
     *                        target="_blank"> Admin API</a> documentation.
     *
     * @return ApiResponse
     *
     * @see https://cloudinary.com/documentation/admin_api#get_the_details_of_a_single_resource
     */
    public function assetByAssetId($assetId, $options = []): ApiResponse
    {
        $uri = [ApiEndPoint::ASSETS, $assetId];

        $params = self::prepareAssetDetailsParams($options);

        return $this->apiClient->get($uri, $params);
    }

    /**
     * Reverts to the latest backed up version of the specified deleted assets.
     *
     * @param string|array $publicIds The public IDs of the backed up assets to restore. They can be existing or
     * deleted assets.
     * @param array        $options   The optional parameters. See the
     * <a href=https://cloudinary.com/documentation/admin_api#restore_resources target="f_blank"> Admin API</a> documentation.
     *
     * @return ApiResponse
     *
     * @see https://cloudinary.com/documentation/admin_api#restore_resources
     */
    public function restore($publicIds, $options = []): ApiResponse
    {
        $assetType = ArrayUtils::get($options, AssetType::KEY, AssetType::IMAGE);
        $type      = ArrayUtils::get($options, DeliveryType::KEY, DeliveryType::UPLOAD);
        $uri       = [ApiEndPoint::ASSETS, $assetType, $type, 'restore'];

        $params = array_merge($options, ['public_ids' => $publicIds]);

        return $this->apiClient->postJson($uri, $params);
    }

    /**
     * Updates details of an existing asset.
     *
     * Update one or more of the attributes associated with a specified asset. Note that you can also update
     * most attributes of an existing asset using the Uploader::explicit method, which is not rate limited.
     *
     * @param string|array $publicId The public ID of the asset to update.
     * @param array        $options  The optional parameters. See the
     * <a href=https://cloudinary.com/documentation/admin_api#update_details_of_an_existing_resource target="_blank"> Admin API</a> documentation.
     *
     * @return ApiResponse
     *
     * @see https://cloudinary.com/documentation/admin_api#update_details_of_an_existing_resource
     */
    public function update($publicId, $options = []): ApiResponse
    {
        $assetType = ArrayUtils::get($options, AssetType::KEY, AssetType::IMAGE);
        $type      = ArrayUtils::get($options, DeliveryType::KEY, DeliveryType::UPLOAD);
        $uri       = [ApiEndPoint::ASSETS, $assetType, $type, $publicId];

        $primitiveOptions = ArrayUtils::whitelist(
            $options,
            [
                ModerationStatus::KEY,
                'raw_convert',
                'ocr',
                'categorization',
                'detection',
                'similarity_search',
                'auto_tagging',
                'background_removal',
                'quality_override',
                'notification_url',
                'use_asset_id',
            ]
        );

        $arrayOptions = [
            'tags'               => ApiUtils::serializeSimpleApiParam(ArrayUtils::get($options, 'tags')),
            'context'            => ApiUtils::serializeContext(ArrayUtils::get($options, 'context')),
            'metadata'           => ApiUtils::serializeContext(ArrayUtils::get($options, 'metadata')),
            'face_coordinates'   => ApiUtils::serializeArrayOfArrays(ArrayUtils::get($options, 'face_coordinates')),
            'custom_coordinates' => ApiUtils::serializeArrayOfArrays(ArrayUtils::get($options, 'custom_coordinates')),
            'access_control'     => ApiUtils::serializeJson(ArrayUtils::get($options, 'access_control')),
        ];

        $updateOptions = array_merge($primitiveOptions, $arrayOptions);

        return $this->apiClient->postForm($uri, $updateOptions);
    }

    /**
     * Deletes the specified assets.
     *
     * @param string|array $publicIds The public IDs of the assets to delete (up to 100).
     * @param array        $options   The optional parameters. See the
     * <a href=https://cloudinary.com/documentation/admin_api#sdelete_resources target="_blank"> Admin API</a> documentation.
     *
     * @return ApiResponse
     *
     * @throws ApiError
     *
     * @see https://cloudinary.com/documentation/admin_api#delete_resources
     */
    public function deleteAssets($publicIds, $options = []): ApiResponse
    {
        $assetType = ArrayUtils::get($options, AssetType::KEY, AssetType::IMAGE);
        $type      = ArrayUtils::get($options, DeliveryType::KEY, DeliveryType::UPLOAD);
        $uri       = [ApiEndPoint::ASSETS, $assetType, $type];

        $params = self::prepareDeleteAssetParams($options, ['public_ids' => $publicIds]);

        return $this->apiClient->delete($uri, $params);
    }

    /**
     * Deletes assets by prefix.
     *
     * Delete up to 1000 original assets, along with their derived assets, where the public ID starts with the
     * specified prefix.
     *
     * @param string $prefix  The Public ID prefix.
     * @param array  $options The optional parameters. See the
     * <a href=https://cloudinary.com/documentation/admin_api#delete_resources target="_blank"> Admin API</a> documentation.
     *
     * @return ApiResponse
     *
     * @throws ApiError
     *
     * @see https://cloudinary.com/documentation/admin_api#delete_resources
     */
    public function deleteAssetsByPrefix($prefix, $options = []): ApiResponse
    {
        $assetType = ArrayUtils::get($options, AssetType::KEY, AssetType::IMAGE);
        $type      = ArrayUtils::get($options, DeliveryType::KEY, DeliveryType::UPLOAD);
        $uri       = [ApiEndPoint::ASSETS, $assetType, $type];

        $params = self::prepareDeleteAssetParams($options, ['prefix' => $prefix]);

        return $this->apiClient->delete($uri, $params);
    }

    /**
     * Deletes all assets of the specified asset and delivery type, including their derived assets.
     *
     * Supports deleting up to a maximum of 1000 original assets in a single call.
     *
     * @param array $options The optional parameters. See the
     * <a href=https://cloudinary.com/documentation/admin_api#delete_resources target="_blank"> Admin API</a> documentation.
     *
     * @return ApiResponse
     *
     * @throws ApiError
     *
     * https://cloudinary.com/documentation/admin_api#delete_resources
     */
    public function deleteAllAssets($options = []): ApiResponse
    {
        $assetType = ArrayUtils::get($options, AssetType::KEY, AssetType::IMAGE);
        $type      = ArrayUtils::get($options, DeliveryType::KEY, DeliveryType::UPLOAD);
        $uri       = [ApiEndPoint::ASSETS, $assetType, $type];
        $params    = self::prepareDeleteAssetParams($options, ['all' => true]);

        return $this->apiClient->delete($uri, $params);
    }

    /**
     * Deletes assets with the specified tag, including their derived assets.
     *
     * Supports deleting up to a maximum of 1000 original assets in a single call.
     *
     * @param string $tag     The tag value of the assets to delete.
     * @param array  $options The optional parameters. See the
     * <a href=https://cloudinary.com/documentation/admin_api#delete_resources_by_tags target="_blank"> Admin API</a> documentation.
     *
     * @return ApiResponse
     *
     * @throws ApiError
     *
     * @see https://cloudinary.com/documentation/admin_api#delete_resources_by_tags
     */
    public function deleteAssetsByTag($tag, $options = []): ApiResponse
    {
        $assetType = ArrayUtils::get($options, AssetType::KEY, AssetType::IMAGE);
        $uri       = [ApiEndPoint::ASSETS, $assetType, 'tags', $tag];
        $params    = self::prepareDeleteAssetParams($options);

        return $this->apiClient->delete($uri, $params);
    }

    /**
     * Prepares optional parameters for delete asset API calls.
     *
     * @param array $options Additional options.
     * @param array $params  The parameters passed to the API.
     *
     * @return array    Updated parameters
     *
     * @internal
     */
    protected static function prepareDeleteAssetParams($options, $params = []): array
    {
        $filtered = ArrayUtils::whitelist($options, ['next_cursor', 'invalidate']);

        return array_merge($params, $filtered);
    }

    /**
     * Prepares optional parameters for asset/assetByAssetId API calls.
     *
     * @param array $options Additional options.
     *
     * @return array    Optional parameters
     *
     * @internal
     */
    protected static function prepareAssetDetailsParams($options): array
    {
        return ArrayUtils::whitelist(
            $options,
            [
                'colors',
                'faces',
                'quality_analysis',
                'image_metadata',
                'phash',
                'pages',
                'cinemagraph_analysis',
                'coordinates',
                'max_results',
                'derived_next_cursor',
                'accessibility_analysis',
                'versions',
            ]
        );
    }
}
