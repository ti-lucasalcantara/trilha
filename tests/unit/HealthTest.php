<?php

use CodeIgniter\Test\CIUnitTestCase;
use Config\App;

/**
 * @internal
 */
final class HealthTest extends CIUnitTestCase
{
    public function testIsDefinedAppPath(): void
    {
        $this->assertDirectoryExists(APPPATH);
    }

    public function testBaseUrlHasBeenSet(): void
    {
        $config = config(App::class);
        $this->assertTrue(
            service('validation')->check($config->baseURL, 'valid_url'),
            'A URL base efetiva do aplicativo deve ser válida.',
        );
    }
}
