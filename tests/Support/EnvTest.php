<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Support;

use NeneProfile\Support\Env;
use PHPUnit\Framework\TestCase;

final class EnvTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_ENV['NENE_TEST_KEY'], $_SERVER['NENE_TEST_KEY']);
        putenv('NENE_TEST_KEY');
    }

    public function test_reads_from_env_superglobal(): void
    {
        $_ENV['NENE_TEST_KEY'] = 'from-env';
        $this->assertSame('from-env', Env::get('NENE_TEST_KEY'));
    }

    public function test_reads_from_server_superglobal(): void
    {
        $_SERVER['NENE_TEST_KEY'] = 'from-server';
        $this->assertSame('from-server', Env::get('NENE_TEST_KEY'));
    }

    public function test_reads_from_getenv(): void
    {
        putenv('NENE_TEST_KEY=from-getenv');
        $this->assertSame('from-getenv', Env::get('NENE_TEST_KEY'));
    }

    public function test_env_takes_precedence_over_getenv(): void
    {
        $_ENV['NENE_TEST_KEY'] = 'win';
        putenv('NENE_TEST_KEY=lose');
        $this->assertSame('win', Env::get('NENE_TEST_KEY'));
    }

    public function test_returns_default_when_absent(): void
    {
        $this->assertSame('fallback', Env::get('NENE_DEFINITELY_ABSENT_KEY', 'fallback'));
        $this->assertSame('', Env::get('NENE_DEFINITELY_ABSENT_KEY'));
    }
}
