<?php
 require_once( 'LineFile.php' );
?>

<?php
/**********************************************************
/// @file   IniFile.php
/// @brief  PHP实现的INI文件读写工具类
/// @date   2019-06-24
/// @author elicl <elicl@163.com>
/// @par    说明
    本模块采用PHP实现一个INI文件读写工具类，实现INI文件的读写
功能。
**********************************************************/
//---------------------------------------------------------
class IniFile
{
const FOUNDNONE    = 0;           // 未找到
const FOUNDSECTION = 1;           // 找到了节
const FOUNDKEY     = 2;           // 找到了键

private $m_Linefile;              // 行文件对象
//---------------------------------------------------------
public function __construct()
{
	$this->m_Linefile = new LineFile();
}
//---------------------------------------------------------
public function __destruct()
{
}
//---------------------------------------------------------
/// @brief
///         追加保存一个键值，要求INI文件不存在相应的键值
/// @param
///         [in]  $sAppName     节名
///         [in]  $sKeyName     键名
///         [in]  $sValue       键值
/// @return
///         若成功，返回真，否则返回假
private function AppendKey( $sAppName, $sKeyName, $sValue )
{
	$bResult = false;
	$handle = fopen( $this->m_Linefile->GetFileName(), "r+b" );
	if ( !$handle )
	{
		return false;
	}
	
	// 写入节
	$sSection = sprintf( "[%s]\r\n", $sAppName );
	fseek( $handle, 0, SEEK_END );
	$iToWriteLen = strlen( $sSection );
	$ret = fwrite( $handle, $sSection, $iToWriteLen );
	if ( $ret != $iToWriteLen )
	{
		$bResult = false;
	}
	
	// 写入键
	$sData = sprintf( "%s=%s\r\n", $sKeyName, $sValue );
	$iToWriteLen = strlen( $sData );
	$ret = fwrite( $handle, $sData, $iToWriteLen );
	if ( $ret != $iToWriteLen )
	{
		$bResult = false;
	}
	
	// 关闭文件
	fclose( $handle );
	
	// 重新统计文件的行数据信息，以便后续能正确使用
	$this->m_Linefile->CountTotalLines();
	return $bResult;
}
//---------------------------------------------------------
/// @brief
///         重新INI文件以修改相应的键值
/// @param
///         [in]  $sKeyName     键名
///         [in]  $sValue       键值
///         [in]  $dwCopyLine   待复制的分隔行号
///         [in]  $bIsNew       为真表示dwCopyLine指定的行
///                             号也要复制，然后在该行号下增加一个新行，
///                             否则，表示该行需要被替换为新的内容
/// @return
///         若成功，返回真，否则返回假
private function BackIniFile( $sKeyName, $sValue, $dwCopyLine, $bIsNew )
{
	$bResult = false;
	$handle_read;
	$handle_write;

	// 先生成一个临时文件名
	$stempname = tempnam( "","bak" ); 
	// 创建临时文件
	$handle_write = fopen( $stempname, "w+b" );
	if ( !$handle_write )
	{
		return false;
	}
	
	// 取原INI文件的长度
	$iforglen = filesize( $this->m_Linefile->GetFileName() );
	
	// 计算分隔行的起始位置
	$file_pos = $this->m_Linefile->GetLinePos( $dwCopyLine );
	
	if ( $bIsNew )
	{
		// 如果是新插入行，则该行也要拷贝进来，因此还要加上该行的长度
		// 如果不是新插入行，则拷贝到该行的起始位置即可，因为该行需要被顶替
		$file_pos += $this->m_Linefile->GetLineLength( $dwCopyLine );
	}
	
	// 打开原来INI文件准备读取数据
	$handle_read = fopen( $this->m_Linefile->GetFileName(), "rb" );
	if ( $handle_read )
	{
		// 读取原INI文件的前半部分
		$sBuf = fread( $handle_read, $file_pos );
		// 写入临时文件
		$ret = fwrite( $handle_write, $sBuf, $file_pos );
		if ( $ret == $file_pos )
		{
			$bResult = true;
		}
	}
	
	if ( $bResult )
	{
		// 生成新数据行
		$sBuf = sprintf( "%s=%s\r\n", $sKeyName, $sValue );
		$dwLineLen = strlen( $sBuf );
		// 写入临时文件
		$ret = fwrite( $handle_write, $sBuf, $dwLineLen );
		if ( $ret != $dwLineLen )
		{
			$bResult = false;
		}
	}
	
	if ( $bResult )
	{
		// 当前行已经在前面处理过了，要么被拷贝进新文件，要么被舍弃
		// 到这里的时候，都应该从下一行开始复制旧文件的后续部分
		$dwCopyLine++;
		
		// 这里判断一下，如果行号超出总行数，则说明没有后续部分了				
		if ( $dwCopyLine < $this->m_Linefile->TotalLine() )
		{
			// 从该行的起始位置开始，前面可以认为已经处理过了
			$file_pos = $this->m_Linefile->GetLinePos( $dwCopyLine );
			// 文件长度减去该行的起始位置，表示从该行开始一直到文件末尾
			$read_len = $iforglen - $file_pos;
			// 移动原INI文件指针到该行的起始位置
			fseek( $handle_read, $file_pos, SEEK_SET );
			// 读取文件后半部分
			$sBuf = fread( $handle_read, $read_len );
			// 写入临时文件
			$ret = fwrite( $handle_write, $sBuf, $read_len );
			
			if ( $ret != $read_len )
			{
				$bResult = false;
			}
		}
	}
	
	fclose( $handle_read );
	fclose( $handle_write );
	if ( $bResult )
	{
		// 删除原INI文件
		unlink( $this->m_Linefile->GetFileName() );
		// 将临时文件替换原INI文件
		rename( $stempname, $this->m_Linefile->GetFileName() );
		// 重新统计文件的行数据信息，以便后续能正确使用
		$this->m_Linefile->CountTotalLines();
	}
	return $bResult;
}
//---------------------------------------------------------
/// @brief
///         读取一个键值，要求指定行是一个普通数据行
/// @param
///         [in]  $sKeyName     键名
///         [in]  $iLineNo      行号，从0开始
/// @return
///         返回键的值
private function GetPureKeyData( $sKeyName, $iLineNo )
{
  // 取行数据
	$sData = $this->m_Linefile->GetLineData( $iLineNo );
	$sValue = false;
	$sEqualStr = "=";
	$iPos = strpos( $sData, $sEqualStr );

	if ( $iPos > 0 )
	{
		// 取键名
		$sName = substr( $sData, 0, $iPos );
		// 取键值
		$sV = substr( $sData, $iPos + strlen( $sEqualStr ), strlen( $sData ) );
		if ( trim( $sName ) == $sKeyName )
		{
			// 键名符合，赋值键值
			$sValue = trim( $sV );
		}
	}
	// 返回键值
	return $sValue;
}
//---------------------------------------------------------
/// @brief
///         读取一个节名，要求指定行是一个节行
/// @param
///         [in]  $iLineNo      行号，从0开始
/// @return
///         返回节的名称
private function GetPureSectionData( $iLineNo )
{
	$sSectionName = $this->m_Linefile->GetLineData( $iLineNo );
	
	$sResult = "";
	$iBegin  = 0;
	$iEnd    = 0;
	$iLen    = strlen( $sSectionName );
	
	if ( $iLen > 0 )
	{
		for( $iBegin = 0; $iBegin < $iLen; $iBegin++ )
		{
			if ( $sSectionName[ $iBegin ] == "[" )
			{
				break;
			}
		}
		
		for( $iEnd = $iLen - 1; $iEnd > $iBegin; $iEnd-- )
		{
			if ( $sSectionName[ $iEnd ] == "]" )
			{
				break;
			}
		}
		// 计算剔除左右中括号的节名称
		$sResult = substr( $sSectionName, $iBegin + 1, $iEnd - $iBegin - 1 );
	}
	return $sResult;
}
//---------------------------------------------------------
/// @brief
///         在INI文件中查找数据指定节下指定键名的数据。
/// @param
///         [in]  $sAppName     节名
///         [in]  $sKeyName     键名
///         [in]  $sDefault     键值缺省值
///         [out] $tKeyFound    带出查找结果：未找到、找到节、找到键
///         [out] $dwSeparateNo 带出分隔行号，该行号指明所在节的最后行的行号
/// @return
///         若找到，返回键的值，否则返回缺省键值
private function LocatePrivateProfileString( $sAppName, $sKeyName, $sDefault, &$tKeyFound, &$dwSeparateNo )
{
	$sResult   = $sDefault;
	$tKeyFound = IniFile::FOUNDNONE;
	
	$dwBeginLine;
	$dwEndLine;

	if ( $this->SearchSection( $sAppName, $dwBeginLine, $dwEndLine ) )
	{
		// 设置标志，找到了节
		$tKeyFound = IniFile::FOUNDSECTION;
		$dwSeparateNo = $dwEndLine;
		for( $i = $dwBeginLine; $i <= $dwEndLine; $i++ )
		{
			if ( $this->m_Linefile->GetLineAttr( $i ) == LineStru::NORMALLINE )
			{
				$sValue = $this->GetPureKeyData( $sKeyName, $i );
				if ( $sValue )
				{
					$sResult = $sValue;
					// 带出键值对应的行号
					$dwSeparateNo = $i;
					// 设置标志，找到键值
					$tKeyFound = IniFile::FOUNDKEY;
					break;
				}
			}
		}
	}
	return $sResult;
}
//---------------------------------------------------------
//---------------------------------------------------------
//---------------------------------------------------------
/// @brief
///         设置INI的文件名
/// @param
///         [in]  $sFileName    INI文件名
/// @return
///         无
public function SetFileName( $sFileName )
{
	$this->m_Linefile->SetFileName( $sFileName );
	$this->m_Linefile->CountTotalLines();
}
//---------------------------------------------------------
/// @brief
///         搜索指定的节名
/// @param
///         [in]  $sAppName       节名称
///         [out] $dwBeginLineNo  该节的起始行号
///         [out] $dwEndLineNo    该节的结束行号
/// @return
///         找到指定的节，返回真，否则返回假
public function SearchSection( $sAppName, &$dwBeginLineNo, &$dwEndLineNo )
{
	$bResult = false;
	$bHasFoundSectionName = false;
	
	for( $i = 0; $i < $this->m_Linefile->TotalLine(); $i++ )
	{
		if ( $this->m_Linefile->GetLineAttr( $i ) == LineStru::SECTIONLINE )
		{
			if ( $bHasFoundSectionName )
			{
				// 找到了一个新的节，该节的行号的前一行号为该节的结束行号
				$dwEndLineNo = $i - 1;
				// 不再找了
				break;
			}
			else if ( $this->GetPureSectionData( $i ) == $sAppName )
			{
				// 先记录起始行号
				$dwBeginLineNo = $i;
				$dwEndLineNo = $this->m_Linefile->TotalLine();
				if ( $dwEndLineNo > 0 )
				{
					// 行数减1才是行号
					$dwEndLineNo--;
				}
				// 设置标志，找到了节名
				$bHasFoundSectionName = true;
				// 设置返回标志为真
				$bResult = true;
			}
		}
		else
		{
			// 下一行
			continue;
		}
	}
	return $bResult;
}
//---------------------------------------------------------
/// @brief
///          读取一个整数配置项
/// @param
///         [in] $sAppName        节名称
///         [in] $sKeyName        键名称
///         [in] $nDefault        缺省键值
/// @return
///         返回该节对应的键名的整数值，若INI不存在该项，则返回缺省值
public function GetPrivateProfileInt( $sAppName, $sKeyName, $nDefault )
{
	$iResult = $nDefault;
	$sBuf = $this->GetPrivateProfileString( $sAppName, $sKeyName, "" );
	if ( $sBuf )
	{
		$iResult = intval( $sBuf );
	}
	return $iResult;
}
//---------------------------------------------------------
/// @brief
///          读取一个字符串配置项
/// @param
///         [in] $sAppName        节名称
///         [in] $sKeyName        键名称
///         [in] $sDefault        缺省键值
/// @return
///         返回该节对应的键名的字符串值，若INI不存在该项，则返回缺省值
public function GetPrivateProfileString( $sAppName, $sKeyName, $sDefault )
{
	$tKeyfound;
	$dwLineNo;
	return $this->LocatePrivateProfileString( $sAppName, $sKeyName, $sDefault, 
	  $tKeyfound, $dwLineNo );
}
//---------------------------------------------------------
/// @brief
///          写入一个字符串配置项
/// @param
///         [in] $sAppName        节名称
///         [in] $sKeyName        键名称
///         [in] $sValue          键值
/// @return
///         写入成功，返回真，否则返回假
public function WritePrivateProfileString( $sAppName, $sKeyName, $sValue )
{
	$bResult = false;
	$tKeyFound;
	$dwLineNo;
	
	$this->LocatePrivateProfileString( $sAppName, $sKeyName, "", $tKeyFound, $dwLineNo );
	
	switch( $tKeyFound )
	{
	case IniFile::FOUNDNONE:
	  $bResult = $this->AppendKey( $sAppName, $sKeyName, $sValue );
	  break;
	case IniFile::FOUNDSECTION;
    $bResult = $this->BackIniFile( $sKeyName, $sValue, $dwLineNo, true );
	  break;
	case IniFile::FOUNDKEY;
	  $bResult = $this->BackIniFile( $sKeyName, $sValue, $dwLineNo, false );
	  break;
	default:
	  break;
	}
	return $bResult;
}
//---------------------------------------------------------
/// @brief
///          写入一个整数配置项
/// @param
///         [in] $sAppName        节名称
///         [in] $sKeyName        键名称
///         [in] $nValue          键值
/// @return
///         写入成功，返回真，否则返回假
public function WritePrivateProfileInt( $sAppName, $sKeyName, $nValue )
{
	$sBuf = spintf( "%d", $nValue );
	return $this->WritePrivateProfileString( $sAppName, $sKeyName, $sBuf );
}
//---------------------------------------------------------
}
?>
