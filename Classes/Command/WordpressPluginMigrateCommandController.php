<?php
namespace Projektkater\NewsWordpressimport\Command;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * A Command Controller which provides help for available commands
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class WordpressPluginMigrateCommandController extends CommandController
{

    const WHERE_CLAUSE = 'deleted=0 AND list_type="9" AND CType="list"';

    /**
     * Check if any plugin needs to be migrated
     */
    public function checkCommand()
    {
        $this->outputDashedLine();
        $this->outputLine('List of plugins:');
        $this->outputLine('%-2s% -5s %s',
            array(' ', $this->getNewsPluginCount('hidden=0 AND news_wordpressimport_new_id != ""'), 'already migrated'));
        $this->outputLine('%-2s% -5s %s', array(
            ' ',
            $this->getNewsPluginCount('hidden=1 AND news_wordpressimport_new_id != ""'),
            'already migrated but hidden'
        ));
        $this->outputLine('%-2s% -5s %s',
            array(' ', $this->getNewsPluginCount('hidden=0 AND news_wordpressimport_new_id = ""'), 'not yet migrated'));
        $this->outputLine('%-2s% -5s %s', array(
            ' ',
            $this->getNewsPluginCount('hidden=1 AND news_wordpressimport_new_id = ""'),
            'not yet migrated and hidden'
        ));
        $this->outputDashedLine();
        $this->outputLine();
    }

    /**
     * Create news plugins below each tt_news plugin
     */
    public function runCommand()
    {
        /** @var \Projektkater\NewsWordpressimport\Service\Migrate\WordpressPluginMigrate $migrate */
        $migrate = $this->objectManager->get('Projektkater\\NewsWordpressimport\\Service\\Migrate\\WordpressPluginMigrate');
        $migrate->run();
    }

    /**
     * REPLACE tt_news plugins
     */
    public function replaceCommand()
    {
        /** @var \Projektkater\NewsWordpressimport\Service\Migrate\WordpressPluginMigrate $migrate */
        $migrate = $this->objectManager->get('Projektkater\\NewsWordpressimport\\Service\\Migrate\\WordpressPluginMigrate');
        $migrate->replace();
    }

    /**
     * Remove tt_news plugins
     *
     * @param bool $delete Set to TRUE to delete the plugins instead of hiding
     */
    public function removeOldPluginsCommand($delete = false)
    {
        $update = $delete ? array('deleted' => 1) : array('hidden' => 1);
        $this->getDatabaseConnection()->exec_UPDATEquery('tt_content', self::WHERE_CLAUSE, $update);
    }

    /**
     * Get count of tt_news plugins
     *
     * @param string $additionalWhere
     * @return int
     */
    protected function getNewsPluginCount($additionalWhere)
    {
        return $this->getDatabaseConnection()->exec_SELECTcountRows(
            '*',
            'tt_content',
            self::WHERE_CLAUSE . ' AND ' . $additionalWhere
        );
    }

    /**
     * @param string $char
     */
    protected function outputDashedLine($char = '-')
    {
        $this->outputLine(str_repeat($char, 79));
    }

    /**
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
