<?php
/*
 $zip=new ZipFile();
 $ziplist=$zip->getFilelist($zipfile);
 $zip->unzip($zipfile,  $desDir);
 
$z = new ZipFile();
$z->zip("aa.zip", $path);

$zip = new ZipFile();
$zip->addDir($dir_utf8.'/hdwiki');
$zip->addDir($tmpdir.'/document');
$zip->addFile('../release/'.$readme, $readme);
$zip->save($tmpdir.'/HDWiki-v'.HDWIKI_VERSION.'UTF8-'.HDWIKI_RELEASE.'.zip');
*/

interface ZipFile_i{
	function getFilelist($zipname);
	function zip($zipname, $path, $data='');
	function zipFiles($zipname, $files);
	function unzip($zipname, $toDir, $index = Array(-1));
	function addData($name, $data, $compact = 1);
}

class ZipFile implements ZipFile_i{
	public $datasec, $ctrl_dir = array();
	public $eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00";
	public $old_offset = 0;
	public $dirs = Array(".");
	
	public function __construct(){
		
	}
	
	public function zip($zipname, $path, $data=''){
		if (empty($data)){
			if (is_file($path)){
				$this->addFile($path);
			}elseif(is_dir($path)){
				$this->addDir($path);
			}else{
				return false;
			}
		}else{
			$this->addData($path, $data);
		}
		return $this->save($zipname);
	}
	
	public function zipFiles($zipname, $files){
		foreach($files as $file){
			$this->addFile($file, basename($file));
		}
		
		return $this->save($zipname);
	}
	
	public function addFiles($files){
		foreach($files as $file){
			$this->addFile($file, basename($file));
		}
	}
	
	/**
	 * 保存压缩zip文件
	 * 
	 * @param string $zipname 压缩文件路径
	 * 
	 * @return int 文件字节长度
	 */
	public function save($zipname) {
		$zipdata = $this->getZipData();
		if (function_exists('file_put_contents')){
			return file_put_contents($zipname, $zipdata);
		} else {
			$handle = fopen($zipname, 'w');
			$length = fwrite($handle, $zipdata);
			closedir($handle);
			return $length;
		}
	}
	
	// 将压缩包输出到浏览器以下载
	public function download($filename){
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment;filename="'.$filename.'"');
		header('Cache-Control: max-age=0');
		
		$this->save('php://output');
	}
	
	/**
	 * 解压缩zip文件
	 * 
	 * @param string $zipname 压缩文件路径
	 * @param string $toDir 目标目录
	 * 
	 * @return mixed
	 */
	public function unzip($zipname, $toDir, $index = Array(-1)){
		$zip = @fopen($zipname, 'rb');
		if(!$zip) return(-1);
		$cdir = $this->getCentralDir($zip, $zipname);
		$pos_entry = $cdir['offset'];
		if(!is_array($index)){ $index = array($index);  }
		for($i=0; $index[$i];$i++){
			if(intval($index[$i]) != $index[$i] || $index[$i] > $cdir['entries'])
			return(-1);
		}
		for ($i=0; $i<$cdir['entries']; $i++){
			@fseek($zip, $pos_entry);
			$header = $this->getCentralFileHeaders($zip);
			$header['index'] = $i;
			$pos_entry = ftell($zip);
			@rewind($zip); 
			fseek($zip, $header['offset']);
			if(in_array("-1",$index) || in_array($i,$index)){
				$stat[$header['filename']] = $this->extractFile($header, $toDir, $zip);
			}
		}
		fclose($zip);
		return $stat;
	}
	
	/**
	 * 获取压缩文件的文件列表
	 * 
	 * @param string $zipname 压缩文件路径
	 * 
	 * @return array
	 */
	public function getFilelist($zipname){
		$ret = array();
		$zip = @fopen($zipname, 'rb');
		if(!$zip) return $ret;
		$centd = $this->getCentralDir($zip,$zipname);

		@rewind($zip);
		@fseek($zip, $centd['offset']);

		for ($i=0; $i<$centd['entries']; $i++){
			$header = $this->getCentralFileHeaders($zip);
			$header['index'] = $i;
			$info['filename'] = $header['filename'];
			$info['stored_filename'] = $header['stored_filename'];
			$info['size'] = $header['size'];
			$info['compressed_size']=$header['compressed_size'];
			$info['crc'] = strtoupper(dechex( $header['crc'] ));
			$info['mtime'] = $header['mtime']; 
			$info['comment'] = $header['comment'];
			$info['folder'] = ($header['external']==0x41FF0010||$header['external']==16)?1:0;
			$info['index'] = $header['index'];
			$info['status'] = $header['status'];
			$ret[]=$info;
			unset($header);
		}
		return $ret;
	}
	
	/**
	 * 给压缩zip文件增加一个空目录
	 * 
	 * @param string $name 目录名称
	 * 
	 * @return void
	 */
	public function addEmptyDir($name) {
	   $name = str_replace("\\", "/", $name);
	   $fr = "\x50\x4b\x03\x04\x0a\x00\x00\x00\x00\x00\x00\x00\x00\x00";

	   $fr .= pack("V",0).pack("V",0).pack("V",0).pack("v", strlen($name) );
	   $fr .= pack("v", 0 ).$name.pack("V", 0).pack("V", 0).pack("V", 0);
	   $this -> datasec[] = $fr;

	   $new_offset = strlen(implode("", $this->datasec));

	   $cdrec = "\x50\x4b\x01\x02\x00\x00\x0a\x00\x00\x00\x00\x00\x00\x00\x00\x00";
	   $cdrec .= pack("V",0).pack("V",0).pack("V",0).pack("v", strlen($name) );
	   $cdrec .= pack("v", 0 ).pack("v", 0 ).pack("v", 0 ).pack("v", 0 );
	   $ext = "\xff\xff\xff\xff";
	   $cdrec .= pack("V", 16 ).pack("V", $this -> old_offset ).$name;

	   $this -> ctrl_dir[] = $cdrec;
	   $this -> old_offset = $new_offset;
	   $this -> dirs[] = $name;
	}

	// 获取压缩包的二进制数据
	public function getZipData() {
	   $data = implode('', $this -> datasec);
	   $ctrldir = implode('', $this -> ctrl_dir);

	   return $data . $ctrldir . $this -> eof_ctrl_dir .
	    pack('v', sizeof($this -> ctrl_dir)).pack('v', sizeof($this -> ctrl_dir)).
	    pack('V', strlen($ctrldir)) . pack('V', strlen($data)) . "\x00\x00";
	}
	
	/**
	 * 将一个目录递归添加到压缩文件当中
	 * 
	 * @param string $path 要添加的目录
	 * @param string $newpath 添加的目录在压缩文件当中的新目录名称，一般和原目录相同
	 * 
	 * @return boolean
	 */
	public function addDir($path, $newpath='') {
		if(!is_dir($path)){	return false;}
		if('' == $newpath) $newpath = basename($path);

		$handle = opendir($path);
		while(false !== ($temp = readdir($handle))){
			if($temp == '.' || $temp == '..'){
				continue;
			}
			$file = $path.DIRECTORY_SEPARATOR.$temp;
			
			if(is_dir($file)){
				$this->addDir($file, $newpath.DIRECTORY_SEPARATOR.$temp);
			}else{
				$this->addFile($file, $newpath.DIRECTORY_SEPARATOR.$temp);
			}
		}
		closedir($handle);
		return true;
	}
	
	/**
	 * 增加一个文件到zip压缩包中
	 * 
	 * @param string $file 待压缩文件路径
	 * @param string $path 压缩文件当中使用的新文件名称，默认和原文件相同
	 * 
	 * @return boolean
	 */
	public function addFile($file, $path='', $compact = 1) {
		$file = str_replace('\\', '/', $file);
		if ($path) {
			$path = str_replace('\\', '/', $path);
		}else{
			$path= $file;
		}
		if(file_exists($file) && !is_dir($file)){
			if (function_exists('file_get_contents')){
				$data = file_get_contents($file);
			}else{
				$handle = fopen($file, "r");
				$data = fread($handle, filesize($file));
				fclose($handle);
			}
			if ($data === false) return false;
		}
		$this->addData($path, $data, $compact);
		return true;
	}
	
	/**
	 * 增加数据到zip压缩包中
	 * 
	 * @param string $name 带压缩文件路径
	 * @param string $data 压缩文件当中使用的新文件名称，默认和原文件相同
	 * 
	 * @return boolean
	 */
	public function addData($name, $data, $compact = 1) {
		$name = str_replace('\\', '/', $name);
		
		$dtime = dechex($this->dosTime());
		$hexdtime = '\x' . $dtime[6] . $dtime[7].'\x'.$dtime[4] . $dtime[5]
		 . '\x' . $dtime[2] . $dtime[3].'\x'.$dtime[0].$dtime[1];
		eval('$hexdtime = "' . $hexdtime . '";');

		if($compact){
			$fr = "\x50\x4b\x03\x04\x14\x00\x00\x00\x08\x00".$hexdtime;
		}else {
			$fr = "\x50\x4b\x03\x04\x0a\x00\x00\x00\x00\x00".$hexdtime;
		}
		$unc_len = strlen($data); $crc = crc32($data);

		if($compact){
			$zdata = gzcompress($data); $c_len = strlen($zdata);
			$zdata = substr(substr($zdata, 0, strlen($zdata) - 4), 2);
		}else{
			$zdata = $data;
		}
		$c_len=strlen($zdata);
		$fr .= pack('V', $crc).pack('V', $c_len).pack('V', $unc_len);
		$fr .= pack('v', strlen($name)).pack('v', 0).$name.$zdata;

		$fr .= pack('V', $crc).pack('V', $c_len).pack('V', $unc_len);

		$this -> datasec[] = $fr;
		$new_offset = strlen(implode('', $this->datasec));
		if($compact){
			$cdrec = "\x50\x4b\x01\x02\x00\x00\x14\x00\x00\x00\x08\x00";
		}else{
			$cdrec = "\x50\x4b\x01\x02\x14\x00\x0a\x00\x00\x00\x00\x00";
		}
		$cdrec .= $hexdtime.pack('V', $crc).pack('V', $c_len).pack('V', $unc_len);
		$cdrec .= pack('v', strlen($name) ).pack('v', 0 ).pack('v', 0 );
		$cdrec .= pack('v', 0 ).pack('v', 0 ).pack('V', 32 );
		$cdrec .= pack('V', $this -> old_offset );

		$this -> old_offset = $new_offset;
		$cdrec .= $name;
		$this -> ctrl_dir[] = $cdrec;
		return true;
	}

	/* protected function */
	
	protected function dosTime() {
	   $timearray = getdate();
	   if ($timearray['year'] < 1980) {
	     $timearray['year'] = 1980; $timearray['mon'] = 1;
	     $timearray['mday'] = 1; $timearray['hours'] = 0;
	     $timearray['minutes'] = 0; $timearray['seconds'] = 0;
	   }
	   return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) |     ($timearray['mday'] << 16) | ($timearray['hours'] << 11) |
	    ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
	}

	protected function getZipFileHeader($zip){
		$binary_data = fread($zip, 30);
		$data = unpack('vchk/vid/vversion/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len', $binary_data);

		$header['filename'] = fread($zip, $data['filename_len']);
		if ($data['extra_len'] != 0){
			$header['extra'] = fread($zip, $data['extra_len']);
		} else {
			$header['extra'] = '';
		}

		$header['compression'] = $data['compression'];
		$header['size'] = $data['size'];
		$header['compressed_size'] = $data['compressed_size'];
		$header['crc'] = $data['crc'];
		$header['flag'] = $data['flag'];
		$header['mdate'] = $data['mdate'];
		$header['mtime'] = $data['mtime'];

		if ($header['mdate'] && $header['mtime']) {
			$hour=($header['mtime']&0xF800)>>11;$minute=($header['mtime']&0x07E0)>>5;
			$seconde=($header['mtime']&0x001F)*2;$year=(($header['mdate']&0xFE00)>>9)+1980;
			$month=($header['mdate']&0x01E0)>>5;$day=$header['mdate']&0x001F;
			$header['mtime'] = mktime($hour, $minute, $seconde, $month, $day, $year);
		} else {
			$header['mtime'] = time();
		}

		$header['stored_filename'] = $header['filename'];
		$header['status'] = "ok";
		return $header;
	}

	protected function getCentralFileHeaders($zip){
		$binary_data = fread($zip, 46);
		$header = unpack('vchkid/vid/vversion/vversion_extracted/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len/vcomment_len/vdisk/vinternal/Vexternal/Voffset', $binary_data);

		if ($header['filename_len'] != 0)
		$header['filename'] = fread($zip,$header['filename_len']);
		else $header['filename'] = '';

		if ($header['extra_len'] != 0)
		$header['extra'] = fread($zip, $header['extra_len']);
		else $header['extra'] = '';

		if ($header['comment_len'] != 0)
		$header['comment'] = fread($zip, $header['comment_len']);
		else $header['comment'] = '';

		if ($header['mdate'] && $header['mtime']){
			$hour = ($header['mtime'] & 0xF800) >> 11;
			$minute = ($header['mtime'] & 0x07E0) >> 5;
			$seconde = ($header['mtime'] & 0x001F)*2;
			$year = (($header['mdate'] & 0xFE00) >> 9) + 1980;
			$month = ($header['mdate'] & 0x01E0) >> 5;
			$day = $header['mdate'] & 0x001F;
			$header['mtime'] = mktime($hour, $minute, $seconde, $month, $day, $year);
		}else{
			$header['mtime'] = time();
		}
		$header['stored_filename'] = $header['filename'];
		$header['status'] = 'ok';
		if (substr($header['filename'], -1) == '/')
		$header['external'] = 0x41FF0010;
		return $header;
	}

	protected function getCentralDir($zip,$zip_name)	{
		$size = filesize($zip_name);
		if ($size < 277) $maximum_size = $size;
		else $maximum_size=277;

		@fseek($zip, $size-$maximum_size);
		$pos = ftell($zip);
		$bytes = 0x00000000;

		while ($pos < $size){
			$byte = @fread($zip, 1); $bytes=($bytes << 8) | ord($byte);
			if (($bytes & 0xFFFFFFFF) == 0x504b0506){ $pos++;break;} $pos++;
		}

		$fdata=fread($zip,18);

		$data=@unpack('vdisk/vdisk_start/vdisk_entries/ventries/Vsize/Voffset/vcomment_size',$fdata);

		if ($data['comment_size'] != 0) $centd['comment'] = fread($zip, $data['comment_size']);
		else $centd['comment'] = ''; $centd['entries'] = $data['entries'];
		$centd['disk_entries'] = $data['disk_entries'];
		$centd['offset'] = $data['offset'];$centd['disk_start'] = $data['disk_start'];
		$centd['size'] = $data['size'];  $centd['disk'] = $data['disk'];
		return $centd;
	}

	protected function extractFile($header,$to,$zip){
		$header = $this->getZipFileHeader($zip);

		if(substr($to,-1)!="/") $to.="/";
		if($to=='./') $to = '';
		$pth = explode("/",$to.$header['filename']);
		$mydir = '';
		for($i=0;$i<count($pth)-1;$i++){
			if(!$pth[$i]) continue;
			$mydir .= $pth[$i]."/";
			if((!is_dir($mydir) && @mkdir($mydir,0777)) || (($mydir==$to.$header['filename'] || ($mydir==$to && $this->total_folders==0)) && is_dir($mydir)) )
			{
				@chmod($mydir,0777);
			}
		}

		if(strrchr($header['filename'],'/')=='/') return;

		if (!($header['external']==0x41FF0010)&&!($header['external']==16)){
			if ($header['compression']==0){
				$fp = @fopen($to.$header['filename'], 'wb');
				if(!$fp) return(-1);
				$size = $header['compressed_size'];

				while ($size != 0){
					$read_size = ($size < 2048 ? $size : 2048);
					$buffer = fread($zip, $read_size);
					$binary_data = pack('a'.$read_size, $buffer);
					@fwrite($fp, $binary_data, $read_size);
					$size -= $read_size;
				}
				fclose($fp);
				touch($to.$header['filename'], $header['mtime']);
			}else{
				$fp = @fopen($to.$header['filename'].'.gz','wb');
				if(!$fp) return(-1);
				$binary_data = pack('va1a1Va1a1', 0x8b1f, Chr($header['compression']),
				Chr(0x00), time(), Chr(0x00), Chr(3));

				fwrite($fp, $binary_data, 10);
				$size = $header['compressed_size'];

				while ($size != 0){
					$read_size = ($size < 1024 ? $size : 1024);
					$buffer = fread($zip, $read_size);
					$binary_data = pack('a'.$read_size, $buffer);
					@fwrite($fp, $binary_data, $read_size);
					$size -= $read_size;
				}

				$binary_data = pack('VV', $header['crc'], $header['size']);
				fwrite($fp, $binary_data,8); fclose($fp);

				$gzp = @gzopen($to.$header['filename'].'.gz','rb');
				if(!$gzp)return false;
				if(!$gzp) return(-2);
				$fp = @fopen($to.$header['filename'],'wb');
				if(!$fp) return(-1);
				$size = $header['size'];

				while ($size != 0){
					$read_size = ($size < 2048 ? $size : 2048);
					$buffer = gzread($gzp, $read_size);
					$binary_data = pack('a'.$read_size, $buffer);
					@fwrite($fp, $binary_data, $read_size);
					$size -= $read_size;
				}
				fclose($fp); gzclose($gzp);

				touch($to.$header['filename'], $header['mtime']);
				@unlink($to.$header['filename'].'.gz');
			}
		}
		return true;
	}
}