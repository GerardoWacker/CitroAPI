<?php

print "CitroAPI 1.0 \n";
print "---------------------- \n";
print "Created by Gerardo Wacker, 2019 \n";
print "From humans, to machines, here's my gift. A copy of the internet. \n";
print "\n";
print "\n";
print "\n";

ini_set("user_agent","CitroAPI/1.0\n");
print "Setting agent settings...\n";
$start = "urls.html";
print "Creating directories...\n";
mkdir("cache");
print "Directories succesfully created.\n";
print "\n";
print "---------------------- \n";
print "\n";

$already_crawled = array();
$crawling = array();

function get_details($url) {

	$options = array('http'=>array('method'=>"GET", 'headers'=>"CitroAPI/1.0\n"));
	$context = stream_context_create($options);
	$doc = new DOMDocument();
	@$doc->loadHTML(@file_get_contents($url, false, $context));

	$title = $doc->getElementsByTagName("title");
	$title = $title->item(0)->nodeValue;
	$description = "";
	$keywords = "";
	$metas = $doc->getElementsByTagName("meta");
	for ($i = 0; $i < $metas->length; $i++) {
		$meta = $metas->item($i);
		if (strtolower($meta->getAttribute("name")) == "description")
			$description = $meta->getAttribute("content");
		if (strtolower($meta->getAttribute("name")) == "keywords")
			$keywords = $meta->getAttribute("content");

	}
	$resulturl = str_replace("\n", "", $url);
	$aaaa = str_replace("http://", "", $resulturl);
	$aaaaa = str_replace("?", "", $aaaa);
	$aaa = str_replace("https://", "", $aaaaa);
	$resulttitle = str_replace("\n", "", $title);
	$jsonreturn = '{ "Title": "'.str_replace("\n", "", $title).'", "Description": "'.str_replace("\n", "", $description).'", "Keywords": "'.str_replace("\n", "", $keywords).'", "URL": "'.$url.'"}';
	mkdir("cache/$aaa", 0777, true);
	file_put_contents("cache/$aaa/index.html", file_get_contents("$resulturl"));
	print "Saved $resulturl\n";
	file_put_contents("cache/$aaa/info.json", $jsonreturn);
	print "Saved $resulturl's info\n";
}

function follow_links($url) {
	global $already_crawled;
	global $crawling;
	$options = array('http'=>array('method'=>"GET", 'headers'=>"CitroAPI/1.0\n"));
	$context = stream_context_create($options);
	$doc = new DOMDocument();
	@$doc->loadHTML(@file_get_contents($url, false, $context));
	$linklist = $doc->getElementsByTagName("a");
	foreach ($linklist as $link) {
		$l =  $link->getAttribute("href");
		if (substr($l, 0, 1) == "/" && substr($l, 0, 2) != "//") {
			$l = parse_url($url)["scheme"]."://".parse_url($url)["host"].$l;
		} else if (substr($l, 0, 2) == "//") {
			$l = parse_url($url)["scheme"].":".$l;
		} else if (substr($l, 0, 2) == "./") {
			$l = parse_url($url)["scheme"]."://".parse_url($url)["host"].dirname(parse_url($url)["path"]).substr($l, 1);
		} else if (substr($l, 0, 1) == "#") {
			$l = parse_url($url)["scheme"]."://".parse_url($url)["host"].parse_url($url)["path"].$l;
		} else if (substr($l, 0, 3) == "../") {
			$l = parse_url($url)["scheme"]."://".parse_url($url)["host"]."/".$l;
		} else if (substr($l, 0, 11) == "javascript:") {
			continue;
		} else if (substr($l, 0, 5) != "https" && substr($l, 0, 4) != "http") {
			$l = parse_url($url)["scheme"]."://".parse_url($url)["host"]."/".$l;
		}
		if (!in_array($l, $already_crawled)) {
				$already_crawled[] = $l;
				$crawling[] = $l;
				echo get_details($l)."\n";
		}

	}
	array_shift($crawling);
	foreach ($crawling as $site) {
		follow_links($site);
	}

}
follow_links($start);
