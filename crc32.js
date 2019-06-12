/**********************************************************
/// @file   crc32.js
/// @brief  javascriptʵ�ֵ�crc32��
/// @date   2019-06-12
/// @author elicl <elicl@163.com>
/// @par    ˵��
    ��ģ�鴴��һ������32λCRC���࣬���ַ�������32λCRC��
**********************************************************/
// ��ʼ��32λCRC������
var table = ( function() 
{
  const tab = Array( 256 );
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
//����һ��crc32��
/// @brief
///         ���ú�������һ��crc32�࣬ͬʱ�����ַ�����32λCRCֵ
/// @param
///         [in]  str           �ַ���
///         [in]  init_value    ��ʼֵ������Ϊ��
/// @return
///         ��
function crc32( str, init_value )
{
	var crc = 0;
	var bBaseManner = true;
	if ( init_value === undefined )
	{
		// û��ʹ�ó�ʼֵ������ԭʼ��ʽ���м���
		crc = crc ^ ( -1 );
	}
	else
	{
		// �г�ʼֵ�������־������ʼ��ԭʼcrcֵ
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
