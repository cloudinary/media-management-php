<?php
/**
 * This file is part of the Cloudinary Media Management PHP package.
 *
 * (c) Cloudinary
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cloudinary\MediaManagement\Api;

use Cloudinary\ArrayUtils;
use Cloudinary\MediaManagement\Asset\AssetTransformation;
use Cloudinary\ClassUtils;
use Cloudinary\MediaManagement\Configuration\CloudConfig;
use Cloudinary\Transformation\Transformation;
use Cloudinary\MediaManagement\Utils;

/**
 * Class ApiUtils
 *
 * @api
 */
class ApiUtils
{
    const HEADERS_OUTER_DELIMITER         = "\n";
    const HEADERS_INNER_DELIMITER         = ':';
    const API_PARAM_DELIMITER             = ',';
    const CONTEXT_OUTER_DELIMITER         = '|';
    const CONTEXT_INNER_DELIMITER         = '=';
    const ARRAY_OF_ARRAYS_DELIMITER       = '|';
    const ASSET_TRANSFORMATIONS_DELIMITER = '|';
    const QUERY_STRING_INNER_DELIMITER    = '=';
    const QUERY_STRING_OUTER_DELIMITER    = '&';

    /**
     * Serializes a simple parameter, which can be a serializable object.
     *
     * In case the parameter is an array, it is joined using ApiUtils::API_PARAM_DELIMITER.
     *
     * @param string|array|mixed $parameter The parameter to serialize.
     *
     * @return string The resulting serialized parameter.
     *
     * @internal
     *
     * @see ApiUtils::API_PARAM_DELIMITER
     */
    public static function serializeSimpleApiParam($parameter): ?string
    {
        return self::serializeParameter($parameter, self::API_PARAM_DELIMITER);
    }

    /**
     * Serializes 'headers' upload API parameter.
     *
     * @param $headers
     *
     * @return string The resulting serialized parameter.
     *
     * @internal
     */
    public static function serializeHeaders($headers): ?string
    {
        return self::serializeParameter($headers, self::HEADERS_OUTER_DELIMITER, self::HEADERS_INNER_DELIMITER);
    }

    /**
     * Serializes 'context' and 'metadata' upload API parameters.
     *
     * @param array $context
     *
     * @return string The resulting serialized parameter.
     *
     * @internal
     */
    public static function serializeContext($context): ?string
    {
        if (is_array($context)) {
            $context = array_map('\Cloudinary\MediaManagement\Api\ApiUtils::serializeJson', $context);
        }

        return self::serializeParameter($context, self::CONTEXT_OUTER_DELIMITER, self::CONTEXT_INNER_DELIMITER);
    }

    /**
     * Serializes (encodes) json object to string.
     *
     * @param mixed $jsonParam Json object
     *
     * @return string The resulting serialized parameter.
     *
     * @internal
     */
    public static function serializeJson($jsonParam): ?string
    {
        if ($jsonParam === null) {
            return null;
        }

        // Avoid extra double quotes around strings.
        if (is_string($jsonParam) || (is_object($jsonParam) && method_exists($jsonParam, '__toString'))) {
            return $jsonParam;
        }

        return json_encode($jsonParam); //TODO: serialize dates
    }

    /**
     * Commonly used util for building Cloudinary API params.
     *
     * @param      $parameter
     * @param      $outerDelimiter
     * @param null $innerDelimiter
     *
     * @return string The resulting serialized parameter.
     *
     * @internal
     *
     */
    public static function serializeParameter($parameter, $outerDelimiter = null, $innerDelimiter = null): ?string
    {
        if (! is_array($parameter)) {
            return $parameter === null ? null : (string)$parameter; // can be a serializable object
        }

        return ArrayUtils::safeImplodeAssoc($parameter, $outerDelimiter, $innerDelimiter);
    }

    /**
     * Serializes array of nested array parameters.
     *
     * @param array $arrayOfArrays The input array.
     *
     * @return string The resulting serialized parameter.
     *
     * @internal
     */
    public static function serializeArrayOfArrays($arrayOfArrays): ?string
    {
        $array = ArrayUtils::build($arrayOfArrays);

        if (! count($array)) {
            return null;
        }

        if (! is_array($array[0])) {
            return self::serializeSimpleApiParam($array);
        }

        $array = array_map('self::serializeSimpleApiParam', $array);


        return self::serializeParameter($array, self::ARRAY_OF_ARRAYS_DELIMITER);
    }

    /**
     * Serializes asset transformations.
     *
     * @param array|string $transformations
     *
     * @return string The resulting serialized parameter.
     *
     * @internal
     */
    public static function serializeAssetTransformations($transformations): string
    {
        $serializedTransformations = [];

        foreach (ArrayUtils::build($transformations) as $singleTransformation) {
            $serializedTransformations[] = (string)ClassUtils::verifyInstance(
                $singleTransformation,
                AssetTransformation::class
            );
        }

        return ArrayUtils::implodeFiltered(self::ASSET_TRANSFORMATIONS_DELIMITER, $serializedTransformations);
    }

    /**
     * Serializes incoming transformation.
     *
     * @param array|string $transformationParams
     *
     * @return string The resulting serialized parameter.
     *
     * @internal
     */
    public static function serializeTransformation($transformationParams): string
    {
        return (string)ClassUtils::forceInstance($transformationParams, Transformation::class);
    }

    /**
     * Finalizes Upload API parameters.
     *
     * Normalizes parameters values, removes empty, adds timestamp.
     *
     * @param array $params Parameters to finalize.
     *
     * @return array Resulting parameters.
     *
     * @internal
     *
     */
    public static function finalizeUploadApiParams($params): array
    {
        $additionalParams = [
            'timestamp' => Utils::unixTimeNow(),
        ];

        $params = array_merge($params, $additionalParams);

        ArrayUtils::convertBoolToIntStrings($params);

        return ArrayUtils::safeFilter($params);
    }

    /**
     * @param array $parameters
     *
     * @return string
     *
     * @internal
     */
    public static function serializeQueryParams($parameters = []): string
    {
        return ArrayUtils::implodeAssoc(
            $parameters,
            self::QUERY_STRING_OUTER_DELIMITER,
            self::QUERY_STRING_INNER_DELIMITER
        );
    }

    /**
     * Signs parameters of the request.
     *
     * @param array  $parameters         Parameters to sign.
     * @param string $secret             The API secret of the cloud.
     * @param string $signatureAlgorithm Signature algorithm
     *
     * @return string The signature.
     *
     * @api
     */
    public static function signParameters($parameters, $secret, $signatureAlgorithm = Utils::ALGO_SHA1): string
    {
        $parameters = array_map('self::serializeSimpleApiParam', $parameters);

        ksort($parameters);

        $signatureContent = self::serializeQueryParams($parameters);

        return Utils::sign($signatureContent, $secret, false, $signatureAlgorithm);
    }

    /**
     * Adds signature and api_key to $parameters.
     *
     * @param array|null  $parameters
     * @param CloudConfig $cloudConfig
     *
     * @internal
     */
    public static function signRequest(&$parameters, $cloudConfig): void
    {
        $parameters['signature'] = self::signParameters(
            $parameters,
            $cloudConfig->apiSecret,
            $cloudConfig->signatureAlgorithm
        );
        $parameters['api_key']   = $cloudConfig->apiKey;
    }
}
