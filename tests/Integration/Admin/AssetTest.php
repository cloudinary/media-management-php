<?php
/**
 * This file is part of the Cloudinary Media Management PHP package.
 *
 * (c) Cloudinary
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cloudinary\MediaManagement\Test\Integration\Admin;

use Cloudinary\MediaManagement\Api\Exception\ApiError;
use Cloudinary\MediaManagement\Asset\AssetType;
use Cloudinary\MediaManagement\Asset\DeliveryType;
use Cloudinary\MediaManagement\Asset\ModerationStatus;
use Cloudinary\MediaManagement\Asset\ModerationType;
use Cloudinary\MediaManagement\Test\Integration\IntegrationTestCase;

/**
 * Class AssetTest
 */
final class AssetTest extends IntegrationTestCase
{
    const EXTRA_INFO = ['colors' => true, 'faces' => true];

    const ASSET_IMAGE = 'asset_image';
    const ASSET_DOCX  = 'asset_docx';

    private static $UNIQUE_CONTEXT;
    private static $UNIQUE_CONTEXT_KEY;
    private static $UNIQUE_CONTEXT_VALUE;

    /**
     * @throws ApiError
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$UNIQUE_CONTEXT_KEY   = 'asset_context_key_' . self::$UNIQUE_TEST_ID;
        self::$UNIQUE_CONTEXT_VALUE = 'asset_context_value_' . self::$UNIQUE_TEST_ID;
        self::$UNIQUE_CONTEXT       = [self::$UNIQUE_CONTEXT_KEY => self::$UNIQUE_CONTEXT_VALUE];

        self::createTestAssets(
            [
                self::ASSET_IMAGE => [
                    'options' => [
                        ModerationType::KEY => ModerationType::MANUAL,
                    ],
                ],
                self::ASSET_DOCX  => [
                    'options' => [
                        ModerationType::KEY => ModerationType::MANUAL,
                        AssetType::KEY      => AssetType::RAW,
                        'file'              => self::TEST_DOCX_PATH,
                    ],
                ],
            ]
        );
    }

    public static function tearDownAfterClass(): void
    {
        self::cleanupTestAssets([AssetType::IMAGE, AssetType::RAW]);

        parent::tearDownAfterClass();
    }

    /**
     * Get uploaded image details without extra info
     */
    public function testGetUploadedImageDetailsNoExtraInfo()
    {
        $result = self::$adminApi->asset(self::getTestAssetPublicId(self::ASSET_IMAGE));

        self::assertValidAsset($result);
        self::assertArrayNotHasKey('accessibility_analysis', $result);
        self::assertArrayNotHasKey('colors', $result);
        self::assertArrayNotHasKey('faces', $result);
    }

    /**
     * Get uploaded image details including faces, colors and Exif info
     */
    public function testGetUploadedImageDetailsWithExtraInfo()
    {
        $result = self::$adminApi->asset(
            self::getTestAssetPublicId(self::ASSET_IMAGE),
            self::EXTRA_INFO
        );

        self::assertValidAsset(
            $result,
            [
                'colors' => [],
                'faces'  => [],
            ]
        );
    }

    /**
     * Get uploaded image details by asset_id without extra info
     */
    public function testGetUploadedImageDetailsByAssetId()
    {
        $result = self::$adminApi->assetByAssetId(self::getTestAssetAssetId(self::ASSET_IMAGE));

        self::assertValidAsset($result);
        self::assertArrayNotHasKey('accessibility_analysis', $result);
        self::assertArrayNotHasKey('colors', $result);
        self::assertArrayNotHasKey('faces', $result);
    }

    /**
     * Get uploaded image details by asset_id including faces, colors and Exif info
     */
    public function testGetUploadedImageDetailsByAssetIdWithExtraInfo()
    {
        $result = self::$adminApi->assetByAssetId(self::getTestAssetAssetId(self::ASSET_IMAGE), self::EXTRA_INFO);

        self::assertValidAsset(
            $result,
            [
                'colors' => [],
                'faces'  => [],
            ]
        );
    }

    /**
     * Get accessibility analysis of an an uploaded image
     */
    public function testGetUploadedImageAccessibilityAnalysis()
    {
        $result = self::$adminApi->asset(
            self::getTestAssetPublicId(self::ASSET_IMAGE),
            [
                'accessibility_analysis' => true,
            ]
        );

        self::assertArrayHasKey('accessibility_analysis', $result);
    }

    /**
     * Get Facebook picture details
     */
    public function testGetFacebookImageDetails()
    {
        $this->markTestSkipped('FIXME: Fetch facebook image before running this test');

        $result = self::$adminApi->asset('sample', [DeliveryType::KEY => DeliveryType::FACEBOOK]);

        self::assertValidAsset($result, [DeliveryType::KEY => DeliveryType::FACEBOOK]);
    }

    /**
     * Get uploaded raw file details
     */
    public function testGetUploadedRawFileDetails()
    {
        $result = self::$adminApi->asset(
            self::getTestAssetPublicId(self::ASSET_DOCX),
            [
                AssetType::KEY => AssetType::RAW,
            ]
        );

        self::assertValidAsset(
            $result,
            [
                DeliveryType::KEY => DeliveryType::UPLOAD,
                AssetType::KEY    => AssetType::RAW,
                'bytes'           => 20453,
            ]
        );
    }

    /**
     * Update one or more of the attributes associated with a specified asset
     */
    public function testUpdateImageAttributes()
    {
        $result = self::$adminApi->update(
            self::getTestAssetPublicId(self::ASSET_IMAGE),
            [
                ModerationStatus::KEY => ModerationStatus::APPROVED,
                'context'             => self::$UNIQUE_CONTEXT,
            ]
        );

        self::assertValidAsset($result);
        self::assertEquals(ModerationStatus::APPROVED, $result['moderation'][0]['status']);
        self::assertEquals(self::$UNIQUE_CONTEXT, $result['context']['custom']);

        $result = self::$adminApi->asset(
            self::getTestAssetPublicId(self::ASSET_IMAGE)
        );

        self::assertValidAsset($result);
        self::assertEquals(ModerationStatus::APPROVED, $result['moderation'][0]['status']);
        self::assertEquals(self::$UNIQUE_CONTEXT, $result['context']['custom']);
    }
}
