<?php
/**
 * This file is part of the Cloudinary Media Management PHP package.
 *
 * (c) Cloudinary
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cloudinary\MediaManagement\Api\Upload;

use Cloudinary\MediaManagement\Api\ApiClient;
use Cloudinary\MediaManagement\Api\ApiResponse;
use Cloudinary\MediaManagement\Api\ApiUtils;
use Cloudinary\MediaManagement\Api\Exception\ApiError;
use Cloudinary\ArrayUtils;
use Cloudinary\MediaManagement\Asset\ModerationType;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Trait UploadTrait
 *
 * @property ApiClient $apiClient Defined in UploadApi class
 *
 * @api
 */
trait UploadTrait
{
    /**
     * Consolidates the upload parameters.
     *
     * @param array $options The optional parameters. See the upload API documentation.
     *
     * @return array
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_method
     */
    public static function buildUploadParams($options): array
    {
        $simpleParams = [
            'accessibility_analysis',
            'asset_folder',
            'async',
            'auto_tagging',
            'background_removal',
            'backup',
            'callback',
            'categorization',
            'cinemagraph_analysis',
            'colors',
            'context',
            'detection',
            'discard_original_filename',
            'display_name',
            'eager_notification_url',
            'eager_async',
            'eval',
            'faces',
            'filename_override',
            'folder',
            'format',
            'image_metadata',
            'invalidate',
            ModerationType::KEY,
            'notification_url',
            'ocr',
            'overwrite',
            'phash',
            'proxy',
            'public_id',
            'public_id_prefix',
            'quality_analysis',
            'quality_override',
            'raw_convert',
            'return_delete_token',
            'similarity_search',
            'type',
            'unique_filename',
            'use_filename',
            'use_filename_as_display_name',
        ];

        $complexParams = [
            'access_control'         => ApiUtils::serializeJson(ArrayUtils::get($options, 'access_control')),
            'allowed_formats'        => ApiUtils::serializeSimpleApiParam(ArrayUtils::get($options, 'allowed_formats')),
            'context'                => ApiUtils::serializeContext(ArrayUtils::get($options, 'context')),
            'custom_coordinates'     => ApiUtils::serializeArrayOfArrays(
                ArrayUtils::get($options, 'custom_coordinates')
            ),
            'eager'                  => ApiUtils::serializeAssetTransformations(ArrayUtils::get($options, 'eager')),
            'face_coordinates'       => ApiUtils::serializeArrayOfArrays(ArrayUtils::get($options, 'face_coordinates')),
            'headers'                => ApiUtils::serializeHeaders(ArrayUtils::get($options, 'headers')),
            'metadata'               => ApiUtils::serializeContext(ArrayUtils::get($options, 'metadata')),
            'public_ids'             => ApiUtils::serializeSimpleApiParam(ArrayUtils::get($options, 'public_ids')),
            'tags'                   => ApiUtils::serializeSimpleApiParam((ArrayUtils::get($options, 'tags'))),
            'transformation'         => ApiUtils::serializeTransformation($options),
        ];

        return ApiUtils::finalizeUploadApiParams(
            array_merge(ArrayUtils::whitelist($options, $simpleParams), $complexParams)
        );
    }

    /**
     * Uploads an asset to a Cloudinary cloud.
     *
     * The asset can be:
     * * a local file path
     * * the actual data (byte array buffer)
     * * the Data URI (Base64 encoded), max ~60 MB (62,910,000 chars)
     * * the remote FTP, HTTP or HTTPS URL address of an existing file
     * * a private storage bucket (S3 or Google Storage) URL of a whitelisted bucket
     *
     * This is an asynchronous function.
     *
     * @param string $file    The asset to upload.
     * @param array  $options The optional parameters. See the upload API documentation.
     *
     * @return PromiseInterface
     *
     * @throws ApiError
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_method
     */
    public function uploadAsync($file, $options = []): PromiseInterface
    {
        $params   = UploadApi::buildUploadParams($options);
        $endPoint = self::getUploadApiEndPoint(UploadEndPoint::UPLOAD, $options);

        return $this->apiClient->postFileAsync($endPoint, $file, $params, $options);
    }

    /**
     * Uploads an asset to a Cloudinary cloud.
     *
     * The asset can be:
     * * a local file path
     * * the actual data (byte array buffer)
     * * the Data URI (Base64 encoded), max ~60 MB (62,910,000 chars)
     * * the remote FTP, HTTP or HTTPS URL address of an existing file
     * * a private storage bucket (S3 or Google Storage) URL of a whitelisted bucket
     *
     * @param string $file    The asset to upload.
     * @param array  $options The optional parameters. See the upload API documentation.
     *
     * @return ApiResponse
     *
     * @throws ApiError
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_method
     */
    public function upload($file, $options = []): ApiResponse
    {
        return $this->uploadAsync($file, $options)->wait();
    }
}
