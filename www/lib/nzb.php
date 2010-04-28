<?php

require_once($_SERVER['DOCUMENT_ROOT']."/lib/framework/db.php");
require_once($_SERVER['DOCUMENT_ROOT']."/lib/util.php");
require_once($_SERVER['DOCUMENT_ROOT']."/config.php");
require_once "Net/NNTP/Client.php";

class NZB 
{
	function NZB() 
	{
		$this->retention = 20; // number of days afterwhich binaries are deleted.
		$this->maxMssgs = 20000; //fetch this ammount of messages at the time
		$this->downloadspeedArr = array();
		$this->groupfilter = "alt.binaries.sounds";
	}

	function connect() 
	{
		$this->nntp = new Net_NNTP_Client;
		$ret = $this->nntp->connect(NNTP_SERVER);
		$ret2 = $this->nntp->authenticate(NNTP_USERNAME, NNTP_PASSWORD);
		if(PEAR::isError($ret) || PEAR::isError($ret2)) 
		{
			echo "Cannot connect to server - ".NNTP_SERVER." - ".NNTP_USERNAME." ($ret $ret2)";
			exit;
		}
	}

	function quit() 
	{
		$this->nntp->quit();
	}
	
	function genNZB($selected) 
	{
		$db = new DB();
	
		$str = "<?xml version=\"1.0\" encoding=\"us-ascii\"?>\n";
		$str .= "<!DOCTYPE nzb PUBLIC \"-//newzBin//DTD NZB 0.9//EN\" \"http://www.newzbin.com/DTD/nzb/nzb-0.9.dtd\">\n";
		$str .= "<nzb xmlns=\"http://www.newzbin.com/DTD/2003/nzb\">\n";

		if(count($selected) > 0) 
		{
			$selected = join(',',$selected);
			$sql = "SELECT *, UNIX_TIMESTAMP(date) AS unixdate FROM binaries WHERE ID IN ({$selected}) ORDER BY name";
			$res = $db->query($sql);

			foreach($res as $arr) 
			{
				$group = $db->queryOneRow("SELECT name FROM groups WHERE ID = {$arr['groupID']}");
				$arr['name'] = ereg_replace("[^a-zA-Z0-9\(\)\! .]",'', str_replace('"', '', $arr['name']));
				$arr['fromname'] = str_replace('(','',str_replace(')','',$arr['fromname']));
				$str .= "\t<file poster=\"who@no.com\" date=\"{$arr['unixdate']}\" subject=\"{$arr['name']} (1/{$arr['totalParts']})\">\n";
				$str .= "\t<groups>\n";
				$str .= "\t\t<group>{$group}</group>\n";
				$str .= "\t</groups>\n";
				$str .= "\t<segments>\n";
				$res2 = $db->query("SELECT * FROM parts WHERE binaryID = {$arr['ID']} ORDER BY partnumber");
				foreach ($res2 as $arr2)
				{
					$str .= "\t\t<segment bytes=\"{$arr2['size']}\" number=\"".round($arr2['partnumber'])."\">{$arr2['messageID']}</segment>\n";
				}
				$str .= "\t</segments>\n";
				$str .= "\t</file>\n";
			}
		}

		$str .= "</nzb>";

		return $str;
	}

	function updateGroup($groupArr) 
	{
		$db = new DB();
		$attemts = 0;

		//select newsgroup
		$data = $this->nntp->selectGroup($groupArr['name']);
		if(PEAR::isError($data)) 
		{
			echo "Could not select group: {$groupArr['name']}\n";
		}

		//get first and last part numbers from newsgroup
		$last = $orglast = $data['last'];
		if($groupArr['last_record'] == 0) 
		{
			$first = $data['first'];
		} else 
		{
			$first = $groupArr['last_record'] + 1;
		}

		//calculate total number op parts
		$total = $last - $first;

		//if total is bigger than 0 it means we have new parts in the newsgroup
		if($total > 0) 
		{

			echo "Group has ".$data['first']." - ".$last." = {$total} (Total parts)\n";

			$done = false;

			//get all te parts (in portions of $this->maxMssgs to not use too much memory)
			while($done === false) 
			{
				if($total > $this->maxMssgs) 
				{
					if($first + $this->maxMssgs > $orglast) 
					{
						$last = $orglast;
					} 
					else 
					{
						$last = $first + $this->maxMssgs;
					}
				}

				$starttime = $this->getmicrotime();
				if($last - $first < $this->maxMssgs) 
				{
					$fetchpartscount = $last - $first;
				} 
				else 
				{
					$fetchpartscount = $this->maxMssgs;
				}
				echo "Getting {$fetchpartscount} parts (".($orglast - $last)." in queue)\n";
				flush();

				//get headers from newsgroup
				$msgs = $this->nntp->getOverview($first, $last);

				//loop headers, figure out parts
				foreach($msgs AS $msg) 
				{
					$pos = strrpos($msg['Subject'], '(');
					$part = substr($msg['Subject'], $pos+1, -1);
					$part = explode('/',$part);

					if(is_numeric($part[0])) 
					{
						$subject = trim(substr($msg['Subject'], 0, $pos));
						if(!isset($this->message[$subject])) 
						{
							$this->message[$subject] = $msg;
							$this->message[$subject]['MaxParts'] = $part[1];
							$this->message[$subject]['Group'] = $group;
							$this->message[$subject]['Date'] = strtotime($this->message[$subject]['Date']);
						}
						if($part[0] > 0) 
						{
							$this->message[$subject]['Parts'][$part[0]] = array('Message-ID' => substr($msg['Message-ID'],1,-1), 'number' => $msg['number'], 'part' => $part[0], 'size' => $msg['Bytes']);
						}
					}
				}

				$count = 0;
				$updatecount = 0;
				$partcount = 0;

				if(count($this->message)) 
				{

					//insert binaries and parts into database. when binary allready exists; only insert new parts
					foreach($this->message AS $subject => $data) {
						if(count($data['Parts']) > 0 && $subject != '') {
							$subject = str_replace("'", "\'", $subject);
							$data['From'] = str_replace("'", "\'", $data['From']);
							$res = $db->query("SELECT ID FROM binaries WHERE name = '{$subject}' AND fromname = '{$data['From']}' AND groupID = {$groupArr['ID']}");
							if($res) 
							{
								$sql = "INSERT INTO binaries (name, fromname, date, xref, totalparts, groupID) VALUES ('{$subject}', '{$data['From']}', FROM_UNIXTIME({$data['Date']}), '{$data['Xref']}', '{$data['MaxParts']}', {$groupArr['ID']})";
								$binaryID = $db->queryInsert($sql);
								$count++;
							} 
							else 
							{
								$binaryID = $res["ID"];
								$updatecount++;
							}

							ksort($data['Parts']);
							reset($data['Parts']);
							foreach($data['Parts'] AS $partdata) 
							{
								$sql = "INSERT INTO parts (binaryID, messageID, number, partnumber, size) VALUES ($binaryID, '{$partdata['Message-ID']}', '{$partdata['number']}', '".round($partdata['part'])."', '{$partdata['size']}')";
								$partcount++;
								$db->queryInsert($sql);
							}
						}
					}
					echo "Received $count new binaries\n";
					echo "Updated $updatecount binaries\n";
					$endtime = $this->getmicrotime();

					//calculate speed
					$parsetime = $endtime - $starttime;
					$downloadspeed = $partcount/$parsetime;
					echo "Current download speed: ".round($downloadspeed)." parts/sec\n";
					$avgdownloadspeed = (array_sum($this->downloadspeedArr) + $downloadspeed) / (count($this->downloadspeedArr) + 1);
					echo "Average download speed: ".round($avgdownloadspeed)." parts/sec\n";
					if(round($downloadspeed) > 0) 
					{
						$this->downloadspeedArr[] = $downloadspeed;
					}

					//update group table with last received message
					$countRes = $db->query("SELECT COUNT(ID) as num FROM binaries WHERE groupID = {$groupArr['ID']}");
					$totingroup = $countRes["num"];

					$db->query("UPDATE groups SET last_record = '{$last}', last_updated = '".date("Y-m-d H:m:i")."', postcount = '{$totingroup}' WHERE ID = {$groupArr['ID']}");

					//when last = orglast; all headers are downloaded; not ? than go on with next $this->maxMssgs messages
					if($last == $orglast) 
					{
						$done = true;
					} 
					else 
					{
						$first = $last + 1;
					}

					//calculate estimated time left with current average speed
					if($parsetime > 0 && $done === false) 
					{
						if($avgdownloadspeed > 0) 
						{
							$ETA = round(($orglast - $first) / $avgdownloadspeed);
						} else 
						{
							$ETA = 0;
						}
						$ETA = $this->sec2min($ETA);
						echo "Estimated time left: {$ETA}\n";
					}

					unset($this->message);
					unset($msgs);
					unset($msg);
					unset($data);
				} else {
					$attemts++;
					echo "Error fetching messages attemt {$attemts}...\n";
					if($attemts == 5) 
					{
						echo "Skipping group\n";
						break;
					}
					sleep(1);
				}
			}
		} 
		else 
		{
			echo "No new records\n";
		}
	}

	function updateAllGroups() 
	{
		$db = new DB();
		$res = $db->query("SELECT * FROM groups WHERE active = 1 ORDER BY name");

		foreach($res as $groupArr) 
		{
			echo "\nProcessing: ".$groupArr['name']."\n";
			flush();
			$this->message = array();
			$this->updateGroup($groupArr);
		}
	}

	//
	// update the list of newsgroups and return an array of messages.
	//
	function updateGroupList() 
	{
		$db = new DB();
		$groups = $this->nntp->getGroups();
		$ret = array();
		
		foreach($groups AS $group) 
		{
			if(stristr($group['group'], $this->groupfilter)) 
			{
				$res = $db->query("SELECT ID FROM groups WHERE name = '{$group['group']}'");
				if($res) 
				{
					if (isset($group['desc']))
					{
						$db->query(sprintf("UPDATE groups SET description = %s where ID = %d", $db->escapeString($group['desc']), $res["ID"]));
						$ret[] = array ('group' => $group['group'], 'msg' => 'Updated description');
					}
					else
					{
						$ret[] = array ('group' => $group['group'], 'msg' => 'Not updated');
					}
				} 
				else 
				{
					$desc = "";
					if (isset($group['desc']))
					{
						$desc = $group['desc'];
					}
					$db->queryInsert(sprintf("INSERT INTO groups (name, description, active) VALUES (%s, %s, 0)", $db->escapeString($group['group']), $db->escapeString($desc)));
					$ret[] = array ('group' => $group['group'], 'msg' => 'Created');
				}
			}
		}

		return $ret;
	}

	function delOldBinaries($groupID='') 
	{
		$db = new DB();
		if(is_numeric($groupID)) 
		{
			$gr = " AND groupID = {$groupID} ";
		}
		$count = 0;
		echo "Deleting posts older than {$this->retention} days\n";
		$sql = "SELECT ID FROM binaries WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(date)) / 3600 / 24 > {$this->retention} {$gr}";
		$res = $db->query($sql);
		foreach($res as $arr) 
		{
			$sql = "DELETE FROM parts WHERE binaryID = {$arr['ID']}";
			$db->query($sql);

			$sql = "DELETE FROM binaries WHERE ID = {$arr['ID']}";
			$db->query($sql);
			$count++;
		}
		echo "Deleted {$count} binaries\n";
		echo "Done\n\n";

	}

}

?>