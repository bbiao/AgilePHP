<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Location
{
    private $fp;
    private $wrydat;
    private $wrydat_version;
    private $ipnumber;
    private $firstip;
    private $lastip;
    private $ip_range_begin;
    private $ip_range_end;
    private $country;
    private $area;
    
    const REDIRECT_MODE_0 = 0;
    const REDIRECT_MODE_1 = 1;
    const REDIRECT_MODE_2 = 2;
    
    function __construct($wrydat = 'QQWry.dat')
    {
	    $this->wrydat = APPPATH . '/libraries/' . $wrydat;
        $this->initialize();
    }
    
    function __destruct()
    {
        fclose($this->fp);
    }
    
    private function initialize()
    {
        if(file_exists($this->wrydat)) {
            $this->fp = fopen($this->wrydat, 'rb');
        }
        $this->getipnumber();
        $this->getwryversion();
    }
    
    public function get($str)
    {
        return $this->$str;
    }
    
    public function set($str, $val)
    {
        $this->$str = $val;
    }
    
    private function getbyte($length, $offset = null)
    {
        if(!is_null($offset))
        {
            fseek($this->fp, $offset, SEEK_SET);
        }
        $b = fread($this->fp, $length);
        return $b;
    }
/**
* 把IP地址打包成二进制数据，以big endian（高位在前）格式打包
* 数据存储格式为 little endian（低位在前） 如：
* 00 28 C6 DA    218.198.40.0    little endian
* 3F 28 C6 DA    218.198.40.0    little endian
* 这样的数据无法作二分搜索查找的比较，所以必须先把获得的IP数据使用strrev转换为big endian
* @param $ip
* @return big endian格式的二进制数据
*/
    private function packip($ip)
    {
        return pack("N", intval(ip2long($ip)));
    }
    
    private function getlong($length=4, $offset=null)
    {
        $chr = null;
        for($c=0; $length % 4 != 0 && $c < (4 - $length % 4); $c++)
        {
            $chr .= chr(0);
        }
        $var = unpack( "Vlong", $this->getbyte($length, $offset).$chr);
        return $var['long'];
    }
    
    private function getwryversion()
    {
        $length = preg_match("/coral/i", $this->wrydat) ? 26 : 30;
        $this->wrydat_version = $this->getbyte($length, $this->firstip-$length);
    }
    
    private function getipnumber()
    {
        $this->firstip = $this->getlong();
        $this->lastip = $this->getlong();
        $this->ipnumber = ($this->lastip-$this->firstip)/7+1;
    }
    
    private function getstring($data="",$offset=null)
    {
        $char = $this->getbyte(1,$offset);
        while(ord($char) > 0){
            $data .= $char;
            $char = $this->getbyte(1);
        }
        return $data;
    }
    
    private function iplocaltion($ip)
    {
        $ip = $this->packip($ip);
        $low = 0;
        $high = $this->ipnumber - 1;
        $ipposition = $this->lastip;
        while($low <= $high)
        {
            $t = floor(($low + $high) / 2);
            if($ip < strrev($this->getbyte(4, $this->firstip + $t * 7)))
            {
                $high = $t - 1;
            } 
            else 
            {
                if($ip > strrev($this->getbyte(4, $this->getlong(3))))
                {
                    $low = $t + 1;
                }
                else
                {
                    $ipposition = $this->firstip + $t * 7;
                    break;
                }
            }
        }
        return $ipposition;
    }
    
    private function getarea()
    {
        $b = $this->getbyte(1);
        switch(ord($b)){
            case self::REDIRECT_MODE_0 :
                return "未知";
                break;
            case self::REDIRECT_MODE_1:
            case self::REDIRECT_MODE_2:
                return $this->getstring("",$this->getlong(3));
                break;
            default:
                return $this->getstring($b);
                break;
        }
    }
    
    public function getiplocation($ip)
    {
        $ippos = $this->iplocaltion($ip);
        $this->ip_range_begin = long2ip($this->getlong(4, $ippos));
        $this->ip_range_end = long2ip($this->getlong(4, $this->getlong(3)));
        $b = $this->getbyte(1);
        switch (ord($b))
        {
            case self::REDIRECT_MODE_1:
                $b = $this->getbyte(1, $this->getlong(3));
                if(ord($b) == self::REDIRECT_MODE_2)
                {
                    $countryoffset = $this->getlong(3);
                    $this->area = $this->getarea();
                    $this->country = $this->getstring("", $countryoffset);
                }
                else
                {
                    $this->country = $this->getstring($b);
                    $this->area = $this->getarea();
                }
                break;
                
            case self::REDIRECT_MODE_2:
                    $countryoffset = $this->getlong(3);
                    $this->area = $this->getarea();
                    $this->country = $this->getstring("", $countryoffset);
                break;
                
            default:
                $this->country = $this->getstring($b);
                $this->area = $this->getarea();
                break;
        }
    }
}
?>