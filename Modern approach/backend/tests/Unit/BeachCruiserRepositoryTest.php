<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class BeachCruiserRepositoryTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/pedalpal_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);

        $xml = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<BeachCruisers>
  <Bike>
    <bike_id>1</bike_id>
    <model_name>Sunset Drifter</model_name>
    <color>Coral</color>
    <frame_size>Medium</frame_size>
    <daily_rate>14.99</daily_rate>
    <is_available>true</is_available>
  </Bike>
  <Bike>
    <bike_id>2</bike_id>
    <model_name>Ocean Breeze</model_name>
    <color>Teal</color>
    <frame_size>Large</frame_size>
    <daily_rate>16.99</daily_rate>
    <is_available>false</is_available>
  </Bike>
</BeachCruisers>
XML;
        file_put_contents($this->tempDir . '/beach_cruisers.xml', $xml);
    }

    protected function tearDown(): void
    {
        $files = glob($this->tempDir . '/*');
        if ($files !== false) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        rmdir($this->tempDir);
    }

    public function testGetAllReturnsBikesFromXml(): void
    {
        $repo = new \BeachCruiserRepository($this->tempDir);
        $bikes = $repo->getAll();

        $this->assertCount(2, $bikes);
        $this->assertSame(1, $bikes[0]['bike_id']);
        $this->assertSame('Sunset Drifter', $bikes[0]['model_name']);
        $this->assertSame('Medium', $bikes[0]['frame_size']);
        $this->assertTrue($bikes[0]['is_available']);
        $this->assertFalse($bikes[1]['is_available']);
    }

    public function testGetAllCreatesCacheFile(): void
    {
        $repo = new \BeachCruiserRepository($this->tempDir);
        $repo->getAll();

        $cachePath = $this->tempDir . '/beach_cruisers.xml.cache';
        $this->assertFileExists($cachePath);

        $cached = json_decode(file_get_contents($cachePath), true);
        $this->assertIsArray($cached);
        $this->assertCount(2, $cached);
    }

    public function testSaveUpdatesXmlAndCache(): void
    {
        $repo = new \BeachCruiserRepository($this->tempDir);
        $bikes = $repo->getAll();

        $bikes[0]['is_available'] = false;
        $bikes[0]['color'] = 'Updated Color';
        $repo->save($bikes);

        $reloaded = $repo->getAll();
        $this->assertFalse($reloaded[0]['is_available']);
        $this->assertSame('Updated Color', $reloaded[0]['color']);

        $xml = file_get_contents($this->tempDir . '/beach_cruisers.xml');
        $this->assertStringContainsString('Updated Color', $xml);
    }

    public function testCacheInvalidatedOnDataChange(): void
    {
        $repo = new \BeachCruiserRepository($this->tempDir);
        $repo->getAll(); // populates cache

        // Modify the source file directly
        $xml = file_get_contents($this->tempDir . '/beach_cruisers.xml');
        $xml = str_replace('Sunset Drifter', 'Modified Bike', $xml);
        file_put_contents($this->tempDir . '/beach_cruisers.xml', $xml);

        // Give filemtime a chance to differ
        sleep(1);
        touch($this->tempDir . '/beach_cruisers.xml');

        $reloaded = $repo->getAll();
        $this->assertSame('Modified Bike', $reloaded[0]['model_name']);
    }

    public function testEmptyFileReturnsEmptyArray(): void
    {
        file_put_contents($this->tempDir . '/beach_cruisers.xml', '<?xml version="1.0"?><BeachCruisers/>');
        $repo = new \BeachCruiserRepository($this->tempDir);
        $this->assertSame([], $repo->getAll());
    }

    public function testCorruptedXmlReturnsEmptyArray(): void
    {
        file_put_contents($this->tempDir . '/beach_cruisers.xml', 'not valid xml at all');
        $repo = new \BeachCruiserRepository($this->tempDir);
        $this->assertSame([], $repo->getAll());
    }

    public function testMissingFileReturnsEmptyArray(): void
    {
        unlink($this->tempDir . '/beach_cruisers.xml');
        $repo = new \BeachCruiserRepository($this->tempDir);
        $this->assertSame([], $repo->getAll());
    }

    public function testStaleCacheIsIgnored(): void
    {
        $repo = new \BeachCruiserRepository($this->tempDir);
        $repo->getAll();

        // Modify source directly and make it newer than cache
        sleep(1);
        $xml = file_get_contents($this->tempDir . '/beach_cruisers.xml');
        $xml = str_replace('Sunset Drifter', 'Changed', $xml);
        file_put_contents($this->tempDir . '/beach_cruisers.xml', $xml);

        // Touch source to ensure newer mtime
        touch($this->tempDir . '/beach_cruisers.xml');

        $reloaded = $repo->getAll();
        $this->assertSame('Changed', $reloaded[0]['model_name']);
    }
}
