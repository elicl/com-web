/**********************************************************
/// @file   crc32.js
/// @brief  javascript实现的crc32类
/// @date   2019-06-12
/// @author elicl <elicl@163.com>
/// @par    说明
    本模块创建一个计算32位CRC的类，将字符串计算32位CRC。
**********************************************************/
// 初始化32位CRC的数组
var table = ( function() 
{
  tab = Array( 256 );
	for( var i = 0; i < 256; i++ )
	{
		var coeff = i;
		for( var j = 0; j < 8; j++ )
		{
			if ( coeff & 1 )
			{
				coeff = ( coeff >>> 1 ) ^ 0xedb88320;
			}
			else
			{
				coeff >>>= 1;	
			}
		}
		tab[ i ] = coeff;
	}
	return tab;
})();

//---------------------------------------------------------
//定义一个crc32类
/// @brief
///         利用函数定义一个crc32类，同时计算字符串的32位CRC值
/// @param
///         [in]  str           字符串
///         [in]  init_value    初始值，可以为空
/// @return
///         无
function crc32( str, init_value )
{
	var crc = 0;
	var bBaseManner = true;
	if ( init_value === undefined )
	{
		// 没有使用初始值，按照原始方式进行计算
		crc = crc ^ ( -1 );
	}
	else
	{
		// 有初始值，清除标志，并初始化原始crc值
		bBaseManner = false;
		crc = init_value;
	}
	
  var n = 0;   // a number between 0 and 255
  var x = 0;   // an hex number
  
  for( var i = 0, iTop = str.length; i < iTop; i++ )
  { 
  	n = ( crc ^ str.charCodeAt( i ) ) & 0xFF;
  	x = table[ n ];
  
  	crc = ( crc >>> 8 ) ^ x;
  }
  
  if ( bBaseManner )
  {
  	crc = crc ^ ( -1 );
  }
  
  this.getvalue = function()
  {
  	return crc;
  }
}
//---------------------------------------------------------
