TYPO3 extension "news_wordpressimport"
===================================

This extension imports records from `EXT:tt_news` to `EXT:news` with support for multiple 3rd party extensions which enhance tt_news.

**Requirements**

* TYPO3 CMS >= 6.2
* Ext:news >= 3.0

**License**

GPL v2


Migrate database
----------------

First install your wordpress sql backup into your TYPO3 database.

mysql -u username -p database_name < file.sql


Migrate images
--------------

The physical import of the images was the easy part: Copy all images to the fileadmin folder and delete all generated/resized pictures of WordPress (files with the resolution as suffix). I used this command:

.. code-block:: bash

	# Delete thumbnails, only keep original images 
    $ find -E .  -regex ".*[0-9]{3}x[0-9]{2,3}\.jpg" | xargs rm
    $ find -E .  -regex ".*[0-9]{3}x[0-9]{2,3}\.png" | xargs rm


Migrate records
---------------

Link: https://blog.reelworx.at/detail/migrating-from-wordpress-to-typo3-cms/

Categories and Tags

SELECT wt.*, p.* FROM wp_posts p
 LEFT JOIN wp_term_relationships r ON r.object_id=p.ID
 INNER JOIN wp_term_taxonomy t ON t.term_taxonomy_id = r.term_taxonomy_id
 INNER JOIN wp_terms wt on wt.term_id = t.term_id
WHERE p.post_type="POST" AND t.taxonomy="category"  
ORDER BY `p`.`post_date`  DESC

The first step is to import all categories and tags. Both are stored in the table wp_term_taxonomy where the type is defined by the column taxonomy (for categories it’s category for tags it’s post_tag).

Since the ids after import are different in TYPO3, it’s necessary to build up mapping arrays so the old ids can be mapped to the new ids.

Categories are imported as sys_category, tags are imported as tx_news_domain_model_tag.

WordPress stores the tag/category name in a separate table, so you have to query wp_term_taxonomy for a list of all Tags/Categories and then use the term_id to query the wp_terms table to get the name. Some error checking is necessary since in our instance some terms were missing while the entry in the taxonomy table still existed.
Posts

The initial import of the posts is not too hard. The posts are stored in the wp_posts table. As WordPress stores all kinds of stuff in this table, additional conditions have to be added: posts_type=’POST’ and post_status=’publish’ in our case.

We extended the tx_news model to store the original post id and post_name (permalink) in the imported post. After all posts are imported the tags and categories are assigned to the posts (as stored in the wp_term_relationships table).

The post text is split on the <!—more--> text since news has a dedicated teaser column. 

The records `tt_news` are migrated to `tx_news_domain_model_news` and `tt_news_cat` to `sys_category`.

The following 3rd party extensions are supported during the migration and are not needed anymore:

* DAM: The dam records are migrated using the new FAL API.
* jg_youtubeinnews: YouTube links are migrated to EXT:news media elements
* tl_news_linktext: Related links are migrated to ext:news link elements
* EXT:mbl_newsevent are migrated to the available fields of EXT:roq_newsevent (News event extension for EXT:news)

# clean up during Development
DELETE FROM tx_news_domain_model_news WHERE pid=21

# use to find image per post
SELECT * FROM `wp_postmeta` INNER JOIN `wp_posts` `image` ON `wp_postmeta`.`meta_value` = `image`.`ID` WHERE (`meta_key` = "_thumbnail_id") AND (`post_id` = 4895)

SELECT * FROM `wp_postmeta` 
INNER JOIN `wp_posts` `image` ON `wp_postmeta`.`meta_value` = `image`.`ID` 
INNER JOIN `wp_postmeta` `meta` ON `wp_postmeta`.`post_id` = `meta`.`meta_value` 
WHERE (`wp_postmeta`.`meta_key` = "_thumbnail_id") AND (`meta`.`meta_key` = "_wp_attached_file") AND (`wp_postmeta`.`post_id` = 4895)

SELECT childmeta.* 
FROM wp_postmeta childmeta 
INNER JOIN wp_postmeta parentmeta ON (childmeta.post_id=parentmeta.meta_value)
WHERE parentmeta.meta_key='_thumbnail_id' AND childmeta.meta_key = "_wp_attached_file"
AND parentmeta.post_id=4895

# all image post records
SELECT * FROM wp_posts WHERE post_type = "attachment" AND post_status="inherit"

Usage
^^^^^

* After installing the extension, switch to the module "**News Import**".
* Select the wizard you need and press *Start*.

Important: First start import of categories if any. Afterwards reopen the module to import news.
If you don't reopen the module, some news can be imported twice.


