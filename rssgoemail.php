<?php
/*  
    Copyright 2009 Abdul Ibad (loopxcrack[at]yahoo.co.uk)
    http://ibad.bebasbelanja.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

    header('Content-Type: text/html');
	require_once(dirname(__FILE__).'/config.php');
	require_once(dirname(__FILE__).'/autoloader.php');
	
	$connect = mysql_connect($dbhost,$dbuser,$dbpass) or die("Cannot Connect Database");


	if(!(mysql_select_db($dbbase))){
		echo "CANNOT SELECT DATABASE";
		die();
	}
    // Call SimplePie
	$feed = new SimplePie();
	
	$feed->set_feed_url($urls);
	
	$feed->enable_cache();
	$feed->set_cache_location($cachedir);
	$feed->set_cache_duration($cachetime);
	
	// Init feed
	$feed->init();
	// Make sure the page is being served with the UTF-8 headers.
	$feed->handle_content_type();
	$items = $feed->get_items();
	
	$accumulatedText = '';
		
	foreach($items as $item){
	
		$title = $item->get_title();
		$guid = md5($item->get_id());
		$desc = $item->get_description();
		$link = $item->get_link();
	
		// Check Row
		$query = mysql_query("SELECT * FROM rssgoemail WHERE guid='$guid'");
		$row = mysql_num_rows($query);
	
		// If row empty send email and happy blogging
		if( $row == 0){
			
			$mail = $desc."<br /><a href=\"".$link."\" rel=\"nofollow\">Read More</a>";

			$accumulatedText .= $title . "<br />" . $mail . "<br /><br />" ;
			$accumulatedGuid[] = $guid; 
			
		}else{
			continue;
		}
			
	}
	echo "Mailtest:<br /><br />". $accumulatedText;
	print_r($accumulatedGuid);
	$send = mail($accumulatedText, $title, $mail, "From: {$title}");	
        if($send){
		foreach($accumulatedGuid as $guid){
			mysql_query("INSERT INTO rssgoemail(guid) VALUES ('$guid')");	
		}
	}
?>
