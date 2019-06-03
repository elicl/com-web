<?php  

/**********************************************************
/// @file   utlHashtable.php
/// @brief  PHPʵ�ֵĹ�ϣ������
/// @date   2019-06-03
/// @author elicl <elicl@163.com>
/// @par    ˵��
    ��ģ�鴴��һ��SOCKET�ͻ��˵��࣬��������SOCKET����������
�ܽ��պͷ������ݡ�
    ע�⣬��ģ��ʹ����SOCKET��صĺ����������Ҫ��php.ini��
����extension=php_sockets.dll����ǰ���;�ֺ�ȥ����
**********************************************************/
class CSockClient
{
  public $m_tSocket;
  public $m_bSockOpenOk;
  public $m_slastError;
//---------------------------------------------------------
  public function __construct( $address, $port )
  // ���캯��������һ�����ӵ�ָ�����������˿ڵ��ͻ�������
  {
    // ����PHP���������д�����Ҫ��
    // ���û�д�DLL�Ļ��������������󣬾Ͳ�֪��������ʲô����
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
    
    // ���ʱ�򣬿��Թر����д����ˣ���Ϊ�����
    // ���رյĻ������Ӳ��Ϸ�����ʱ���ᷢ���������
    // ��������ʾû�б�Ҫ����Ϊ�����߼��еĴ�����ʾ����ȷ
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
//��������
  function __destruct()
  {
    socket_close( $this->m_tSocket ); 
  }
}
//---------------------------------------------------------

// ����Ϊ���Դ��룬ʵ�ʿ���ȥ��
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
