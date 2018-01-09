<?php

namespace Projektkater\NewsWordpressimport\Aspect;

/**
 * This file is part of the "news_wordpressimport" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use GeorgRinger\News\Domain\Model\News;
use GeorgRinger\News\Domain\Repository\TagRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Persist dynamic data of import
 */
class NewsImportAspect
{
    /**
     * @var \GeorgRinger\News\Domain\Repository\TagRepository
     */
    protected $tagRepository;

    /**
	*	constructor
    */    
	public function __construct() {
		//$logger = GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
		//$this->logger = $logger;
        
        $objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        $this->tagRepository = $objectManager->get('GeorgRinger\News\Domain\Repository\TagRepository');
	}
    
    /**
     * @param array $importData
     * @param \GeorgRinger\News\Domain\Model\News $news
     */
    public function postHydrate(array $importData, $news)
    {
        if(!empty($importData['tags'])) {
			//$this->logger->info('ASPECT: add tags',(array) $importData['tags']);
            foreach($importData['tags'] as $tagId) {
                    $tag = $this->tagRepository->findByUid($tagId);
                    /** @var News $news */
                    $news->addTag($tag);
                    //$this->logger->info('ASPECT: add tag',(array) $tag);
            }
        }
    }
}
