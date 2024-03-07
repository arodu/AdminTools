<?php
declare(strict_types=1);

namespace AdminTools\Test\TestCase\Command;

use AdminTools\Command\BackupEmailCommand;
use Cake\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * AdminTools\Command\BackupEmailCommand Test Case
 *
 * @uses \AdminTools\Command\BackupEmailCommand
 */
class BackupEmailCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->useCommandRunner();
    }
    /**
     * Test buildOptionParser method
     *
     * @return void
     * @uses \AdminTools\Command\BackupEmailCommand::buildOptionParser()
     */
    public function testBuildOptionParser(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test execute method
     *
     * @return void
     * @uses \AdminTools\Command\BackupEmailCommand::execute()
     */
    public function testExecute(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
