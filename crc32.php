<?php
/**********************************************************
/// @file   crc32.php
/// @brief  PHPʵ�ֵ�crc32��
/// @date   2019-06-20
/// @author elicl <elicl@163.com>
/// @par    ˵��
    ��ģ�鴴��һ������32λCRC���࣬���ַ�������32λCRC��
**********************************************************/
class crc32
{
//---------------------------------------------------------
/// @brief
///         ���캯��
/// @param
///         ��
/// @return
///         ��
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
		  // ע��������Ҫȥ������λ��ԭ����PHP����ʱ��������λҲ�������ˣ�
		  // ���Ǵ���ģ���˱�����������Ӧ����PHPû���������ƹ淶����ġ�
		  
		  // ��������Ʋ��ô�������Ϊ��������ֽڶ�Ĩ���ˣ����û�б�Ҫ�ˡ�
		}
	}
	$this->crc = $init_value;
}
//---------------------------------------------------------
/// @brief
///         ��������
/// @param
///         ��
/// @return
///         ��
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
