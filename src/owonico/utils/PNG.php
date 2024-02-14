<?php

namespace owonico\utils;

use ArrayObject;

class PNG
{
	private $png=null;
	private $fp=null;
	var $data=null;
	var $seek=0;
	var $info=array();
	var $color=array();
	const IHEADER='0D0A1A0A';
	const IEND='0000000049454E44AE426082';
	
	public function __construct($png=null)
	{
		$this->png=$png;
		if($this->png!=null && $this->read($this->png)!==false)
		{
			$this->status=true;
		}
		else
		{
			$this->status=false;
		}
	}
	
	public function readBits8()
	{
		$t='';
		$t=unpack("C", fread($this->fp, 1));
		return $t[1];
	}
	
	public function readBH16()
	{
		$t='';
		$t=unpack("n", fread($this->fp, 2));
		return $t[1];
	}
	
	public function readBits16()
	{
		$t='';
		$t=unpack("S", fread($this->fp, 2));
		return $t[1];
	}
	
	public function readBH32()
	{
		$t='';
		$t=unpack("N", fread($this->fp, 4));
		return $t[1];
	}
	
	public function readBits32()
	{
		$t='';
		$t=unpack("L", fread($this->fp, 4));
		return $t[1];
	}
	
	public function version()
	{
		fseek($this->fp, 1);
		$this->info['version']=fread($this->fp, 3);
		return $this->info['version'];
	}
	
	public function seek($s)
	{
		fseek($this->fp, $s);
		return $this;
	}
	
	public function rBName()
	{
		return fread($this->fp, 4);
	}
	
	public function rSize($s)
	{
		return fread($this->fp, $s);
	}
	
	public function rCRC()
	{
		return $this->readBH32();
	}
	
	public function rBlock()
	{
		$b=new ArrayObject();
		$b->length=$this->readBH32();
		$b->name=$this->rBName();
		if ($b->length>0) {
			$b->data=$this->rSize($b->length);
		} else {
			$b->data='';
		}
		$b->crc=$this->rCRC();
		if (crc32($b->name . $b->data)==$b->crc) {
			$b->checkcrc=true;
		} else {
			$b->checkcrc=false;
		}
		return $b;
	}
	
	public function pIHDR($obj)
	{

		$t=unpack("N", substr($obj->data, 0, 4));
		$this->info['width']=$t[1];

		$t=unpack("N", substr($obj->data, 4, 4));
		$this->info['height']=$t[1];
		
		$t=unpack("C", substr($obj->data, 8, 1));
		$this->info['bitdepth']=$t[1];

		$t=unpack("C", substr($obj->data, 9, 1));
		$this->info['colortype']=$t[1];
		
		$t=unpack("C", substr($obj->data, 10, 1));
		$this->info['lz77']=$t[1];
		
		$t=unpack("C", substr($obj->data, 11, 1));
		$this->info['filter']=$t[1];
		
		$t=unpack("C", substr($obj->data, 12, 1));
		$this->info['interlace']=$t[1];
		return $this;
	}
	
	public function psBit($obj)
	{
		$this->info['sbit']=array();
		for ($i=0; $i<$obj->length; $i++) {
			$t=unpack("C", substr($obj->data, $i, 1));
			$this->info['sbit'][$i]=$t[1];
		}
		return $this;
	}
	
	public function pPLTE($obj)
	{
		$this->info['plte']=array();
		$j=0;
		for ($i=0; $i<$obj->length; $i+=3) {
			$r=unpack("C", substr($obj->data, $i, 1));
			$this->info['plte'][$j]['r']=$r[1];
			$g=unpack("C", substr($obj->data, $i+1, 1));
			$this->info['plte'][$j]['g']=$g[1];
			$b=unpack("C", substr($obj->data, $i+2, 1));
			$this->info['plte'][$j]['b']=$b[1];
			$this->info['plte'][$j]['rgb']=$this->rgbHex($r[1]) . $this->rgbHex($g[1]) . $this->rgbHex($b[1]);
			$j++;
		}
		return $this;
	}
	
	public function ptRNS($obj)
	{
		$this->info['trns']=array();
		for ($i=0; $i<$obj->length; $i++) {
			$t=unpack("C", substr($obj->data, $i, 1));
			$this->info['trns'][$i]=$t[1];
		}
		return $this;
	}
	
	public function ppHYs($obj)
	{
		$this->info['phys']=array();
		$t=unpack("N", substr($obj->data, 0, 4));
		$this->info['phys']['x']=$t[1];
		$t=unpack("N", substr($obj->data, 4, 4));
		$this->info['phys']['y']=$t[1];
		$t=unpack("C", substr($obj->data, 8, 1));
		$this->info['phys']['unit']=$t[1];
		return $this;
	}
	
	public function ptEXt($obj)
	{
		$this->info['create']=$obj->data;
		return $this;
	}
	
	public function pIDAT($obj)
	{
		$this->data=gzuncompress($obj->data);
		$this->seek=0;
		return $this;
	}
	
	public function pcHRM($obj)
	{
		$this->info['chrm']=array();
		$t=unpack("N", substr($obj->data, 0, 4));
		$this->info['chrm']['wx']=$t[1];
		$t=unpack("N", substr($obj->data, 4, 4));
		$this->info['chrm']['wy']=$t[1];
		$t=unpack("N", substr($obj->data, 8, 4));
		$this->info['chrm']['rx']=$t[1];
		$t=unpack("N", substr($obj->data, 12, 4));
		$this->info['chrm']['ry']=$t[1];
		$t=unpack("N", substr($obj->data, 16, 4));
		$this->info['chrm']['gx']=$t[1];
		$t=unpack("N", substr($obj->data, 20, 4));
		$this->info['chrm']['gy']=$t[1];
		$t=unpack("N", substr($obj->data, 24, 4));
		$this->info['chrm']['bx']=$t[1];
		$t=unpack("N", substr($obj->data, 28, 4));
		$this->info['chrm']['by']=$t[1];
		return $this;
	}
	
	public function read($png=null)
	{
		if ($this->png=null)
			$this->png=$png;
		$this->fp=fopen($png,'rb');
		if ($this->readBits8()==0x89 && $this->version()=='PNG' && $this->readBH32()==0x0D0A1A0A) {
			do {
				$obj=$this->rBlock();
				switch ($obj->name) {
					case 'IHDR':
						$this->pIHDR($obj);
						break;
					case 'sBIT':
						$this->psBit($obj);
						break;
					case 'PLTE':
						$this->pPLTE($obj);
						break;
					case 'tRNS':
						$this->ptRNS($obj);
						break;
					case 'pHYs':
						$this->ppHYs($obj);
						break;
					case 'cHRM':
						$this->pcHRM($obj);
						break;
					case 'tEXt':
						$this->ptEXt($obj);
						break;
					case 'IDAT':
						$this->pIDAT($obj);
						break;
					case 'IEND':
						break;
						
				}
			} while (!feof($this->fp) && $obj->name!='IEND');
			fclose($this->fp);
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function decode()
	{
		switch ($this->info['colortype']) {
			case '0':
				break;
			case '2':
				$this->png24row();
				break;
			case '3':
				$this->indexrow();
				break;
			case '4':
				break;
			case '6':
				$this->png32row();
				break;
		}
		
	}
	
	function png32row()
	{
		$tcolor=array();
		$rh=array();
		for ($ii=0; $ii<$this->info['height']; $ii++) {
			$rh[$ii]=$this->rRowHeader();
			for ($i=0; $i<$this->info['width']; $i++) {
				$t=$this->rRGBA();
				$tcolor[$ii][$i]=$t;
				switch ($rh[$ii]) {
					case '0':
						$this->color[$ii][$i]=new ArrayObject();
						$this->color[$ii][$i]->r=$t->r;
						$this->color[$ii][$i]->g=$t->g;
						$this->color[$ii][$i]->b=$t->b;
						$this->color[$ii][$i]->a=$t->a;
						break;
					case '1':
						if ($i==0) {
							$this->color[$ii][$i]=new ArrayObject();
							$this->color[$ii][$i]->r=$t->r;
							$this->color[$ii][$i]->g=$t->g;
							$this->color[$ii][$i]->b=$t->b;
							$this->color[$ii][$i]->a=$t->a;
						} else {
							$this->color[$ii][$i]=new ArrayObject();
							$this->color[$ii][$i]->r=$this->filt1($t->r, $this->color[$ii][$i-1]->r);
							$this->color[$ii][$i]->g=$this->filt1($t->g, $this->color[$ii][$i-1]->g);
							$this->color[$ii][$i]->b=$this->filt1($t->b, $this->color[$ii][$i-1]->b);
							$this->color[$ii][$i]->a=$this->filt1($t->a, $this->color[$ii][$i-1]->a);
						}
						
						break;
					case '2':
						$this->color[$ii][$i]=new ArrayObject();
						$this->color[$ii][$i]->r=$this->filt2($t->r, $this->color[$ii-1][$i]->r);
						$this->color[$ii][$i]->g=$this->filt2($t->g, $this->color[$ii-1][$i]->g);
						$this->color[$ii][$i]->b=$this->filt2($t->b, $this->color[$ii-1][$i]->b);
						$this->color[$ii][$i]->a=$this->filt2($t->a, $this->color[$ii-1][$i]->a);
						break;
					case '3':
						if ($i==0) {
							$this->color[$ii][$i]=new ArrayObject();
							$this->color[$ii][$i]->r=$this->filt3($t->r, $this->color[$ii-1][$i]->r, 0);
							$this->color[$ii][$i]->g=$this->filt3($t->g, $this->color[$ii-1][$i]->g, 0);
							$this->color[$ii][$i]->b=$this->filt3($t->b, $this->color[$ii-1][$i]->b, 0);
							$this->color[$ii][$i]->a=$this->filt3($t->a, $this->color[$ii-1][$i]->a, 0);
						} else {
							$this->color[$ii][$i]=new ArrayObject();
							$this->color[$ii][$i]->r=$this->filt3($t->r, $this->color[$ii-1][$i]->r, $this->color[$ii][$i-1]->r);
							$this->color[$ii][$i]->g=$this->filt3($t->g, $this->color[$ii-1][$i]->g, $this->color[$ii][$i-1]->g);
							$this->color[$ii][$i]->b=$this->filt3($t->b, $this->color[$ii-1][$i]->b, $this->color[$ii][$i-1]->b);
							$this->color[$ii][$i]->a=$this->filt3($t->a, $this->color[$ii-1][$i]->a, $this->color[$ii][$i-1]->a);
						}
						break;
					case '4':
						if ($i==0) {
							$this->color[$ii][$i]=new ArrayObject();
							$this->color[$ii][$i]->r=$this->filt4($t->r, $this->color[$ii-1][$i]->r, $this->color[$ii-1][$i]->r, 0);
							$this->color[$ii][$i]->g=$this->filt4($t->g, $this->color[$ii-1][$i]->g, $this->color[$ii-1][$i]->g, 0);
							$this->color[$ii][$i]->b=$this->filt4($t->b, $this->color[$ii-1][$i]->b, $this->color[$ii-1][$i]->b, 0);
							$this->color[$ii][$i]->a=$this->filt4($t->a, $this->color[$ii-1][$i]->a, $this->color[$ii-1][$i]->a, 0);
						} else {
							$this->color[$ii][$i]=new ArrayObject();
							$this->color[$ii][$i]->r=$this->filt4($t->r, $this->color[$ii-1][$i]->r, $this->color[$ii][$i-1]->r, $this->color[$ii-1][$i-1]->r);
							$this->color[$ii][$i]->g=$this->filt4($t->g, $this->color[$ii-1][$i]->g, $this->color[$ii][$i-1]->g, $this->color[$ii-1][$i-1]->g);
							$this->color[$ii][$i]->b=$this->filt4($t->b, $this->color[$ii-1][$i]->b, $this->color[$ii][$i-1]->b, $this->color[$ii-1][$i-1]->b);
							$this->color[$ii][$i]->a=$this->filt4($t->a, $this->color[$ii-1][$i]->a, $this->color[$ii][$i-1]->a, $this->color[$ii-1][$i-1]->a);
						}
						break;
				}
			}
		}
	}
	
	function png24row()
	{
		$tcolor=array();
		$rh=array();
		for ($ii=0; $ii<$this->info['height']; $ii++) {
			$rh[$ii]=$this->rRowHeader();
			for ($i=0; $i<$this->info['width']; $i++) {
				$t=$this->rRGB();
				$tcolor[$ii][$i]=$t;
				switch ($rh[$ii]) {
					case '0':
						$this->color[$ii][$i]=new ArrayObject();
						$this->color[$ii][$i]->r=$t->r;
						$this->color[$ii][$i]->g=$t->g;
						$this->color[$ii][$i]->b=$t->b;
						break;
					case '1':
						if ($i==0) {
							$this->color[$ii][$i]=new ArrayObject();
							$this->color[$ii][$i]->r=$t->r;
							$this->color[$ii][$i]->g=$t->g;
							$this->color[$ii][$i]->b=$t->b;
						} else {
							$this->color[$ii][$i]=new ArrayObject();
							$this->color[$ii][$i]->r=$this->filt1($t->r, $this->color[$ii][$i-1]->r);
							$this->color[$ii][$i]->g=$this->filt1($t->g, $this->color[$ii][$i-1]->g);
							$this->color[$ii][$i]->b=$this->filt1($t->b, $this->color[$ii][$i-1]->b);
						}
						
						break;
					case '2':
						$this->color[$ii][$i]=new ArrayObject();
						$this->color[$ii][$i]->r=$this->filt2($t->r, $this->color[$ii-1][$i]->r);
						$this->color[$ii][$i]->g=$this->filt2($t->g, $this->color[$ii-1][$i]->g);
						$this->color[$ii][$i]->b=$this->filt2($t->b, $this->color[$ii-1][$i]->b);
						break;
					case '3':
						if ($i==0) {
							$this->color[$ii][$i]=new ArrayObject();
							$this->color[$ii][$i]->r=$this->filt3($t->r, $this->color[$ii-1][$i]->r, 0);
							$this->color[$ii][$i]->g=$this->filt3($t->g, $this->color[$ii-1][$i]->g, 0);
							$this->color[$ii][$i]->b=$this->filt3($t->b, $this->color[$ii-1][$i]->b, 0);
						} else {
							$this->color[$ii][$i]=new ArrayObject();
							$this->color[$ii][$i]->r=$this->filt3($t->r, $this->color[$ii-1][$i]->r, $this->color[$ii][$i-1]->r);
							$this->color[$ii][$i]->g=$this->filt3($t->g, $this->color[$ii-1][$i]->g, $this->color[$ii][$i-1]->g);
							$this->color[$ii][$i]->b=$this->filt3($t->b, $this->color[$ii-1][$i]->b, $this->color[$ii][$i-1]->b);
						}
						break;
					case '4':
						if ($i==0) {
							$this->color[$ii][$i]=new ArrayObject();
							$this->color[$ii][$i]->r=$this->filt4($t->r, $this->color[$ii-1][$i]->r, $this->color[$ii-1][$i]->r, 0);
							$this->color[$ii][$i]->g=$this->filt4($t->g, $this->color[$ii-1][$i]->g, $this->color[$ii-1][$i]->g, 0);
							$this->color[$ii][$i]->b=$this->filt4($t->b, $this->color[$ii-1][$i]->b, $this->color[$ii-1][$i]->b, 0);
						} else {
							$this->color[$ii][$i]=new ArrayObject();
							$this->color[$ii][$i]->r=$this->filt4($t->r, $this->color[$ii-1][$i]->r, $this->color[$ii][$i-1]->r, $this->color[$ii-1][$i-1]->r);
							$this->color[$ii][$i]->g=$this->filt4($t->g, $this->color[$ii-1][$i]->g, $this->color[$ii][$i-1]->g, $this->color[$ii-1][$i-1]->g);
							$this->color[$ii][$i]->b=$this->filt4($t->b, $this->color[$ii-1][$i]->b, $this->color[$ii][$i-1]->b, $this->color[$ii-1][$i-1]->b);
						}
						break;
				}
			}
		}
	}
	
	function indexrow()
	{
		for ($ii=0; $ii<$this->info['height']; $ii++) {
			$rowchar=ceil($this->info['width']*($this->info['bitdepth'])/8);
			$rowheader=$this->rRowHeader();
			$this->wseek=0;
			$rowdata=substr($this->data, $this->seek, $rowchar);
			$this->seek+=$rowchar;
			$cdata='';
			for ($i=strlen($rowdata)-1; $i>=0; $i--) {
				$t=unpack("C", $rowdata[$i]);
				$cdata=str_pad(decbin($t[1]), 8, "0", STR_PAD_LEFT) . $cdata;
			}
			for ($i=0; $i<$this->info['width']; $i++) {
				$this->color[$ii][$i]=new ArrayObject();
				$this->color[$ii][$i]->r=$this->info['plte'][bindec(substr($cdata, $this->wseek, $this->info['bitdepth']))]['r'];
				$this->color[$ii][$i]->g=$this->info['plte'][bindec(substr($cdata, $this->wseek, $this->info['bitdepth']))]['g'];
				$this->color[$ii][$i]->b=$this->info['plte'][bindec(substr($cdata, $this->wseek, $this->info['bitdepth']))]['b'];
				$this->wseek+=$this->info['bitdepth'];
			}
			
		}
	}
	
	function rRowHeader()
	{
		$t=unpack("C", substr($this->data, $this->seek, 1));
		$this->seek+=1;
		return $t[1];
	}
	
	function rBits()
	{
		$t=unpack("C", substr($this->data, $this->seek, 1));
		$this->seek+=1;
		return $t[1];
	}
	
	function rBitC()
	{
		$t=unpack("n", substr($this->data, $this->seek, 2));
		$this->seek+=2;
		return $t[1];
	}
	
	function rRGB()
	{
		$rgb=new ArrayObject();
		$t=unpack("C", substr($this->data, $this->seek, 1));
		$this->seek+=1;
		$rgb->r=$t[1];
		$t=unpack("C", substr($this->data, $this->seek, 1));
		$this->seek+=1;
		$rgb->g=$t[1];
		$t=unpack("C", substr($this->data, $this->seek, 1));
		$this->seek+=1;
		$rgb->b=$t[1];
		
		return $rgb;
	}
	
	function rRGBA()
	{
		$rgb=new ArrayObject();
		$t=unpack("C", substr($this->data, $this->seek, 1));
		$this->seek+=1;
		$rgb->r=$t[1];
		$t=unpack("C", substr($this->data, $this->seek, 1));
		$this->seek+=1;
		$rgb->g=$t[1];
		$t=unpack("C", substr($this->data, $this->seek, 1));
		$this->seek+=1;
		$rgb->b=$t[1];
		$t=unpack("C", substr($this->data, $this->seek, 1));
		$this->seek+=1;
		$rgb->a=$t[1];
		
		return $rgb;
	}
	
	function filt1($x, $b)
	{
		$p=$x+$b;
		$p=substr(str_pad(strtoupper(dechex($p)), 2, "0", STR_PAD_LEFT), -2);
		
		return hexdec('0x' . $p);
	}
	
	function filt2($x, $b)
	{
		$p=$x+$b;
		$p=substr(str_pad(strtoupper(dechex($p)), 2, "0", STR_PAD_LEFT), -2);
		return hexdec('0x' . $p);
	}
	
	function filt3($x, $b, $a=0)
	{
		$p=($b+$a)/2;
		$p=$x+$p;
		$p=substr(str_pad(strtoupper(dechex($p)), 2, "0", STR_PAD_LEFT), -2);
		return hexdec('0x' . $p);
	}
	
	function filt4($x, $b, $a=0, $c=0)
	{
		$p=$a+$b-$c;
		$pa=abs($p-$a);
		$pb=abs($p-$b);
		$pc=abs($p-$c);
		if ($pa<$pb && $pa<=$pc)
			$p=$a;
		elseif ($pb<=$pc)
			$p=$b;
		else
			$p=$c;
		$p=$x+$p;
		
		$p=substr(str_pad(strtoupper(dechex($p)), 2, "0", STR_PAD_LEFT), -2);
		return hexdec('0x' . $p);
	}
	
	function rgbHex($value)
	{
		$value&=0xff;
		return str_pad(strtoupper(dechex($value)), 2, "0", STR_PAD_LEFT);
	}
	
	function tohex($value)
	{
		$value&=0xffffffff;
		return str_pad(strtoupper(dechex($value)), 8, "0", STR_PAD_LEFT);
	}
}
?>
