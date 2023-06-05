<?php
error_reporting(E_ALL ^ E_NOTICE);
@ini_set('pcre.backtrack_limit', '10000000'); // phpcs:ignore
@ini_set('precision', 14); // phpcs:ignore


global $config, $conn, $css_file;
require_once dirname(__FILE__) . '/include/common.php';
$css_file = '';


//Set XML Header
header('Content-Type: application/xml');
header('Cache-control: private'); //IE6 Form Refresh Fix

function linkExtractor($html)
{
    $linkArray = array();
    if (preg_match_all('/<a\s+.*?href=[\"\']?([^\"\' >]*)[\"\']?[^>]*>.*?<\/a>/i', $html, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            array_push($linkArray, $match[1]);
        }
    }
    return $linkArray;
}

//Tutorial at quietearth.us was very helpful in building the pingback parser.
function process_ping($m)
{
    global $config, $conn, $misc, $css_file;

    include_once dirname(__FILE__) . '/include/core.inc.php';
    $page = new page_user();

    $x1 = $m->getParam(0);
    //echo 'x1: <pre>'.print_r($x1).'</pre>';
    $x2 = $m->getParam(1);
    //echo 'x2: <pre>'.print_r($x2).'</pre>';
    $source_uri = $x1->scalarval(); # their article
    $dest_uri = $x2->scalarval(); # your article
    $agent = 'Open-Realty Pingback Service (' . $config['baseurl'] . ')';
    //echo $source;
    // INSERT CODE
    // here we can check for valid urls in source and dest, security
    // lookup the dest article in our database etc..
    if ($config['allow_pingbacks'] == 0) { # Access denied
        return new PhpXmlRpc\Response(0, 49, 'Access denied');
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $source_uri);
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $source = curl_exec($ch);
    curl_close($ch);

    if ($source == false) { # source uri does not exist
        $curl_error = curl_error($ch);
        return new PhpXmlRpc\Response(0, 16, 'Source uri does not exist: ' . $curl_error);
    }
    //$source = htmlspecialchars_decode($source);
    $links = linkExtractor($source);
    $linkFound = false;
    //echo '<pre>Finding Link: '.$dest_uri.' </pre>';
    foreach ($links as $link) {
        if (strpos(htmlspecialchars_decode(htmlspecialchars_decode($link)), $dest_uri) !== false) {
            $linkFound = true;
        }
    }
    if ($linkFound === false) { # source uri does not have a link to target uri
        return new PhpXmlRpc\Response(0, 17, 'Source uri does not have link to target uri');
    }

    //Check that target is a local URI
    $base_url_nowww = str_replace('www.', '', $config['baseurl']);
    if (strpos($dest_uri, $config['baseurl']) === false && strpos($dest_uri, $base_url_nowww) === false) { # target uri cannot be used as target
        return new PhpXmlRpc\Response(0, 33, 'Target uri cannot be used as target (baseurl)');
    }

    $dest_url_path = str_replace($base_url_nowww, '', $dest_uri);
    $dest_url_path = str_replace($config['baseurl'], '', $dest_url_path);
    //Check that it is a published artile
    $article_id = 0;
    unset($_GET['ArticleID']);
    //Check if this is a magicurl
    $output = [];
    $url_parts = parse_url($dest_url_path);
    if (isset($url_parts['query'])) {
        parse_str($url_parts['query'], $output);
    }
    if (isset($output['ArticleID']) && isset($output['action']) && $output['action'] == 'blog_view_article') {
        $_GET['ArticleID'] = $output['ArticleID'];
    } else {
        $page->magicURIParser(false, $dest_url_path);
    }
    if (isset($_GET['ArticleID'])) {
        $article_id = intval($_GET['ArticleID']);
    }
    if ($article_id > 0) {
        //Make sure article is published.
        $sql = 'SELECT blogmain_id FROM ' . $config['table_prefix'] . 'blogmain WHERE blogmain_id = ' . intval($article_id) . ' AND blogmain_published = 1;';
        //echo $sql;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        //echo 'RC:'.$recordSet->RecordCount();
        if ($recordSet->RecordCount() == 0) { # Pingback already registered
            return new PhpXmlRpc\Response(0, 33, 'Target uri cannot be used as target (nonpublished)');
        }
    } else {
        return new PhpXmlRpc\Response(0, 33, 'Target uri cannot be used as target (invalid article id)');
    }
    //Al
    //Make sure this is not a duplicate ping
    /*  CREATE TABLE  `blogtest`.`default_blogpingbacks` (
        `blogpingback_id` int(11) NOT NULL auto_increment,
        `blogmain_id` int(11) NOT NULL,
        `blogpingback_timestamp` int(11) NOT NULL,
        `blogpingback_source` varchar(2000) NOT NULL,
        `blogpingback_dest` varchar(2000) NOT NULL,
        `blogcomments_moderated` tinyint(1) default NULL,
        PRIMARY KEY  (`blogpingback_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1
        */
    $sql = 'SELECT blogpingback_id FROM ' . $config['table_prefix_no_lang'] . 'blogpingbacks 
			WHERE blogpingback_source = ' . $misc->make_db_safe($source_uri) . ' 
			AND blogmain_id = ' . $article_id . ';';
    $recordSet = $conn->Execute($sql);
    if (!$recordSet) {
        $misc->log_error($sql);
    }

    if ($recordSet->RecordCount() > 0) { # Pingback already registered
        return new PhpXmlRpc\Response(0, 48, 'Pingback already registered');
    }

    /*
        if (..) { # Could not communicate with upstream server or got error
        return new PhpXmlRpc\Response(0, 50, "Problem with upstream server");
        }
    */
    if ($config['blog_requires_moderation'] == 1) {
        $moderated = 0;
    } else {
        $moderated = 1;
    }
    $sql = 'INSERT INTO ' . $config['table_prefix_no_lang'] . 'blogpingbacks (blogpingback_source,blogmain_id,blogpingback_timestamp,blogcomments_moderated)
	VALUES (' . $misc->make_db_safe($source_uri) . ',' . $article_id . ',
	' . time() . ',' . $moderated . '
	);';
    $recordSet = $conn->Execute($sql);
    if (!$recordSet) {
        return new PhpXmlRpc\Response(0, 50, 'Unkown error');
    }

    return new PhpXmlRpc\Response(new PhpXmlRpc\Value('Pingback registered.', 'string'));
}

$a = ['pingback.ping' => ['function' => 'process_ping']];
$s = new PhpXmlRpc\Server($a, false);
// $s->setdebug(3);
$s->service();
