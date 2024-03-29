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
use Cloudinary\MediaManagement\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Constraint\IsType;

/**
 * Class TagsTest
 */
final class TagsTest extends IntegrationTestCase
{
    private static $PREFIX_TAG;
    private static $TAG_WITH_PREFIX;

    /**
     * @throws ApiError
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$PREFIX_TAG      = 'tags_prefix_tag_' . self::$UNIQUE_TEST_TAG;
        self::$TAG_WITH_PREFIX = self::$PREFIX_TAG . self::$UNIQUE_TEST_TAG;

        self::createTestAssets(
            [
                [
                    'options' => ['tags' => [self::$TAG_WITH_PREFIX]],
                ],
            ]
        );
    }

    public static function tearDownAfterClass(): void
    {
        self::cleanupTestAssets();

        parent::tearDownAfterClass();
    }

    /**
     * List tags of images
     *
     * @throws ApiError
     */
    public function testListTagsOfImages()
    {
        $result = self::$adminApi->tags(['max_results' => 2]);

        self::assertObjectStructure($result, ['tags' => IsType::TYPE_ARRAY]);
        self::assertCount(2, $result['tags']);
        self::assertIsString($result['tags'][0]);
    }

    /**
     * List all tags with a given prefix
     */
    public function testListTagsWithPrefix()
    {
        $result = self::$adminApi->tags(['prefix' => self::$PREFIX_TAG]);

        self::assertObjectStructure($result, ['tags' => IsType::TYPE_ARRAY]);
        self::assertCount(1, $result['tags']);
        self::assertContains(self::$TAG_WITH_PREFIX, $result['tags']);
    }
}
