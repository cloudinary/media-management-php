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

use Cloudinary\MediaManagement\Configuration\Configuration;
use Cloudinary\MediaManagement\Configuration\ConfigUtils;
use Cloudinary\MediaManagement\Test\CloudinaryTestCase;

/**
 * Class UnitTestCase
 *
 * Base class for all Unit tests.
 */
abstract class UnitTestCase extends CloudinaryTestCase
{
    const CLOUD_NAME = 'test123';
    const API_KEY    = 'key';
    const API_SECRET = 'secret';

    const SECURE_CNAME = 'secure-dist';

    const TEST_LOGGING = ['logging' => ['test' => ['level' => 'debug']]];

    protected $cloudinaryUrl;

    private $cldUrlEnvBackup;

    public function setUp(): void
    {
        parent::setUp();

        $this->cldUrlEnvBackup = getenv(Configuration::CLOUDINARY_URL_ENV_VAR);

        self::assertNotEmpty($this->cldUrlEnvBackup, 'Please set up CLOUDINARY_URL before running tests!');

        $this->cloudinaryUrl = 'cloudinary://' . $this::API_KEY . ':' . $this::API_SECRET . '@' . $this::CLOUD_NAME;

        putenv(Configuration::CLOUDINARY_URL_ENV_VAR . '=' . $this->cloudinaryUrl);

        $config = ConfigUtils::parseCloudinaryUrl(getenv(Configuration::CLOUDINARY_URL_ENV_VAR));
        $config = array_merge($config, self::TEST_LOGGING);
        Configuration::instance()->init($config);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        putenv(Configuration::CLOUDINARY_URL_ENV_VAR . '=' . $this->cldUrlEnvBackup);
    }

    protected static function clearEnvironment()
    {
        putenv(Configuration::CLOUDINARY_URL_ENV_VAR); // unset CLD_MEDIA_MANAGEMENT

        Configuration::instance()->init();
    }
}
