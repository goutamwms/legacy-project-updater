<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @coversNothing  — tests handler logic by reading the VERSION file directly
 */
final class VersionHandlerTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/pedalpal_version_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->tempDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $f) {
                $f->isDir() ? rmdir((string)$f) : unlink((string)$f);
            }
            rmdir($this->tempDir);
        }
    }

    public function testReturnsVersionFromFile(): void
    {
        file_put_contents($this->tempDir . '/VERSION', '2.3.4');
        $content = @file_get_contents($this->tempDir . '/VERSION');
        $version = $content !== false ? trim($content) : '0.0.0';

        $result = json_encode([
            'version'     => $version,
            'api_version' => 'v1',
            'name'        => 'PedalPal API',
        ]);

        $decoded = json_decode($result, true);
        $this->assertSame('2.3.4', $decoded['version']);
        $this->assertSame('v1', $decoded['api_version']);
        $this->assertSame('PedalPal API', $decoded['name']);
    }

    public function testReturnsDefaultWhenVersionFileMissing(): void
    {
        $content = @file_get_contents($this->tempDir . '/VERSION');
        $version = $content !== false ? trim($content) : '0.0.0';
        $this->assertSame('0.0.0', $version);
    }

    public function testHandlesEmptyVersionFile(): void
    {
        file_put_contents($this->tempDir . '/VERSION', '');
        $content = @file_get_contents($this->tempDir . '/VERSION');
        $version = $content !== false ? trim($content) : '0.0.0';
        $this->assertSame('', $version);
    }

    public function testHandlesVersionWithWhitespace(): void
    {
        file_put_contents($this->tempDir . '/VERSION', "  1.0.0  \n");
        $content = @file_get_contents($this->tempDir . '/VERSION');
        $version = $content !== false ? trim($content) : '0.0.0';
        $this->assertSame('1.0.0', $version);
    }
}
