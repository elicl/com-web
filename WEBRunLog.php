<?php
 require_once( 'utlsockclient.php' );
 require_once( 'crc32.php' );
?>

<?php
/**********************************************************
/// @file   WEBRunLog.php
/// @brief  PHPʵ�ֵ�ͨ����־������
/// @date   2019-06-19
/// @author elicl <elicl@163.com>
/// @par    ˵��
    ��ģ�����SOCKET�ͻ��˶���������־��������������־����
**********************************************************/
class CGenlog
{
	const ucDATA_CMD               = 0x02;  // ��������
	const ucREPORTERIP_CMD         = 0x06;  // ����IP��ַ
	const ucREPORTERNAME_CMD       = 0x08;  // ���������
	const ucREPORT_PROGAMNAME_CMD  = 0x0F;  // ��������
	public $m_tSockObj;
	private $m_sModuleCrc;
//---------------------------------------------------------
/// @brief
///         ���캯��
/// @param
///         [in]  $sServer      ��־��������IP��ַ
///         [in]  $iPort        ��־�������Ķ˿ں�
/// @return
///         ��
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
///         ��������
/// @param
///         ��
/// @return
///         ��
public function __destruct()
{
	$this->Close();
}
//---------------------------------------------------------
/// @brief
///         ������ת��Ϊ4���ֽڣ����ֽ���ǰ�����ֽ��ں�
/// @param
///         [in]  $iValue       ����
/// @return
///         ת�����4�ֽ��ַ���
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
///         �������ݣ����ַ���������͵���־������
/// @param
///         [in]  $cmd          ���͵�����
///         [in]  $sData        �����͵�����
/// @return
///         ��
private function SendMessage( $cmd, $sData )
{
	if ( !$this->m_tSockObj->m_bSockOpenOk )
	{
		return;
	}
	// �������
	$Buff = chr( $cmd );
	// ��ӳ���
	$Buff  = $Buff . $this->IntTo4Byte( strlen( $sData ) );
	// �������
	$Buff = $Buff . $sData;
	// �������ݰ�	
	$this->m_tSockObj->Write( $Buff, strlen( $Buff ) );
}
//---------------------------------------------------------
/// @brief
///         ����־ģ��
/// @param
///         [in]  $ModuleName   ģ������
/// @return
///         ��
public function Open( $ModuleName )
{
	$crc32 = new crc32( 252 );
	for( $i = 0; $i < strlen( $ModuleName ); $i++ )
	{
		$crc32->update( ord( $ModuleName[ $i ] ) );
	}
	// ����ģ���CRCֵ������
	$this->m_sModuleCrc = sprintf( "%08X", $crc32->value() );

	// ���ʹ���ģ������ݰ�����־������
	$Buff = "#M" . $this->m_sModuleCrc . "#\x20^\x20" . $ModuleName . "\r\n";
	$this->SendMessage( self::ucDATA_CMD, $Buff ); 
}
//---------------------------------------------------------
public function Close()
{
	// ���͹ر���־ģ������ݰ���������
	$Buff = "#N" . $this->m_sModuleCrc . "#\x20^\x20" . "\r\n";
	$this->SendMessage( self::ucDATA_CMD, $Buff ); 
}
//---------------------------------------------------------
public function WriteLog( $sLog )
{
	// �������ݰ�����־������
	$Buff = "#L" . $this->m_sModuleCrc . "#\x20^\x20" . $sLog . "\r\n";
	$this->SendMessage( self::ucDATA_CMD, $Buff );
}
//---------------------------------------------------------
}
?>