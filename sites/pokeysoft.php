<?php

// Original code: The Wizard of DATz

print "<pre>";

$pages = array(
	"http://pokeysoft.no/games/atari/a8arc.htm",
	"http://pokeysoft.no/games/atari/a8str.htm",
	"http://pokeysoft.no/games/atari/a8util.htm",
	"http://pokeysoft.no/games/atari/a8demo.htm",
);

foreach ($pages as $page)
{
	$count = 0;
	$new = 0;

	print "load ".$page."\n";
	$query = get_data($page);
	$query = explode('<TR><TD><FONT COLOR="#FFFF00"><A HREF="../files/pokeysoft/', $query);
	$query[0] = null;

	foreach ($query as $row)
	{
		if ($row)
		{
			$count++;

			$row = explode('<TD>', $row);

			$id = explode('"', $row[0]);
			$id = $id[0];

			if (!$r_query[$id])
			{
				$new++;

				$title = trim($row[1]);
				if ($title[0] != '*')
				{
					$author = trim($row[2]);
					$year = trim($row[3]);
					$type = explode('</TD>', $row[6]);
					$type = trim($type[0]);

					if ($type == 'Disk')
					{
						if ($author && $author != '?' && !strstr($title, $author))
						{
							$title = $title." (".$author.")";
						}
						if ($year && !strstr($year, '?'))
						{
							$title = $title." (".$year.")";
						}
					}

					if (!$r_query[$id])
					{
						$found[$id] = array();
						$found[$id][url] = $id;
					}
					
					$found[$id][name][] = str_replace(array('/', ':'), array(', ', '-'), $title);
				}
			}
		}
	}

	print "found: ".$count.", new: ".$new."\n\n";
}

print "\nurls:\n\n";


print "<table><tr><td><pre>";

foreach ($found as $row)
{
	print $row[url]."\n";
}

print "</td><td><pre>";

foreach ($found as $row)
{
	print "<a href=\"http://pokeysoft.no/games/files/pokeysoft/".$row[url]."\">".implode(', ',$row[name]).".zip</a>\n";
}

print "</td></tr></table>";

?>