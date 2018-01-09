<?php

namespace Projektkater\NewsWordpressimport\Domain\Repository;

/**
 * This file is part of the "NewsWordpressimport" Extension for TYPO3 CMS.
 * TODO: this repository is not in use
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use GeorgRinger\News\Domain\Model\DemandInterface;
use GeorgRinger\News\Utility\Validation;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Repository for tag objects
 */
class TagRepository extends \GeorgRinger\News\Domain\Repository\TagRepository
{
    public function __construct() {
        $logger = GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        $this->logger = $logger;
        $this->logger->info('TagRepository __construct');
        //parent::__construct();
    }
    /**
     * Find tag by import source and import id
     *
     * @param string $importSource import source
     * @param int $importId import id
     * @return Tag
     */
    
    public function findOneByImportSourceAndImportId($importSource, $importId)
    {
        $this->logger->info('call findOneByImportSourceAndImportId');
        $query = $this->createQuery();
        $this->logger->info('call findOneByImportSourceAndImportId2');
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        return $query->matching(
            $query->logicalAnd(
                $query->equals('importSource', $importSource),
                $query->equals('importId', $importId)
            ))->execute()->getFirst();
    }
}
