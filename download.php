<?php
//---------------------------------------------------------
function ExtractFileName( $sfilename )
{
	$sResult = $sfilename;
	// 取文件路径
	$spath = pathinfo( $sfilename, PATHINFO_DIRNAME );
	$iPos = strrpos( $spath, "/" );
	
	if ( $iPos >= 0 )
	{
		// 有路径，则剔除路径
		$sResult = substr( $sfilename, strlen( $spath . "/" ),  strlen( $sfilename ) );
	}
	
	return $sResult;
}
//---------------------------------------------------------
//文件下载的封装(超链接的方式)
function down_file( $filename, $allowDownExt = array( 'zip','html','doc' ) )
{
  if( !is_file( $filename ) || !is_readable( $filename ) )
  {
  	return false;
  }
  $ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
  if(  !in_array( $ext, $allowDownExt ) )
  {
  	// 暂时解除扩展名的限制
  	//return false;
  }
  
  //发送请求头,告诉浏览器输出的是字节流
  header( 'content-type:application/octet-stream' );
  //告诉浏览器文件是按照字节来计算的
  header( 'Accept-Ranges:bytes');
  //告诉浏览器文件的大小
  header( 'Accept-Length:' . filesize( $filename ) );
  //告诉浏览器文件是按附件处理，并且告诉浏览器下载的文件的名称
  //header( 'Content-Disposition:attachment;filename=' . basename( $filename ) );
  header( 'Content-Disposition:attachment;filename=' . ExtractFileName( $filename ) );
  //读取文件的内容
  readfile( $filename );
}
//---------------------------------------------------------
//---------------------------------------------------------
//---------------------------------------------------------
  $filename = $_GET[ 'filename' ];   
  
  if ( ExtractFileName( $filename ) != $filename )
	{
		// 如果去掉路径有变化，说明文件名带有路径，此时需要在路径的前面加一个.
		// 否则在windows类型的服务器下会判定为文件不存在
		$filename = "." . $filename;
	}
	
  down_file( $filename );
?>
