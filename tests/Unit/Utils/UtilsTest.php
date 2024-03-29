<?php
/**
 * This file is part of the Cloudinary Media Management PHP package.
 *
 * (c) Cloudinary
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cloudinary\MediaManagement\Test\Unit\Utils;

use Cloudinary\MediaManagement\Utils;
use PHPUnit\Framework\TestCase;

/**
 * Class UtilsTest
 */
final class UtilsTest extends TestCase
{
    public function testFloatToString()
    {
        self::assertEquals(
            '1.4142135623731',
            Utils::floatToString(sqrt(2))
        );
    }
}
