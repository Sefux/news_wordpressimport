<?php

namespace Projektkater\NewsWordpressimport\Service\Import;

/**
 * This file is part of the "news" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use GeorgRinger\News\Domain\Model\Tag;
use GeorgRinger\News\Domain\Model\FileReference;
use GeorgRinger\News\Domain\Service\AbstractImportService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Tag Import Service
 *
 */
class TagImportService extends \GeorgRinger\News\Domain\Service\AbstractImportService
{

    /**
     * @var \GeorgRinger\News\Domain\Repository\TagRepository
     */
    protected $tagRepository;

    /**
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     */
    protected $signalSlotDispatcher;

    public function __construct()
    {
        $logger = GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        $this->logger = $logger;

        parent::__construct();
    }

    /**
     * Inject the tag repository.
     *
     * @param \GeorgRinger\News\Domain\Repository\TagRepository $tagRepository
     */
    public function injectTagRepository(\GeorgRinger\News\Domain\Repository\TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * Inject SignalSlotDispatcher
     *
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher
     */
    public function injectSignalSlotDispatcher(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher)
    {
        $this->signalSlotDispatcher = $signalSlotDispatcher;
    }

    /**
     * @param array $importArray
     */
    public function import(array $importArray)
    {
        $this->logger->info(sprintf('Starting import for %s tags', count($importArray)));

        // Sort import array to import the default language first
        foreach ($importArray as $importItem) {
            $this->logger->info('import for tag ok');
            
            /*
            $arguments = ['importItem' => $importItem];
            $return = $this->emitSignal('preHydrate', $arguments);
            $importItem = $return['importItem'];
            */
            $this->logger->info('import for tag', $importItem);
            $tag = $this->hydrateTag($importItem);
            
        }
$this->logger->info(sprintf('Finished import for %s tags', count($importArray)));
        $this->persistenceManager->persistAll();

    }

    /**
     * Hydrate a tag record with the given array
     *
     * @param array $importItem
     * @return Tag
     */
    protected function hydrateTag(array $importItem)
    {
        $tag = $this->tagRepository->findOneByImportSourceAndImportId($importItem['import_source'],
            $importItem['import_id']);

        $this->logger->info(sprintf('Import of tag from source "%s" with id "%s"', $importItem['import_source'],
            $importItem['import_id']));

        if (is_null($tag)) {
            $this->logger->info('Tag is new');

            $tag = $this->objectManager->get(\GeorgRinger\News\Domain\Model\Tag::class);
            $this->tagRepository->add($tag);
        } else {
            $this->logger->info(sprintf('Tag exists already with id "%s".', $tag->getUid()));
        }

        $tag->setPid($importItem['pid']);
        $tag->setHidden($importItem['hidden']);
        $tag->setStarttime($importItem['starttime']);
        $tag->setEndtime($importItem['endtime']);
        $tag->setCrdate($importItem['crdate']);
        $tag->setTstamp($importItem['tstamp']);
        $tag->setTitle($importItem['title']);
        //TODO: add missing fields from tag model

        $tag->setImportId($importItem['import_id']);
        $tag->setImportSource($importItem['import_source']);

/*
        $arguments = ['importItem' => $importItem, 'tag' => $tag];
        $this->emitSignal('postHydrate', $arguments);
*/
        return $tag;
    }

    /**
     * Emits signal
     *
     * @param string $signalName name of the signal slot
     * @param array $signalArguments arguments for the signal slot
     */
    protected function emitSignal($signalName, array $signalArguments)
    {
        $this->signalSlotDispatcher->dispatch('GeorgRinger\\News\\Domain\\Service\\TagImportService', $signalName,
            $signalArguments);
    }
}
