<?php  

/**********************************************************
/// @file   utlsockclient.php
/// @brief  PHP实现的SOCKET客户端类
/// @date   2019-06-03
/// @author elicl <elicl@163.com>
/// @par    说明
    本模块创建一个SOCKET客户端的类，用于链接SOCKET服务器，并
能接收和发送数据。
    注意，本模块使用了SOCKET相关的函数，因此需要打开php.ini，
搜索extension=php_sockets.dll，把前面的;分号去掉。
**********************************************************/
class CSockClient
{
  public $m_tSocket;
  public $m_bSockOpenOk;
  public $m_slastError;
//---------------------------------------------------------
  public function __construct( $address, $port )
  // 构造函数，创建一个链接到指定服务器及端口到客户端链接
  {
    // 告诉PHP，报告所有错误，主要是
    // 如果没有打开DLL的话，如果不报告错误，就不知道发生了什么问题
    error_reporting( E_ALL );
    set_time_limit( 0 );
		
    $this->m_tSocket = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
    if ( $this->m_tSocket < 0 ) 
    {
      $this->m_bSockOpenOk = false;
      $this->m_slastError = socket_strerror( $this->m_tSocket );
    }
    else
    {
    	$this->m_bSockOpenOk = true;
    }
    
    // 这个时候，可以关闭所有错误了，因为，如果
    // 不关闭的话，连接不上服务器时，会发生警告错误。
    // 这样的提示没有必要，因为程序逻辑中的错误提示更精确
    error_reporting( 0 );
    
    $result = socket_connect( $this->m_tSocket, $address, $port );
    
    if ( $result == true )
    {
    	$this->m_bSockOpenOk = true;
    }
    else
    {
    	$this->m_bSockOpenOk = false;
      $this->m_slastError = socket_strerror( socket_last_error() );
    }  
	}
//---------------------------------------------------------
  public function Read( $len )
  {
  	return socket_read( $this->m_tSocket, $len );
  }
//---------------------------------------------------------
  public function Write( $out, $len )
  {
  	socket_write( $this->m_tSocket, $out, $len );
  }		
//---------------------------------------------------------
//析构函数
  function __destruct()
  {
    socket_close( $this->m_tSocket ); 
  }
}
//---------------------------------------------------------

// 以下为测试代码，实际可以去除
/*$skClt = new CSockClient( 'localhost', 90 );
if ( $skClt->m_bSockOpenOk )
{
	$out = 'test123<br>'; 
	$len = strlen( $out );
	$skClt->Write( $out, $len );
	$in = $skClt->Read( $len );
	echo $in; 
}
else
{
	echo "connect error ".$skClt->m_slastError."<br>";
}
*/
?>
