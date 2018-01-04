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

Usage
^^^^^

* After installing the extension, switch to the module "**News Import**".
* Select the wizard you need and press *Start*.

Important: First start import of categories if any. Afterwards reopen the module to import news.
If you don't reopen the module, some news can be imported twice.


Plugin migration
----------------

You can migrate the plugins of `tt_news` to `news` by using the command line.

Be aware that not all options are migrated. Supported are:

* what_to_display
* listOrderBy (except: archivedate, author, type, random)
* ascDesc
* categoryMode
* categorySelection
* useSubCategories
* archive
* imageMaxWidth
* imageMaxHeight
* listLimit
* noPageBrowser
* croppingLenght
* PIDitemDisplay
* backPid
* pages
* recursive

**not supported:**

* croppingLenghtOptionSplit
* firstImageIsPreview
* forceFirstImageIsPreview
* myTS
* template_file
* altLayoutsOptionSplit
* maxWordsInSingleView
* catImageMode
* catImageMaxWidth
* catImageMaxHeight
* maxCatImages
* catTextMode
* maxCatTexts
* alternatingLayouts

Usage
^^^^^

**Important:** Run the plugin migration **after** the record migration!

.. code-block:: bash

	# Gives you some information about how many plugins are still to be migrated
	./typo3/cli_dispatch.phpsh extbase wordpresspluginmigrate:check

.. code-block:: bash

	# Creates the plugins for *EXT:news* by creating a new record below the plugin of *EXT:tt_news*.
	# This makes it possible for you to cross check the migration and adapt the plugins.
	./typo3/cli_dispatch.phpsh extbase wordpresspluginmigrate:run

.. code-block:: bash

	# Replace tt_news plugins directly without creating copies. 
	./typo3/cli_dispatch.phpsh extbase wordpresspluginmigrate:replace

.. code-block:: bash

	# Hide the old tt_news plugins.
	./typo3/cli_dispatch.phpsh extbase wordpresspluginmigrate:removeOldPlugins

	# Deletes the old tt_news plugins.
	./typo3/cli_dispatch.phpsh extbase wordpresspluginmigrate:removeOldPlugins delete=1

