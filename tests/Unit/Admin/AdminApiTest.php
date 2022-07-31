<?php
/**
 * This file is part of the Cloudinary Media Management PHP package.
 *
 * (c) Cloudinary
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cloudinary\MediaManagement\Test\Unit\Admin;

use Cloudinary\MediaManagement\Test\Helpers\MockAdminApi;
use Cloudinary\MediaManagement\Test\Helpers\RequestAssertionsTrait;
use Cloudinary\MediaManagement\Test\Unit\UnitTestCase;

/**
 * Class AdminApiTest
 */
final class AdminApiTest extends UnitTestCase
{
    use RequestAssertionsTrait;

    /**
     * Should allow the user to pass accessibility_analysis in the asset function.
     */
    public function testAccessibilityAnalysisResource()
    {
        $mockAdminApi = new MockAdminApi();
        $mockAdminApi->asset(self::$UNIQUE_TEST_ID, ['accessibility_analysis' => true]);
        $lastRequest = $mockAdminApi->getMockHandler()->getLastRequest();

        self::assertRequestQueryStringSubset($lastRequest, ['accessibility_analysis' => '1']);
    }
}
