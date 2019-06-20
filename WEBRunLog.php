<?php
 require_once( 'utlsockclient.php' );
 require_once( 'crc32.php' );
?>

<?php
/**********************************************************
/// @file   WEBRunLog.php
/// @brief  PHP实现的通用日志工具类
/// @date   2019-06-19
/// @author elicl <elicl@163.com>
/// @par    说明
    本模块调用SOCKET客户端对象连接日志服务器并发送日志数据
**********************************************************/
class CGenlog
{
	const ucDATA_CMD               = 0x02;  // 数据命令
	const ucREPORTERIP_CMD         = 0x06;  // 报告IP地址
	const ucREPORTERNAME_CMD       = 0x08;  // 报告机器名
	const ucREPORT_PROGAMNAME_CMD  = 0x0F;  // 程序名称
	public $m_tSockObj;
	private $m_sModuleCrc;
//---------------------------------------------------------
/// @brief
///         构造函数
/// @param
///         [in]  $sServer      日志服务器的IP地址
///         [in]  $iPort        日志服务器的端口号
/// @return
///         无
public function __construct( $sServer, $iPort )
{
	$this->m_tSockObj = new CSockClient( $sServer, $iPort );
	
	$selfName = $_SERVER[ 'PHP_SELF' ];
	$RemoteIP = $_SERVER[ 'REMOTE_ADDR' ];
	$RemoteName = gethostbyaddr( $RemoteIP );
	
	$this->SendMessage( self::ucREPORT_PROGAMNAME_CMD, $selfName );
	$this->SendMessage( self::ucREPORTERNAME_CMD, $RemoteName );
	$this->SendMessage( self::ucREPORTERIP_CMD, $RemoteIP );
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
	$this->Close();
}
//---------------------------------------------------------
/// @brief
///         将整数转换为4个字节，低字节在前，高字节在后
/// @param
///         [in]  $iValue       整数
/// @return
///         转换后的4字节字符串
private function IntTo4Byte( $iValue )
{
	$Result = NULL;
	
	$Result = $Result . chr( $iValue & 0x00FF );
	$Result = $Result . chr( ( $iValue >> 8 ) & 0x00FF );
	$Result = $Result . chr( ( $iValue >> 16 ) & 0x00FF );
	$Result = $Result . chr( ( $iValue >> 24 ) & 0x00FF );
	return $Result;
}
//---------------------------------------------------------
/// @brief
///         发送数据，将字符串打包发送到日志服务器
/// @param
///         [in]  $cmd          发送的命令
///         [in]  $sData        待发送的数据
/// @return
///         无
private function SendMessage( $cmd, $sData )
{
	if ( !$this->m_tSockObj->m_bSockOpenOk )
	{
		return;
	}
	// 添加命令
	$Buff = chr( $cmd );
	// 添加长度
	$Buff  = $Buff . $this->IntTo4Byte( strlen( $sData ) );
	// 添加数据
	$Buff = $Buff . $sData;
	// 发送数据包	
	$this->m_tSockObj->Write( $Buff, strlen( $Buff ) );
}
//---------------------------------------------------------
/// @brief
///         打开日志模块
/// @param
///         [in]  $ModuleName   模块名称
/// @return
///         无
public function Open( $ModuleName )
{
	$crc32 = new crc32( 252 );
	for( $i = 0; $i < strlen( $ModuleName ); $i++ )
	{
		$crc32->update( ord( $ModuleName[ $i ] ) );
	}
	// 计算模块的CRC值并保存
	$this->m_sModuleCrc = sprintf( "%08X", $crc32->value() );

	// 发送创建模块的数据包到日志服务器
	$Buff = "#M" . $this->m_sModuleCrc . "#\x20^\x20" . $ModuleName . "\r\n";
	$this->SendMessage( self::ucDATA_CMD, $Buff ); 
}
//---------------------------------------------------------
public function Close()
{
	// 发送关闭日志模块的数据包到服务器
	$Buff = "#N" . $this->m_sModuleCrc . "#\x20^\x20" . "\r\n";
	$this->SendMessage( self::ucDATA_CMD, $Buff ); 
}
//---------------------------------------------------------
public function WriteLog( $sLog )
{
	// 发送数据包到日志服务器
	$Buff = "#L" . $this->m_sModuleCrc . "#\x20^\x20" . $sLog . "\r\n";
	$this->SendMessage( self::ucDATA_CMD, $Buff );
}
//---------------------------------------------------------
}
?>