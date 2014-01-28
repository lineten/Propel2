<?php

namespace Propel\Tests\Command;

use Propel\Generator\Command\MigrationDiffCommand;
use Propel\Generator\Command\MigrationDownCommand;
use Propel\Generator\Command\MigrationMigrateCommand;
use Propel\Generator\Command\MigrationUpCommand;
use Propel\Runtime\Propel;
use Propel\Tests\TestCase;
use Symfony\Component\Console\Application;

class MigrationTest extends TestCase
{
    protected static $output = '/../../../migrationdiff';

    public function testDiffCommand()
    {
        $app = new Application('Propel', Propel::VERSION);
        $command = new MigrationDiffCommand();
        $app->add($command);

        $outputDir = __DIR__ . self::$output;

        $files = glob($outputDir . '/PropelMigration_*.php');
        foreach ($files as $file) {
            unlink($file);
        }

        $input = new \Symfony\Component\Console\Input\ArrayInput(array(
            'command' => 'migration:diff',
            '--input-dir' => __DIR__ . '/../../../Fixtures/bookstore',
            '--output-dir' => $outputDir,
            '--platform' => ucfirst($this->getDriver()) . 'Platform',
            '--connection' => ['bookstore=' . $this->getConnectionDsn()],
            '--verbose' => true
        ));

        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        $app->setAutoExit(false);
        $result = $app->run($input, $output);

        if (0 !== $result) {
            echo $output->fetch();
        }

        $this->assertEquals(0, $result, 'migration:diff tests exited successfully');

        $files = glob($outputDir . '/PropelMigration_*.php');
        $this->assertGreaterThanOrEqual(1, count($files));
        $file = $files[0];

        $content = file_get_contents($file);
        $this->assertGreaterThan(100, substr_count($content, "\n"));
        $this->assertContains('CREATE TABLE ', $content);
    }

    public function testUpCommand()
    {
        $app = new Application('Propel', Propel::VERSION);
        $command = new MigrationUpCommand();
        $app->add($command);

        $outputDir = __DIR__ . self::$output;

        $input = new \Symfony\Component\Console\Input\ArrayInput(array(
            'command' => 'migration:up',
            '--input-dir' => __DIR__ . '/../../../Fixtures/bookstore',
            '--output-dir' => $outputDir,
            '--platform' => ucfirst($this->getDriver()) . 'Platform',
            '--connection' => ['bookstore=' . $this->getConnectionDsn()],
            '--verbose' => true
        ));

        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        $app->setAutoExit(false);
        $result = $app->run($input, $output);

        if (0 !== $result) {
            echo $output->fetch();
        }

        $this->assertEquals(0, $result, 'migration:up tests exited successfully');
        $outputString = $output->fetch();
        $this->assertContains('Migration complete.', $outputString);
    }

    public function testDownCommand()
    {
        $app = new Application('Propel', Propel::VERSION);
        $command = new MigrationDownCommand();
        $app->add($command);

        $outputDir = __DIR__ . self::$output;

        $input = new \Symfony\Component\Console\Input\ArrayInput(array(
            'command' => 'migration:down',
            '--input-dir' => __DIR__ . '/../../../Fixtures/bookstore',
            '--output-dir' => $outputDir,
            '--platform' => ucfirst($this->getDriver()) . 'Platform',
            '--connection' => ['bookstore=' . $this->getConnectionDsn()],
            '--verbose' => true
        ));

        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        $app->setAutoExit(false);
        $result = $app->run($input, $output);

        if (0 !== $result) {
            echo $output->fetch();
        }

        $this->assertEquals(0, $result, 'migration:down tests exited successfully');
        $outputString = $output->fetch();
        $this->assertContains('Reverse migration complete.', $outputString);
    }

    public function testMigrateCommand()
    {
        $app = new Application('Propel', Propel::VERSION);
        $command = new MigrationMigrateCommand();
        $app->add($command);

        $outputDir = __DIR__ . self::$output;

        $input = new \Symfony\Component\Console\Input\ArrayInput(array(
            'command' => 'migration:migrate',
            '--input-dir' => __DIR__ . '/../../../Fixtures/bookstore',
            '--output-dir' => $outputDir,
            '--platform' => ucfirst($this->getDriver()) . 'Platform',
            '--connection' => ['bookstore=' . $this->getConnectionDsn()],
            '--verbose' => true
        ));

        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        $app->setAutoExit(false);
        $result = $app->run($input, $output);

        if (0 !== $result) {
            echo $output->fetch();
        }

        $this->assertEquals(0, $result, 'migration:down tests exited successfully');
        $outputString = $output->fetch();
        $this->assertContains('Migration complete.', $outputString);

        //revert this migration change so we have the same database structure as before this test
        $this->testDownCommand();
    }

}