# SearchStax Search for WordPress

The SearchStax Search plugin adds powerful site search features using [SearchStax Serverless](https://www.searchstax.com/pricing/cloud/serverless-solr-service-pricing-details/) and [SearchStax Studio](https://www.searchstax.com/searchstudio/) backed with Solr.

## Set Up

Once this plugin has been added and actived in your WordPress instance you can add SearchStax account details, start indexing content, and configure your search result pages.

### SearchStax Account

1. Go to searchstax.com to create a new Cloud Serverless or Studio account
2. Confirm your account and login to get your API endpoints and access tokens
3. Open the SearchStax Search settings page in your WordPress admin
4. Select the 'Account' tab and add the select read-only and update read-write API URLs and tokens and save

Click 'Check Index' to test the connection to your SearchStax instance. If your API endpoints and access tokens are correct you'll see how many items are currently indexed.

### Indexing Content

1. In the SearchStax Search settings page select the 'Search Index' tab
2. Click 'Index All Content' to add any published WordPress pages and posts (including custom posts managed by other plugins)
3. Wait for all content to get indexed - this can take over a minute for larger sites
4. When all content has been indexed the page will refresh and show indexed pages in the table

### Configure Search Result page

1. Select 'Add New' from the 'Search Result Pages' menu item
2. Give your search result page a name (required)
3. Select the various options for search bar and results display
4. Filter results by post type, category, or tags by selecting them from the list

## Features

- This plugin will automatically configure the Solr schema for WordPress when you add your account details
- Content will be automatically added to the search index when it's published or updated
- Content will be removed automatically when deleted or unpublished
- Site-wide Search will redirect any URLs containing `?s=*searchterm*` to a search result page