<?php
/**
 * This file is part of the Cloudinary Media Management PHP package.
 *
 * (c) Cloudinary
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cloudinary\MediaManagement\Test\Unit;

use Cloudinary\MediaManagement\Exception\Error;
use Cloudinary\MediaManagement\HttpClient;
use Monolog\Logger as Monolog;
use ReflectionException;

/**
 * Class HttpClientTest
 */
final class HttpClientTest extends UnitTestCase
{
    /**
     * @throws ReflectionException
     */
    public function testLoggingParseInvalidJsonResponse(): void
    {
        $httpClient = new HttpClient();

        $message = null;
        $expectedLogMessage = 'Error parsing JSON server response';
        $expectedExceptionMessage = 'Error parsing server response';
        try {
            $httpClient->getJson('http://cloudinary.com/');
        } catch (Error $e) {
            $message = $e->getMessage();
        }

        self::assertStringStartsWith($expectedExceptionMessage, $message);
        self::assertObjectLoggedMessage($httpClient, $expectedLogMessage, Monolog::CRITICAL);
    }
}
