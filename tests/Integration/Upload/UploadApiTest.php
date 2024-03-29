<?php
/**
 * This file is part of the Cloudinary Media Management PHP package.
 *
 * (c) Cloudinary
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cloudinary\MediaManagement\Test\Integration\Upload;

use Cloudinary\ArrayUtils;
use Cloudinary\MediaManagement\Api\Exception\ApiError;
use Cloudinary\MediaManagement\Api\Exception\GeneralError;
use Cloudinary\MediaManagement\Api\Metadata\SetMetadataField;
use Cloudinary\MediaManagement\Api\Metadata\StringMetadataField;
use Cloudinary\MediaManagement\Asset\AssetType;
use Cloudinary\MediaManagement\Asset\DeliveryType;
use Cloudinary\MediaManagement\FileUtils;
use Cloudinary\MediaManagement\Test\Integration\IntegrationTestCase;
use Cloudinary\Transformation\Resize;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\StreamInterface;

/**
 * Class UploadApiTest
 */
final class UploadApiTest extends IntegrationTestCase
{
    const TEST_IMAGE_WIDTH  = 241;
    const TEST_IMAGE_HEIGHT = 51;

    const LARGE_CHUNK_SIZE = 5243000;

    const LARGE_TEST_IMAGE_SIZE   = 5880138;
    const LARGE_TEST_IMAGE_WIDTH  = 1400;
    const LARGE_TEST_IMAGE_HEIGHT = 1400;

    const SMALL_TEST_IMAGE_WIDTH  = 1;
    const SMALL_TEST_IMAGE_HEIGHT = 1;

    const INCOMING_TRANSFORMATION_WIDTH = 240;

    private static $LARGE_TEST_IMAGE_ID;
    private static $REMOTE_TEST_IMAGE_ID;

    private static $METADATA_FIELD_UNIQUE_EXTERNAL_ID;
    private static $METADATA_FIELD_VALUE;

    private static $METADATA_FIELD_EXTERNAL_ID_SET;
    private static $DATASOURCE_MULTIPLE;
    private static $DATASOURCE_ENTRY_EXTERNAL_ID;
    private static $DATASOURCE_ENTRY_EXTERNAL_ID2;

    private static $METADATA_FIELDS;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$LARGE_TEST_IMAGE_ID  = 'upload_large_' . self::$UNIQUE_TEST_ID;
        self::$REMOTE_TEST_IMAGE_ID = 'upload_remote_' . self::$UNIQUE_TEST_ID;

        self::$METADATA_FIELD_UNIQUE_EXTERNAL_ID = 'metadata_field_external_id_' . self::$UNIQUE_TEST_ID;
        self::$METADATA_FIELD_VALUE              = 'metadata_field_value_' . self::$UNIQUE_TEST_ID;

        self::$METADATA_FIELD_EXTERNAL_ID_SET = 'metadata_external_id_uploader_set_' . self::$UNIQUE_TEST_ID;
        self::$DATASOURCE_ENTRY_EXTERNAL_ID   = 'metadata_datasource_entry_external_id' . self::$UNIQUE_TEST_ID;
        self::$DATASOURCE_ENTRY_EXTERNAL_ID2  = 'metadata_datasource_entry_external_id2' . self::$UNIQUE_TEST_ID;

        self::$DATASOURCE_MULTIPLE = [
            [
                'value'       => 'v2',
                'external_id' => self::$DATASOURCE_ENTRY_EXTERNAL_ID,
            ],
            [
                'value'       => 'v3',
                'external_id' => self::$DATASOURCE_ENTRY_EXTERNAL_ID2,
            ],
            [
                'value' => 'v4',
            ],
        ];

        self::$METADATA_FIELDS = [
            self::$METADATA_FIELD_UNIQUE_EXTERNAL_ID => self::$METADATA_FIELD_VALUE,
            self::$METADATA_FIELD_EXTERNAL_ID_SET    => [
                self::$DATASOURCE_ENTRY_EXTERNAL_ID,
                self::$DATASOURCE_ENTRY_EXTERNAL_ID2,
            ],
        ];

        $stringMetadataField = new StringMetadataField(self::$METADATA_FIELD_UNIQUE_EXTERNAL_ID);
        $stringMetadataField->setExternalId(self::$METADATA_FIELD_UNIQUE_EXTERNAL_ID);
        self::$adminApi->addMetadataField($stringMetadataField);

        $setMetadataField = new SetMetadataField(self::$METADATA_FIELD_EXTERNAL_ID_SET, self::$DATASOURCE_MULTIPLE);
        $setMetadataField->setExternalId(self::$METADATA_FIELD_EXTERNAL_ID_SET);
        self::$adminApi->addMetadataField($setMetadataField);
    }

    public static function tearDownAfterClass(): void
    {
        self::cleanupTestAssets();
        self::cleanupMetadataField(self::$METADATA_FIELD_UNIQUE_EXTERNAL_ID);
        self::cleanupMetadataField(self::$METADATA_FIELD_EXTERNAL_ID_SET);

        parent::tearDownAfterClass();
    }

    /**
     * @throws ApiError
     */
    public function testUpload()
    {
        $result = self::$uploadApi->upload(self::TEST_IMAGE_PATH, ['tags' => self::$ASSET_TAGS]);

        self::assertValidAsset(
            $result,
            [
                'width'  => self::TEST_IMAGE_WIDTH,
                'height' => self::TEST_IMAGE_HEIGHT,
            ]
        );
    }

    /**
     * @throws ApiError
     */
    public function testUploadAsync()
    {
        $p = self::$uploadApi->uploadAsync(self::TEST_IMAGE_PATH, ['tags' => self::$ASSET_TAGS]);

        $result = $p->wait();

        self::assertValidAsset(
            $result,
            [
                'width'  => self::TEST_IMAGE_WIDTH,
                'height' => self::TEST_IMAGE_HEIGHT,
            ]
        );
    }

    /**
     * @throws ApiError
     */
    public function testUploadNonExistingFile()
    {
        $this->expectException(GeneralError::class);
        $this->expectExceptionMessage('No such file or directory');
        self::$uploadApi->upload(self::TEST_IMAGE_PATH . '_non_existing');
    }

    /**
     * @throws ApiError
     */
    public function testUploadFileHandle()
    {
        $fd = FileUtils::safeFileOpen(self::TEST_IMAGE_PATH, 'rb');

        $result = self::$uploadApi->upload($fd, ['tags' => self::$ASSET_TAGS]);

        self::assertValidAsset(
            $result,
            [
                'width'  => self::TEST_IMAGE_WIDTH,
                'height' => self::TEST_IMAGE_HEIGHT,
            ]
        );
    }

    /**
     * @throws ApiError
     */
    public function testUploadBase64()
    {
        $result = self::$uploadApi->upload(
            self::TEST_BASE64_IMAGE,
            ['filename' => self::$UNIQUE_TEST_ID, 'tags' => self::$ASSET_TAGS]
        );

        self::assertValidAsset(
            $result,
            [
                'width'  => self::SMALL_TEST_IMAGE_WIDTH,
                'height' => self::SMALL_TEST_IMAGE_HEIGHT,
            ]
        );
        self::assertArrayNotHasKey('original_filename', $result);
    }

    /**
     * @throws ApiError
     */
    public function testUploadUrl()
    {
        $result = self::$uploadApi->upload(self::FETCH_IMAGE_URL, ['tags' => self::$ASSET_TAGS]);

        self::assertValidAsset($result, ['original_filename' => self::ASSET_ID]);

        $result = self::$uploadApi->upload(new Uri(self::FETCH_IMAGE_URL), ['tags' => self::$ASSET_TAGS]);

        self::assertValidAsset($result, ['original_filename' => self::ASSET_ID]);
    }

    /**
     * @throws ApiError
     */
    public function testUploadGSUrl()
    {
        self::markTestSkipped('Check GS authorization issue');

        $gsImage = 'gs://gcp-public-data-landsat/LC08/PRE/044/034/LC80440342016259LGN00/LC80440342016259LGN00_BQA.TIF';
        $result  = self::$uploadApi->upload($gsImage, ['tags' => self::$ASSET_TAGS]);

        self::assertEquals($gsImage, $result['original_filename']);
    }

    /**
     * @throws ApiError
     */
    public function testUploadLarge()
    {
        $result = self::$uploadApi->upload(
            self::populateLargeImage(),
            [
                'filename'        => self::$LARGE_TEST_IMAGE_ID,
                'tags'            => self::$ASSET_TAGS,
                'chunk_size'      => self::LARGE_CHUNK_SIZE,
                'use_filename'    => true,
                'unique_filename' => false,
            ]
        );

        self::assertValidAsset(
            $result,
            [
                'original_filename' => self::$LARGE_TEST_IMAGE_ID,
                'width'             => self::LARGE_TEST_IMAGE_WIDTH,
                'height'            => self::LARGE_TEST_IMAGE_HEIGHT,
            ]
        );
    }

    /**
     * @throws ApiError
     */
    public function testUploadNonSeekableStream()
    {
        $stream = fopen(self::FETCH_IMAGE_URL, 'rb'); // open http socket and pass it to uploader

        $result = self::$uploadApi->upload(
            $stream,
            [
                'filename'        => self::$REMOTE_TEST_IMAGE_ID,
                'tags'            => self::$ASSET_TAGS,
                'use_filename'    => true,
                'unique_filename' => false,
            ]
        );

        self::assertValidAsset($result, ['original_filename' => self::$REMOTE_TEST_IMAGE_ID]);
    }

    /**
     * @return StreamInterface
     */
    protected static function populateLargeImage()
    {
        $head = "BMJ\xB9Y\x00\x00\x00\x00\x00\x8A\x00\x00\x00|\x00\x00\x00x\x05\x00\x00x\x05\x00\x00\x01\x00\x18\x00" .
                "\x00\x00\x00\x00\xC0\xB8Y\x00a\x0F\x00\x00a\x0F\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xFF" .
                "\x00\x00\xFF\x00\x00\xFF\x00\x00\x00\x00\x00\x00\xFFBGRs\x00\x00\x00\x00\x00\x00\x00\x00T\xB8\x1E" .
                "\xFC\x00\x00\x00\x00\x00\x00\x00\x00fff\xFC\x00\x00\x00\x00\x00\x00\x00\x00\xC4\xF5(\xFF\x00\x00\x00" .
                "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x04\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00";

        $largeStream = Psr7\Utils::streamFor($head . str_repeat("\xFF", self::LARGE_TEST_IMAGE_SIZE - strlen($head)));
        $largeStream->rewind();

        return $largeStream;
    }

    /**
     * Add a metadata field to the image with the Public ID
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#metadata_method
     */
    public function testAddTheMetadataFieldImagesByPublicID()
    {
        self::markTestIncomplete('This functionality is not implemented yet');
    }

    /**
     * Get the accessibility analysis of an uploaded image
     *
     * @throws ApiError
     */
    public function testAccessibilityAnalysis()
    {
        $result = self::uploadTestAssetImage(['accessibility_analysis' => true], self::TEST_IMAGE_PATH);

        self::assertArrayHasKey('accessibility_analysis', $result);
    }

    /**
     * Upload a resource with a metadata.
     *
     * @throws ApiError
     */
    public function testUploadWithMetadata()
    {
        $asset = self::$uploadApi->upload(
            self::TEST_IMAGE_PATH,
            [
                'tags'     => self::$ASSET_TAGS,
                'metadata' => self::$METADATA_FIELDS,
            ]
        );

        self::assertEquals(
            self::$METADATA_FIELDS,
            ArrayUtils::whitelist($asset['metadata'], array_keys(self::$METADATA_FIELDS))
        );
    }

    /**
     * Applies metadata to existing asset using explicit.
     *
     * @throws ApiError
     */
    public function testExplicitWithMetadata()
    {
        $asset = self::$uploadApi->upload(self::TEST_IMAGE_PATH, ['tags' => self::$ASSET_TAGS]);

        $result = self::$uploadApi->explicit(
            $asset['public_id'],
            [
                'type'          => DeliveryType::UPLOAD,
                'resource_type' => AssetType::IMAGE,
                'metadata'      => self::$METADATA_FIELDS,
            ]
        );

        self::assertEquals(
            self::$METADATA_FIELDS,
            ArrayUtils::whitelist($result['metadata'], array_keys(self::$METADATA_FIELDS))
        );
    }

    /**
     * Update metadata of an existing asset.
     *
     * @throws ApiError
     */
    public function testUploaderUpdateMetadata()
    {
        $asset = self::$uploadApi->upload(self::TEST_IMAGE_PATH, ['tags' => self::$ASSET_TAGS]);

        $result = self::$uploadApi->updateMetadata(
            self::$METADATA_FIELDS,
            [
                $asset['public_id'],
            ]
        );

        self::assertCount(1, $result['public_ids']);
        self::assertContains($asset['public_id'], $result['public_ids']);
    }

    /**
     * Editing metadata of multiple existing resources.
     *
     * @throws ApiError
     */
    public function testUploaderUpdateMetadataOnMultipleResources()
    {
        $resource1 = self::$uploadApi->upload(self::TEST_IMAGE_PATH, ['tags' => self::$ASSET_TAGS]);
        $resource2 = self::$uploadApi->upload(self::TEST_IMAGE_PATH, ['tags' => self::$ASSET_TAGS]);

        $result = self::$uploadApi->updateMetadata(
            self::$METADATA_FIELDS,
            [
                $resource1['public_id'],
                $resource2['public_id'],
            ]
        );

        self::assertCount(2, $result['public_ids']);
        self::assertContains($resource1['public_id'], $result['public_ids']);
        self::assertContains($resource2['public_id'], $result['public_ids']);
    }

    /**
     * Add eval parameter to an uploaded asset
     *
     * @throws ApiError
     * @throws \Exception
     */
    public function testEvalUploadParameter()
    {
        self::retryAssertionIfThrows(
            function () {
                $result = self::$uploadApi->upload(
                    self::TEST_IMAGE_PATH,
                    ['eval' => self::TEST_EVAL_STR, 'tags' => self::$ASSET_TAGS]
                );

                self::assertValidAsset(
                    $result,
                    [
                        'context' => ['custom' => ['width' => self::TEST_IMAGE_WIDTH]],
                    ]
                );
                self::assertIsArray($result['quality_analysis']);
                self::assertIsNumeric($result['quality_analysis']['focus']);
            },
            3,
            1,
            'Unable to use eval upload parameter'
        );
    }

    /**
     * Should successfully override original_filename.
     *
     * @throws ApiError
     */
    public function testFilenameOverride()
    {
        $asset = self::$uploadApi->upload(
            'http://cloudinary.com/images/old_logo.png',
            [
                'filename_override' => 'overridden',
                'tags'              => self::$ASSET_TAGS,
            ]
        );

        self::assertValidAsset($asset);
        self::assertEquals('overridden', $asset['original_filename']);
    }
}
