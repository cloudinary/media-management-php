<?php
/**
 * This file is part of the Cloudinary Media Management PHP package.
 *
 * (c) Cloudinary
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cloudinary\MediaManagement\Test\Integration\Admin\Assets;

use Cloudinary\MediaManagement\Api\Exception\ApiError;
use Cloudinary\MediaManagement\Asset\AssetType;
use Cloudinary\MediaManagement\Test\Integration\IntegrationTestCase;

/**
 * Class RestoreAssetsTest
 */
final class RestoreAssetsTest extends IntegrationTestCase
{
    const RESTORE_ASSET  = 'restore_asset';
    const BACKUP_ASSET_1 = 'backup_asset_1';

    private static $UNIQUE_TEST_TAG_RESTORE;

    /**
     * @throws ApiError
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$UNIQUE_TEST_TAG_RESTORE = 'asset_restore_' . self::$UNIQUE_TEST_TAG;

        self::createTestAssets(
            [
                self::RESTORE_ASSET => [
                    'options' => [
                        'tags'   => [self::$UNIQUE_TEST_TAG_RESTORE],
                        'backup' => true,
                    ],
                ],
                self::BACKUP_ASSET_1 => [
                    'options' => [
                        'backup' => true,
                    ],
                ]
            ]
        );
    }

    public static function tearDownAfterClass(): void
    {
        self::cleanupTestAssets();

        parent::tearDownAfterClass();
    }

    /**
     * Restore deleted assets by public_id.
     *
     * @throws ApiError
     */
    public function testRestoreDeletedAssetsByPublicId()
    {
        $result = self::$adminApi->deleteAssets(
            [
                self::getTestAssetPublicId(self::RESTORE_ASSET),
            ]
        );

        self::assertAssetDeleted(
            $result,
            self::getTestAssetPublicId(self::RESTORE_ASSET)
        );

        $result = self::$adminApi->assetsByTag(self::$UNIQUE_TEST_TAG_RESTORE);

        self::assertEmpty($result['resources']);

        $result = self::$adminApi->restore(
            self::getTestAssetPublicId(self::RESTORE_ASSET)
        );

        self::assertEquals(
            AssetType::IMAGE,
            $result[self::getTestAssetPublicId(self::RESTORE_ASSET)][AssetType::KEY]
        );

        $result = self::$adminApi->assetsByTag(self::$UNIQUE_TEST_TAG_RESTORE);

        self::assertCount(1, $result['resources']);
    }

    /**
     * Restore two different deleted assets.
     *
     * @throws ApiError
     */
    public function testRestoreDifferentDeletedAssets()
    {
        $deleteResult = self::$adminApi->deleteAssets(
            [
                self::getTestAssetPublicId(self::BACKUP_ASSET_1),
            ]
        );

        self::assertAssetDeleted($deleteResult, self::getTestAssetPublicId(self::BACKUP_ASSET_1), 1);

        $secondAsset = self::$adminApi->asset(
            self::getTestAssetPublicId(self::BACKUP_ASSET_1),
            ['versions' => true]
        );

        $restoreResult = self::$adminApi->restore(
            [
                self::getTestAssetPublicId(self::BACKUP_ASSET_1),
            ],
            [
                'versions' => [
                    $secondAsset['versions'][0]['version_id'],
                ],
            ]
        );

        self::assertEquals(
            $restoreResult[self::getTestAssetPublicId(self::BACKUP_ASSET_1)]['bytes'],
            self::getTestAsset(self::BACKUP_ASSET_1)['bytes']
        );
    }

    /**
     * Gets asset backups.
     */
    public function testBackupAsset()
    {
        $asset = self::$adminApi->asset(
            self::getTestAssetPublicId(self::RESTORE_ASSET),
            [
                'versions' => true
            ]
        );

        self::assertGreaterThanOrEqual(1, $asset['versions']);
    }
}
