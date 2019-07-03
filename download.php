<?php
//---------------------------------------------------------
function ExtractFileName( $sfilename )
{
	$sResult = $sfilename;
	// ȡ�ļ�·��
	$spath = pathinfo( $sfilename, PATHINFO_DIRNAME );
	$iPos = strrpos( $spath, "/" );
	
	if ( $iPos >= 0 )
	{
		// ��·�������޳�·��
		$sResult = substr( $sfilename, strlen( $spath . "/" ),  strlen( $sfilename ) );
	}
	
	return $sResult;
}
//---------------------------------------------------------
//�ļ����صķ�װ(�����ӵķ�ʽ)
function down_file( $filename, $allowDownExt = array( 'zip','html','doc' ) )
{
  if( !is_file( $filename ) || !is_readable( $filename ) )
  {
  	return false;
  }
  $ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
  if(  !in_array( $ext, $allowDownExt ) )
  {
  	// ��ʱ�����չ��������
  	//return false;
  }
  
  //��������ͷ,�����������������ֽ���
  header( 'content-type:application/octet-stream' );
  //����������ļ��ǰ����ֽ��������
  header( 'Accept-Ranges:bytes');
  //����������ļ��Ĵ�С
  header( 'Accept-Length:' . filesize( $filename ) );
  //����������ļ��ǰ������������Ҹ�����������ص��ļ�������
  //header( 'Content-Disposition:attachment;filename=' . basename( $filename ) );
  header( 'Content-Disposition:attachment;filename=' . ExtractFileName( $filename ) );
  //��ȡ�ļ�������
  readfile( $filename );
}
//---------------------------------------------------------
//---------------------------------------------------------
//---------------------------------------------------------
  $filename = $_GET[ 'filename' ];   
  
  if ( ExtractFileName( $filename ) != $filename )
	{
		// ���ȥ��·���б仯��˵���ļ�������·������ʱ��Ҫ��·����ǰ���һ��.
		// ������windows���͵ķ������»��ж�Ϊ�ļ�������
		$filename = "." . $filename;
	}
	
  down_file( $filename );
?>
