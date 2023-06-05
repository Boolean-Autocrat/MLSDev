<?php


class sitemap
{
    public $PageFreq = 'weekly'; //hourly, daily, weekly, monthly, yearly
    public $PagePri = 0.6; // 0.0 to 0.9, 1.0 is used for the domain root
    //Set the change frequency and priority for the listings
    public $ListingFreq = 'weekly'; //hourly, daily, weekly, monthly, yearly
    public $ListingPri = 0.5; // 0.0 to 0.9, 1.0 is used for the domain root
    //Set the change frequency and priority for the listings
    public $BlogFreq = 'monthly'; //hourly, daily, weekly, monthly, yearly
    public $BlogPri = 0.4; // 0.0 to 0.9, 1.0 is used for the domain root

    private $fhandle;
    private $file_list=[];
    private $update_time;

    public function generate()
    {
        global $config,$conn, $api, $lang, $misc;

        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        $this->update_time=time();
        $url_counter=0;
        $file_counter=1;
        @ini_set('max_execution_time', 0);
        $this->write_sitemap_file_start($file_counter);

        // Get all the static pages
        $sql = 'SELECT pagesmain_id
        		FROM '. $config['table_prefix'] .'pagesmain
        		ORDER BY pagesmain_id';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        while (!$recordSet->EOF) {
            // loop through the pages to get the title and ID
            if ($url_counter>9998) {
                $this->write_sitemap_file_end();
                $file_counter++;
                $this->write_sitemap_file_start($file_counter);
                $url_counter=0;
            }
            $page_id = $recordSet->fields('pagesmain_id');
            $url = $page->magicURIGenerator('page', $page_id, true);

            // no last mod date so we will use the generation date
            $Lastmod = date('Y-m-d');
            $this->write_item($url, $Lastmod, $this->PageFreq, $this->PagePri);
            $recordSet->MoveNext();
            $url_counter++;
        } // end while loop for pages

        $sql = 'SELECT listingsdb_id, listingsdb_last_modified
        		FROM '. $config['table_prefix'] .'listingsdb
        		WHERE listingsdb_active = \'yes\'
        		ORDER BY listingsdb_id';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }

        while (!$recordSet->EOF) {
            if ($url_counter>9998) {
                $this->write_sitemap_file_end();
                $file_counter++;
                $this->write_sitemap_file_start($file_counter);
                $url_counter=0;
            }
            // loop through the listings to get the title, ID and last mod date
            $listing_id = $recordSet->fields('listingsdb_id');
            $Lastmod = strtotime($recordSet->fields('listingsdb_last_modified'));
            //$Lastmod = substr_replace($Lastmod, '', -9); // Strip the time from lastmod - unless you really want it
            $url = $page->magicURIGenerator('listing', $listing_id, true);
            $this->write_item($url, date('c', $Lastmod), $this->ListingFreq, $this->ListingPri);
            $recordSet->MoveNext();
            $url_counter++;
        }
        // Get all published blog articles
        $sql = 'SELECT blogmain_id, blogmain_date
        		FROM '. $config['table_prefix'] .'blogmain
        		WHERE blogmain_published = 1 ORDER BY blogmain_id';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }

        while (!$recordSet->EOF) {
            if ($url_counter>9998) {
                $this->write_sitemap_file_end();
                $file_counter++;
                $this->write_sitemap_file_start($file_counter);
                $url_counter=0;
            }
            //loop through the articles to get the title, ID and created date
            $blog_id = $recordSet->fields('blogmain_id');
            $Lastmod = $recordSet->fields('blogmain_date');
            $url = $page->magicURIGenerator('blog', $blog_id, true);
            $this->write_item($url, date('c', $Lastmod), $this->BlogFreq, $this->BlogPri);
            $recordSet->MoveNext();
            $url_counter++;
        }
        $this->write_sitemap_file_end();
        $this->write_sitemap_index();

        //return success message
        return $lang['sitemap_generated'];
    }

    public function write_sitemap_file_start($filenum)
    {
        global $config;

        $filename = $config['basepath'].'/sitemap'.$filenum.'.xml';
        $this->file_list[]='sitemap'.$filenum.'.xml';
        $this->fhandle = fopen($filename, 'w+');
        fwrite($this->fhandle, '<?xml version="1.0" encoding="UTF-8"?>
        	<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n");
    }

    public function write_sitemap_file_end()
    {
        fwrite($this->fhandle, '</urlset>'."\n");
        fclose($this->fhandle);
    }

    public function write_sitemap_index()
    {
        global $config;
        $filename = $config['basepath'].'/sitemap.xml';
        $this->fhandle = fopen($filename, 'w+');
        fwrite($this->fhandle, '<?xml version="1.0" encoding="UTF-8"?>
		<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n");
        foreach ($this->file_list as $sitemap) {
            fwrite($this->fhandle, '<sitemap>
			<loc>'.$config['baseurl'].'/'.$sitemap.'</loc>
      			<lastmod>'.date('c', $this->update_time).'</lastmod>
      			</sitemap>'."\n");
        }
        if (file_exists($config['basepath'].'/sitemap_custom.xml')) {
            $custom_modtime= filemtime($config['basepath'].'/sitemap_custom.xml');
            fwrite($this->fhandle, '<sitemap>
			<loc>'.$config['baseurl'].'/sitemap_custom.xml</loc>
      			<lastmod>'.date('c', $custom_modtime).'</lastmod>
      			</sitemap>'."\n");
        }
        fwrite($this->fhandle, '</sitemapindex>'."\n");
        fclose($this->fhandle);
    }

    public function write_item($link, $modified, $freq, $priority)
    {
        $link = htmlentities($link, ENT_QUOTES, 'UTF-8');
        $fileinfo = "<url>
		<loc>$link</loc>
		<lastmod>$modified</lastmod>
		<changefreq>$freq</changefreq>
		<priority>$priority</priority>
 		</url>\n";
        fwrite($this->fhandle, $fileinfo);
    }
}
