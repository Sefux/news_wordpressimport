<?php
namespace Projektkater\NewsWordpressimport\Service\Import;

/***************************************************************
*  Copyright notice
*
*  (c) 2011 Nikolas Hagelstein <nikolas.hagelstein@gmail.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

use GeorgRinger\News\Service\Import\DataProviderServiceInterface;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Utility\PathUtility;
use \TYPO3\CMS\Core\Utility\DebugUtility;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

/**
 * wordpress ImportService
 *
 * @package TYPO3
 * @subpackage news_wordpressimport
 */
class WordpressNewsDataProviderService implements DataProviderServiceInterface, \TYPO3\CMS\Core\SingletonInterface  {

	protected $importSource = 'WP_NEWS_IMPORT';
	protected $importPid = 21;

	/**
	*	constructor
	*/
	public function __construct() {
		$logger = GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
		$this->logger = $logger;
	}

	
	/**
	 * Get total record count
	 *
	 * @return integer
	 */
	public function getTotalRecordCount() {
		$count = 0;
		/*
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('count(*)',
			'wp_posts',
			'posts_type="POST" and post_status="publish"'
		);
		list($count) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
		$GLOBALS['TYPO3_DB']->sql_free_result($res);			
*/
		
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('wp_posts');
		
		$statement = $queryBuilder
				->count('*')
    			->from('wp_posts')    
				->where(
				    $queryBuilder->expr()->eq('post_type', '"POST"'),
					$queryBuilder->expr()->eq('post_status', '"publish"')
				)
    			->execute();
		$sql = $queryBuilder->getSQL();
		$count = $statement->fetchColumn(0);
		
		$this->logger->info(sprintf('START: Counting wordpress posts: %s ', $count));
		
	//	$this->logger->info(sprintf('SQL:   "%s" ', $sql));
		
		//return 10;
		return (int)$count;
	}

	/**
	 * Get the partial import data, based on offset + limit
	 *
	 * @param integer $offset offset
	 * @param integer $limit limit
	 * @return array
	 */
	public function getImportData($offset = 0, $limit = 50) {
		$importData = array();
		/*
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',
			'wp_posts',
			'posts_type="POST" and post_status="publish"',
			'',
			'post_date DESC',
			$offset . ',' . $limit
		);
		*/
		//echo 'check';
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('wp_posts');
		$statement = $queryBuilder->select('*')
    			->from('wp_posts')    
				->where(
					$queryBuilder->expr()->eq('post_type', '"POST"'),
					$queryBuilder->expr()->eq('post_status', '"publish"')
				)
				->orderBy('post_date')
				->setMaxResults($limit)
   				->setFirstResult($offset)
    			->execute();
		$sql = $queryBuilder->getSQL();
		//$this->logger->info(sprintf('IMPORT POSTS SQL:   "%s" ', $sql));

		while ($row = $statement->fetch()) {
			$importData[] = array(
				'pid' => $this->importPid,
				'hidden' => ($row['post_status']=="publish" ? 0 : 1),
				'tstamp' => strtotime($row['post_modified']),
				'crdate' => strtotime($row['post_date']),
				'cruser_id' => $row['post_author'],
				//'l10n_parent' => $row['l18n_parent'],
				//'sys_language_uid' => $row['sys_language_uid'],
				//'sorting' => array_key_exists('sorting', $row) ? $row['sorting'] : 0,
				//'starttime' => $row['starttime'],
				//'endtime'  => $row['endtime'],
				//'fe_group'  => $row['fe_group'],
				'title' => $row['post_title'],
				'teaser' => $row['post_excerpt'],
				'bodytext' => str_replace('###YOUTUBEVIDEO###', '', $row['post_content']),
				'datetime' => strtotime($row['post_date']),
				//'archive' => $row['archivedate'],
				'author' => $row['post_author'],
				//'author_email' => $row['author_email'],
				'type' => 0,//$row['type'],
				//'keywords' => $row['keywords'],
				//'externalurl' => $row['ext_url'],
				//'internalurl' => $row['page'],
				'categories' => $this->getCategories($row['ID']),
				'media' => $this->getMedia($row),
				//'related_files' => $this->getFiles($row),
				//'related_links' => array_key_exists('tx_tlnewslinktext_linktext', $row) ? $this->getRelatedLinksTlNewsLinktext($row['links'], $row['tx_tlnewslinktext_linktext']) : $this->getRelatedLinks($row['links']),
				//'content_elements' => $row['tx_rgnewsce_ce'],
				'import_id' => $row['ID'],
				'import_source' => $this->importSource
			);
		}
		
		return $importData;
		//return array();
	}

	/**
	 * Parses the related files
	 *
	 * @param array $row
	 * @return array
	 */
	protected function getFiles(array $row) {
		$relatedFiles = array();

		// tx_damnews_dam_media
		if (!empty($row['tx_damnews_dam_media'])) {

			// get DAM items
			$files = $this->getDamItems($row['uid'], 'tx_damnews_dam_media');
			foreach ($files as $damUid => $file) {
				$relatedFiles[] = array(
					'file' => $file
				);
			}
		}

		if (!empty($row['news_files'])) {
			$files = GeneralUtility::trimExplode(',', $row['news_files']);

			foreach ($files as $file) {
				$relatedFiles[] = array(
					'file' => 'uploads/media/' . $file
				);
			}
		}

		return $relatedFiles;
	}

	/**
	 * Get correct categories of a news record
	 *
	 * @param integer $newsUid news uid
	 * @return array
	 */
	protected function getCategories($newsUid) {
		$categories = array();

		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('wp_term_relationships');
		$statement = $queryBuilder->select('*')
    			->from('wp_term_relationships') 
				->where(
					$queryBuilder->expr()->eq('object_id', $newsUid)
				)
    			->execute();
		$sql = $queryBuilder->getSQL();
		$this->logger->info(sprintf('IMPORT CATEGORIE SQL:   "%s" ', $sql));
	

		while ($row = $statement->fetch()) {
			$categories[] = $row['term_taxonomy_id'];
		}
		$this->logger->info(sprintf('IMPORT CATEGORIE FOR UID:   "%s" ', $newsUid), $categories);

		return $categories;
	}

	/**
	 * Get correct media elements to be imported
	 *
	 * @param array $row news record
	 * @return array
	 */
	protected function getMedia(array $row) {
		$media = array();

		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('wp_postmeta');
		$statement = $queryBuilder->select('*')
    			->from('wp_postmeta') 
				->join(
					'wp_postmeta',
					'wp_posts',
					'image',
					$queryBuilder->expr()->eq(
					 'wp_postmeta.meta_value',
					 $queryBuilder->quoteIdentifier('image.ID')
				 	),
					$queryBuilder->expr()->eq('post_type', '"attachment"')
				) 
				->where(
					$queryBuilder->expr()->eq('meta_key', '"_thumbnail_id"'),
					$queryBuilder->expr()->eq('post_id', $row['ID'])
				)
    			->execute();
				$queryBuilderMeta = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('wp_postmeta');
				$meta_statement = $queryBuilderMeta->select('wp_postmeta.*')
		    			->from('wp_postmeta') 
						->join(
							'wp_postmeta',
							'wp_postmeta',
							'parentmeta',
							$queryBuilderMeta->expr()->eq(
							 'wp_postmeta.post_id',
							 $queryBuilderMeta->quoteIdentifier('parentmeta.meta_value')
						 	)
						) 
						->where(
							$queryBuilderMeta->expr()->eq('parentmeta.meta_key', '"_thumbnail_id"'),
							$queryBuilderMeta->expr()->eq('wp_postmeta.meta_key', '"_wp_attached_file"'),
							$queryBuilderMeta->expr()->eq('parentmeta.post_id', $row['ID'])
						)
		    			->execute();
					$meta_row = $meta_statement->fetch();
						
		$sql = $queryBuilderMeta->getSQL();
		
	

		while ($image = $statement->fetch()) {
			$this->logger->info(sprintf('SEARCH IMAGE SQL:   "%s" ', $sql));
				$media[] = array(
					'title' => $image['post_title'],
					'alt' => $image['post_excerpt'],
					'caption' => $image['post_content'],
					'image' => "1:archive/".$meta_row['meta_value'],
					'type' => 0,
					'showinpreview' => 1
				);
			
		}
		
		//$media = array_merge($media, $this->getMultimediaItems($row));
		$this->logger->info(sprintf('FOUND MEDIA:   "%s" ', $row['ID']), $media);
		return $media;
		//return array();
	}

	/**
	 * Get link elements to be imported
	 *
	 * @param string $newsLinks
	 * @return array
	 */
	protected function getRelatedLinks($newsLinks) {
		$links = array();

		if (empty($newsLinks)) {
			return $links;
		}

		$newsLinks = str_replace(array('<link ', '</link>'), array('<LINK ', '</LINK>'), $newsLinks);

		$linkList = GeneralUtility::trimExplode('</LINK>', $newsLinks, TRUE);
		foreach ($linkList as $singleLink) {
			if (strpos($singleLink, '<LINK') === FALSE) {
				continue;
			}
			$title = substr(strrchr($singleLink, '>'), 1);
			$uri = str_replace('>' . $title, '', substr(strrchr($singleLink, '<link '), 6));
			$links[] = array(
				'uri' => $uri,
				'title' => $title,
				'description' => '',
			);
		}
		return $links;
	}

	/**
	 * Get link elements to be imported when using EXT:tl_news_linktext
	 * This extension adds an additional field for link texts that are separated by a line break
	 *
	 * @param string $newsLinks
	 * @param string $newsLinksTexts
	 * @return array
	 */
	protected function getRelatedLinksTlNewsLinktext($newsLinks, $newsLinksTexts) {
		$links = array();

		if (empty($newsLinks)) {
			return $links;
		}

		$newsLinks = str_replace("\r\n", "\n", $newsLinks);
		$newsLinksTexts = str_replace("\r\n", "\n", $newsLinksTexts);

		$linkList = GeneralUtility::trimExplode("\n", $newsLinks, TRUE);
		$linkTextList = GeneralUtility::trimExplode("\n", $newsLinksTexts, TRUE);

		$iterator = 0;
		foreach ($linkList as $uri) {
			$links[] = array(
				'uri' => $uri,
				'title' => array_key_exists($iterator, $linkTextList) ? $linkTextList[$iterator] : $uri,
				'description' => '',
			);
			$iterator++;
		}
		return $links;
	}

	/**
	 * Get DAM file names
	 *
	 * @param $newsUid
	 * @param $field
	 * @return array
	 */
	protected function getDamItems($newsUid, $field) {

		$files = array();

		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query('tx_dam.uid, tx_dam.file_name, tx_dam.file_path',
			'tx_dam', 'tx_dam_mm_ref', 'tt_news',
			'AND tx_dam_mm_ref.tablenames="tt_news" AND tx_dam_mm_ref.ident="'.$field.'" ' .
			'AND tx_dam_mm_ref.uid_foreign="' . $newsUid . '"', '', 'tx_dam_mm_ref.sorting_foreign ASC');

		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$files[$row['uid']] = $row['file_path'].$row['file_name'];
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		return $files;
	}

	/**
	 * Parse row for custom plugin info
	 *
	 * @param $row current row
	 * @return array
	 */
	protected function getMultimediaItems($row) {

		$media = array();

		/**
		 * Ext:jg_youtubeinnews
		 */
		if (!empty($row['tx_jgyoutubeinnews_embed'])) {
			if (preg_match_all('#((http|https)://)?([a-zA-Z0-9\-]*\.)+youtube([a-zA-Z0-9\-]*\.)+[a-zA-Z0-9]{2,4}(/[a-zA-Z0-9=.?&_-]*)*#i', $row['tx_jgyoutubeinnews_embed'], $matches)) {
				$matches = array_unique($matches[0]);
				foreach ($matches as $url) {
					$urlInfo = parse_url($url);
					$media[] = array(
						'type' => \Tx_News_Domain_Model_Media::MEDIA_TYPE_MULTIMEDIA,
						'multimedia' => $url,
						'title' => $urlInfo['host'],
					);
				}
			}
		}

		return $media;
	}
}
