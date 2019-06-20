<?php
/**********************************************************
/// @file   crc32.php
/// @brief  PHP实现的crc32类
/// @date   2019-06-20
/// @author elicl <elicl@163.com>
/// @par    说明
    本模块创建一个计算32位CRC的类，将字符串计算32位CRC。
**********************************************************/
class crc32
{
//---------------------------------------------------------
/// @brief
///         构造函数
/// @param
///         无
/// @return
///         无
private static $table;
private static $initialized = false;
private $crc;
public function __construct( $init_value )
{
	if ( !crc32::$initialized )
	{
		crc32::$initialized = true;
		crc32::$table = array( 256 );
		for( $i = 0; $i < 256; $i++ )
		{
			$coeff = $i;
			for( $j = 0; $j < 8; $j++ )
			{
				if ( $coeff & 1 )
				{
					$coeff = ( ( $coeff >> 1 ) & 0x7FFFFFFF ) ^ 0xedb88320;
				}
				else
				{
					$coeff >>= 1;
					$coeff &= 0x7FFFFFFF;
				}
			}
		  crc32::$table[ $i ] = $coeff;
		  // 注意上面需要去掉符号位的原因是PHP右移时，将符号位也跟过来了，
		  // 这是错误的，因此必须修正，这应该是PHP没有遵守右移规范引起的。
		  
		  // 下面的右移不用处理，是因为随后把最高字节都抹掉了，因此没有必要了。
		}
	}
	$this->crc = $init_value;
}
//---------------------------------------------------------
/// @brief
///         析构函数
/// @param
///         无
/// @return
///         无
public function __destruct()
{
}
//---------------------------------------------------------
public function update( $c )
{
	$this->crc = crc32::$table[ ( $this->crc ^ ( $c & 0xFF ) ) & 0xFF ] ^ 
	  ( ( $this->crc >> 8 ) & 0x00FFFFFF );
}
//---------------------------------------------------------
public function value()
{
	return $this->crc;
}
//---------------------------------------------------------
}
?>
