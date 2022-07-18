<?php
/**
 * This file is part of the Cloudinary Media Management PHP package.
 *
 * (c) Cloudinary
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cloudinary\Test;

use Cloudinary\Cloudinary;
use Cloudinary\StringUtils;
use Exception;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\Constraint\LogicalOr;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;

if (! defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
    //PHP < 7.2 Define it as 0 so it does nothing
    define('JSON_INVALID_UTF8_SUBSTITUTE', 0);
}

/**
 * Class CloudinaryTestCase
 *
 * Base class for all tests.
 */
abstract class CloudinaryTestCase extends TestCase
{
    const ASSET_ID = 'sample';

    const IMG_EXT        = 'png';
    const IMG_EXT_JPG    = 'jpg';
    const IMG_EXT_GIF    = 'gif';
    const IMAGE_NAME     = self::ASSET_ID . '.' . self::IMG_EXT;
    const IMAGE_NAME_GIF = self::ASSET_ID . '.' . self::IMG_EXT_GIF;

    const VID_EXT    = 'mp4';
    const VIDEO_NAME = self::ASSET_ID . '.' . self::VID_EXT;

    const FILE_EXT  = 'bin';
    const FILE_NAME = self::ASSET_ID . '.' . self::FILE_EXT;

    const DOCX_EXT  = 'docx';
    const DOCX_NAME = self::ASSET_ID . '.' . self::DOCX_EXT;

    const FD_PID_PREFIX      = 'fd_public_id_prefix';
    const ASSET_DISPLAY_NAME = 'test';

    const ASSET_FOLDER    = 'asset_folder';
    const FOLDER          = 'test_folder';
    const NESTED_FOLDER   = 'folder/test';
    const IMAGE_IN_FOLDER = self::FOLDER . '/' . self::IMAGE_NAME;

    const TEST_BASE64_IMAGE = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

    const TEST_SOCIAL_PROFILE_ID   = 65646572251;
    const TEST_SOCIAL_PROFILE_NAME = 'johndoe';
    const TEST_EMAIL               = 'info@cloudinary.com';

    protected static $SUFFIX;
    protected static $TEST_TAG;
    protected static $UNIQUE_TEST_TAG;
    protected static $ASSET_TAGS;
    protected static $UNIQUE_TEST_ID;
    protected static $UNIQUE_TEST_ID2;

    protected static $skipAllTests = false;
    protected static $skipReason;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$SUFFIX          = getenv('TRAVIS_JOB_ID') ?: mt_rand(11111, 99999);
        self::$TEST_TAG        = 'cloudinary_mm_php_v' . str_replace(['.', '-'], '_', Cloudinary::VERSION);
        self::$UNIQUE_TEST_TAG = self::$TEST_TAG . '_' . self::$SUFFIX;
        self::$UNIQUE_TEST_ID  = self::$UNIQUE_TEST_TAG;
        self::$UNIQUE_TEST_ID2 = self::$UNIQUE_TEST_ID . '_2';
        self::$ASSET_TAGS      = [self::$TEST_TAG, self::$UNIQUE_TEST_TAG];
    }

    /**
     * @param $obj
     *
     * @return array
     */
    protected static function objectToArray($obj)
    {
        if (is_object($obj)) {
            $obj = (array)$obj;
        }
        if (is_array($obj)) {
            $new = [];
            foreach ($obj as $key => $val) {
                $new[$key] = self::objectToArray($val);
            }
        } else {
            $new = $obj;
        }

        return $new;
    }

    /**
     * @param $object1
     * @param $object2
     */
    protected static function assertObjectsEqual($object1, $object2)
    {
        self::assertEquals(self::objectToArray($object1), self::objectToArray($object2));
    }

    /**
     * Reports an error if the $haystack array does not contain the $needle array.
     *
     * @param array $haystack
     * @param array $needle
     */
    protected static function assertArrayContainsArray($haystack, $needle)
    {
        $result = array_filter(
            $haystack,
            static function ($item) use ($needle) {
                /** @noinspection TypeUnsafeComparisonInspection */
                return $item == $needle;
            }
        );

        self::assertGreaterThanOrEqual(1, count($result), 'The $haystack array does not contain the $needle array');
    }

    /**
     * Asserts that a variable type is of one or more given types.
     *
     * @param array|string $expected
     * @param mixed        $actual
     * @param string       $message
     */
    public static function assertOneOfInternalTypes($expected, $actual, $message = '')
    {
        if (is_string($expected)) {
            $expected = [$expected];
        }

        $constraints = [];
        foreach ($expected as $expectedType) {
            $constraints[] = new IsType($expectedType);
        }

        $orConstraint = new LogicalOr();
        $orConstraint->setConstraints($constraints);

        static::assertThat($actual, $orConstraint, $message);
    }

    /**
     * Tries a function and retries several times if throws.
     * Throws an AssertionFailedError if doesn't succeed after several retries
     *
     * @param callable $f
     * @param int      $retries
     * @param int      $delay
     * @param string   $message
     *
     * @return callable
     *
     * @throws Exception
     */
    public static function retryAssertionIfThrows($f, $retries = 3, $delay = 3, $message = '')
    {
        for ($i = 0; $i < $retries; $i++) {
            try {
                return $f();
            } catch (Exception $e) {
                $message = $message ?: $e->getMessage(); // save error message, if not provided
                $i === $retries - 1 ?: sleep($delay); // prevent sleep on the last loop
            }
        }

        self::fail($message);

        return $f();
    }

    /**
     * Helper for invoking non-public class method
     *
     * @param mixed  $class      Classname or object (instance of the class) that contains the method.
     * @param string $methodName Name of the method, or the method FQN in the form 'Foo::bar' if $class argument missing
     * @param mixed  ...$args    The method arguments
     *
     * @return mixed
     */
    public static function invokeNonPublicMethod($class, $methodName, ...$args)
    {
        $classInstance = is_string($class) ? null : $class;

        try {
            $method = new ReflectionMethod($class, $methodName);
        } catch (ReflectionException $e) {
            // oops
            self::fail((string)$e);

            // we actually never get here
            return null;
        }

        $method->setAccessible(true);

        return $method->invoke($classInstance, ...$args);
    }

    /**
     * Reports an error if the $haystack array does not contain the instance of $className.
     *
     * @param string $className Name of the class to find an instance of
     * @param array  $haystack  The array to search through
     */
    public static function assertContainsInstancesOf($className, array $haystack)
    {
        $instanceFound = false;
        foreach ($haystack as $object) {
            if ($object instanceof $className) {
                $instanceFound = true;
            }
        }
        self::assertTrue($instanceFound, 'The $haystack array does not contain an instance of ' . $className);
    }

    /**
     * Asserts that string representations of the objects are equal.
     *
     * @param mixed  $expected
     * @param mixed  $actual
     * @param string $message
     */
    public static function assertStrEquals($expected, $actual, $message = '')
    {
        self::assertEquals((string)$expected, (string)$actual, $message);
    }

    /**
     * Asserts that a given object logged a message of a certain level
     *
     * @param object     $obj     The object that should have logged a message
     * @param string     $message The message that was logged
     * @param string|int $level   Logging level value or name
     *
     * @throws ReflectionException
     */
    protected static function assertObjectLoggedMessage($obj, $message, $level)
    {
        $reflectionMethod = new ReflectionMethod(get_class($obj), 'getLogger');
        $reflectionMethod->setAccessible(true);
        $logger = $reflectionMethod->invoke($obj);
        /** @var TestHandler $testHandler */
        $testHandler = $logger->getTestHandler();

        self::assertInstanceOf(TestHandler::class, $testHandler);

        if (3 === Logger::API) {
            $level = is_string($level) ? Level::fromName($level) : Level::fromValue($level);
        }

        self::assertTrue(
            $testHandler->hasRecordThatContains($message, $level),
            sprintf('Object %s did not log the message or logged it with a different level', get_class($obj))
        );
    }

    /**
     * Assert that an asset has certain keys of certain types
     *
     * @param array|object $asset
     * @param array        $keys
     * @param string       $message
     */
    protected static function assertObjectStructure($asset, array $keys, $message = '')
    {
        foreach ($keys as $key => $type) {
            $value = is_object($asset) && property_exists($asset, $key) ? $asset->{$key} : $asset[$key];

            self::assertOneOfInternalTypes((array)$type, $value, $message);
        }
    }

    /**
     * Backward compatibility layer for deprecated assertArraySubset function.
     *
     * @param array  $subset
     * @param array  $array
     * @param string $message
     */
    public static function assertSubset(array $subset, array $array, $message = '')
    {
        foreach ($subset as $key => $value) {
            self::assertArrayHasKey($key, $array);
            self::assertEquals($value, $array[$key], $message);
        }
    }

    /**
     * Generate a data provider.
     *
     * @param        $array
     * @param string $prefixValue
     * @param string $suffixValue
     * @param string $prefixMethod
     * @param string $suffixMethod
     *
     * @return array[]
     */
    protected static function generateDataProvider(
        $array,
        $prefixValue = '',
        $suffixValue = '',
        $prefixMethod = '',
        $suffixMethod = ''
    ) {
        return array_map(
            static function ($value) use ($prefixValue, $suffixValue, $prefixMethod, $suffixMethod) {
                return [
                    'value'  => $prefixValue . $value . $suffixValue,
                    'method' => StringUtils::snakeCaseToCamelCase($prefixMethod . $value . $suffixMethod),
                ];
            },
            $array
        );
    }
}
