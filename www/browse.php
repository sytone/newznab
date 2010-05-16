<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/page.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/users.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/releases.php");
define("ITEMS_PER_PAGE", "50");

$page = new Page;
$users = new Users;

if (!$users->isLoggedIn())
	$page->show403();

$releases = new Releases;

$page->meta_title = "Browse Nzbs";
$page->meta_keywords = "browse,nzb,description,details";
$page->meta_description = "Browse for Nzbs";

$results = array();

$category = -1;
if (isset($_REQUEST["t"]))
	$category = $_REQUEST["t"] + 0;

$browsecount = $releases->getBrowseCount($category);
$offset = isset($_REQUEST["offset"]) ? $_REQUEST["offset"] : 0;
$page->smarty->assign('pagertotalitems',$browsecount);
$page->smarty->assign('pageroffset',$offset);
$page->smarty->assign('pageritemsperpage',ITEMS_PER_PAGE);
$page->smarty->assign('pagerquerybase', "/browse?t=".$category."&offset=");
$page->smarty->assign('pagerquerysuffix', "#results");

$pager = $page->smarty->fetch("pager.tpl");
$page->smarty->assign('pager', $pager);

$results = $releases->getBrowseRange($category, $offset, ITEMS_PER_PAGE);
if ($category == -1)
	$page->smarty->assign("catname","All");			
elseif (count($results) > 0)
	$page->smarty->assign('catname',$results[0]["category_name"]);			

$page->smarty->assign('results',$results);		
	
$page->content = $page->smarty->fetch('browse.tpl');
$page->render();

?>