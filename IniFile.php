<?php
 require_once( 'LineFile.php' );
?>

<?php
/**********************************************************
/// @file   IniFile.php
/// @brief  PHPʵ�ֵ�INI�ļ���д������
/// @date   2019-06-24
/// @author elicl <elicl@163.com>
/// @par    ˵��
    ��ģ�����PHPʵ��һ��INI�ļ���д�����࣬ʵ��INI�ļ��Ķ�д
���ܡ�
**********************************************************/
//---------------------------------------------------------
class IniFile
{
const FOUNDNONE    = 0;           // δ�ҵ�
const FOUNDSECTION = 1;           // �ҵ��˽�
const FOUNDKEY     = 2;           // �ҵ��˼�

private $m_Linefile;              // ���ļ�����
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
///         ׷�ӱ���һ����ֵ��Ҫ��INI�ļ���������Ӧ�ļ�ֵ
/// @param
///         [in]  $sAppName     ����
///         [in]  $sKeyName     ����
///         [in]  $sValue       ��ֵ
/// @return
///         ���ɹ��������棬���򷵻ؼ�
private function AppendKey( $sAppName, $sKeyName, $sValue )
{
	$bResult = false;
	$handle = fopen( $this->m_Linefile->GetFileName(), "r+b" );
	if ( !$handle )
	{
		return false;
	}
	
	// д���
	$sSection = sprintf( "[%s]\r\n", $sAppName );
	fseek( $handle, 0, SEEK_END );
	$iToWriteLen = strlen( $sSection );
	$ret = fwrite( $handle, $sSection, $iToWriteLen );
	if ( $ret != $iToWriteLen )
	{
		$bResult = false;
	}
	
	// д���
	$sData = sprintf( "%s=%s\r\n", $sKeyName, $sValue );
	$iToWriteLen = strlen( $sData );
	$ret = fwrite( $handle, $sData, $iToWriteLen );
	if ( $ret != $iToWriteLen )
	{
		$bResult = false;
	}
	
	// �ر��ļ�
	fclose( $handle );
	
	// ����ͳ���ļ�����������Ϣ���Ա��������ȷʹ��
	$this->m_Linefile->CountTotalLines();
	return $bResult;
}
//---------------------------------------------------------
/// @brief
///         ����INI�ļ����޸���Ӧ�ļ�ֵ
/// @param
///         [in]  $sKeyName     ����
///         [in]  $sValue       ��ֵ
///         [in]  $dwCopyLine   �����Ƶķָ��к�
///         [in]  $bIsNew       Ϊ���ʾdwCopyLineָ������
///                             ��ҲҪ���ƣ�Ȼ���ڸ��к�������һ�����У�
///                             ���򣬱�ʾ������Ҫ���滻Ϊ�µ�����
/// @return
///         ���ɹ��������棬���򷵻ؼ�
private function BackIniFile( $sKeyName, $sValue, $dwCopyLine, $bIsNew )
{
	$bResult = false;
	$handle_read;
	$handle_write;

	// ������һ����ʱ�ļ���
	$stempname = tempnam( "","bak" ); 
	// ������ʱ�ļ�
	$handle_write = fopen( $stempname, "w+b" );
	if ( !$handle_write )
	{
		return false;
	}
	
	// ȡԭINI�ļ��ĳ���
	$iforglen = filesize( $this->m_Linefile->GetFileName() );
	
	// ����ָ��е���ʼλ��
	$file_pos = $this->m_Linefile->GetLinePos( $dwCopyLine );
	
	if ( $bIsNew )
	{
		// ������²����У������ҲҪ������������˻�Ҫ���ϸ��еĳ���
		// ��������²����У��򿽱������е���ʼλ�ü��ɣ���Ϊ������Ҫ������
		$file_pos += $this->m_Linefile->GetLineLength( $dwCopyLine );
	}
	
	// ��ԭ��INI�ļ�׼����ȡ����
	$handle_read = fopen( $this->m_Linefile->GetFileName(), "rb" );
	if ( $handle_read )
	{
		// ��ȡԭINI�ļ���ǰ�벿��
		$sBuf = fread( $handle_read, $file_pos );
		// д����ʱ�ļ�
		$ret = fwrite( $handle_write, $sBuf, $file_pos );
		if ( $ret == $file_pos )
		{
			$bResult = true;
		}
	}
	
	if ( $bResult )
	{
		// ������������
		$sBuf = sprintf( "%s=%s\r\n", $sKeyName, $sValue );
		$dwLineLen = strlen( $sBuf );
		// д����ʱ�ļ�
		$ret = fwrite( $handle_write, $sBuf, $dwLineLen );
		if ( $ret != $dwLineLen )
		{
			$bResult = false;
		}
	}
	
	if ( $bResult )
	{
		// ��ǰ���Ѿ���ǰ�洦����ˣ�Ҫô�����������ļ���Ҫô������
		// �������ʱ�򣬶�Ӧ�ô���һ�п�ʼ���ƾ��ļ��ĺ�������
		$dwCopyLine++;
		
		// �����ж�һ�£�����кų�������������˵��û�к���������				
		if ( $dwCopyLine < $this->m_Linefile->TotalLine() )
		{
			// �Ӹ��е���ʼλ�ÿ�ʼ��ǰ�������Ϊ�Ѿ��������
			$file_pos = $this->m_Linefile->GetLinePos( $dwCopyLine );
			// �ļ����ȼ�ȥ���е���ʼλ�ã���ʾ�Ӹ��п�ʼһֱ���ļ�ĩβ
			$read_len = $iforglen - $file_pos;
			// �ƶ�ԭINI�ļ�ָ�뵽���е���ʼλ��
			fseek( $handle_read, $file_pos, SEEK_SET );
			// ��ȡ�ļ���벿��
			$sBuf = fread( $handle_read, $read_len );
			// д����ʱ�ļ�
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
		// ɾ��ԭINI�ļ�
		unlink( $this->m_Linefile->GetFileName() );
		// ����ʱ�ļ��滻ԭINI�ļ�
		rename( $stempname, $this->m_Linefile->GetFileName() );
		// ����ͳ���ļ�����������Ϣ���Ա��������ȷʹ��
		$this->m_Linefile->CountTotalLines();
	}
	return $bResult;
}
//---------------------------------------------------------
/// @brief
///         ��ȡһ����ֵ��Ҫ��ָ������һ����ͨ������
/// @param
///         [in]  $sKeyName     ����
///         [in]  $iLineNo      �кţ���0��ʼ
/// @return
///         ���ؼ���ֵ
private function GetPureKeyData( $sKeyName, $iLineNo )
{
  // ȡ������
	$sData = $this->m_Linefile->GetLineData( $iLineNo );
	$sValue = false;
	$sEqualStr = "=";
	$iPos = strpos( $sData, $sEqualStr );

	if ( $iPos > 0 )
	{
		// ȡ����
		$sName = substr( $sData, 0, $iPos );
		// ȡ��ֵ
		$sV = substr( $sData, $iPos + strlen( $sEqualStr ), strlen( $sData ) );
		if ( trim( $sName ) == $sKeyName )
		{
			// �������ϣ���ֵ��ֵ
			$sValue = trim( $sV );
		}
	}
	// ���ؼ�ֵ
	return $sValue;
}
//---------------------------------------------------------
/// @brief
///         ��ȡһ��������Ҫ��ָ������һ������
/// @param
///         [in]  $iLineNo      �кţ���0��ʼ
/// @return
///         ���ؽڵ�����
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
		// �����޳����������ŵĽ�����
		$sResult = substr( $sSectionName, $iBegin + 1, $iEnd - $iBegin - 1 );
	}
	return $sResult;
}
//---------------------------------------------------------
/// @brief
///         ��INI�ļ��в�������ָ������ָ�����������ݡ�
/// @param
///         [in]  $sAppName     ����
///         [in]  $sKeyName     ����
///         [in]  $sDefault     ��ֵȱʡֵ
///         [out] $tKeyFound    �������ҽ����δ�ҵ����ҵ��ڡ��ҵ���
///         [out] $dwSeparateNo �����ָ��кţ����к�ָ�����ڽڵ�����е��к�
/// @return
///         ���ҵ������ؼ���ֵ�����򷵻�ȱʡ��ֵ
private function LocatePrivateProfileString( $sAppName, $sKeyName, $sDefault, &$tKeyFound, &$dwSeparateNo )
{
	$sResult   = $sDefault;
	$tKeyFound = IniFile::FOUNDNONE;
	
	$dwBeginLine;
	$dwEndLine;

	if ( $this->SearchSection( $sAppName, $dwBeginLine, $dwEndLine ) )
	{
		// ���ñ�־���ҵ��˽�
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
					// ������ֵ��Ӧ���к�
					$dwSeparateNo = $i;
					// ���ñ�־���ҵ���ֵ
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
///         ����INI���ļ���
/// @param
///         [in]  $sFileName    INI�ļ���
/// @return
///         ��
public function SetFileName( $sFileName )
{
	$this->m_Linefile->SetFileName( $sFileName );
	$this->m_Linefile->CountTotalLines();
}
//---------------------------------------------------------
/// @brief
///         ����ָ���Ľ���
/// @param
///         [in]  $sAppName       ������
///         [out] $dwBeginLineNo  �ýڵ���ʼ�к�
///         [out] $dwEndLineNo    �ýڵĽ����к�
/// @return
///         �ҵ�ָ���Ľڣ������棬���򷵻ؼ�
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
				// �ҵ���һ���µĽڣ��ýڵ��кŵ�ǰһ�к�Ϊ�ýڵĽ����к�
				$dwEndLineNo = $i - 1;
				// ��������
				break;
			}
			else if ( $this->GetPureSectionData( $i ) == $sAppName )
			{
				// �ȼ�¼��ʼ�к�
				$dwBeginLineNo = $i;
				$dwEndLineNo = $this->m_Linefile->TotalLine();
				if ( $dwEndLineNo > 0 )
				{
					// ������1�����к�
					$dwEndLineNo--;
				}
				// ���ñ�־���ҵ��˽���
				$bHasFoundSectionName = true;
				// ���÷��ر�־Ϊ��
				$bResult = true;
			}
		}
		else
		{
			// ��һ��
			continue;
		}
	}
	return $bResult;
}
//---------------------------------------------------------
/// @brief
///          ��ȡһ������������
/// @param
///         [in] $sAppName        ������
///         [in] $sKeyName        ������
///         [in] $nDefault        ȱʡ��ֵ
/// @return
///         ���ظýڶ�Ӧ�ļ���������ֵ����INI�����ڸ���򷵻�ȱʡֵ
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
///          ��ȡһ���ַ���������
/// @param
///         [in] $sAppName        ������
///         [in] $sKeyName        ������
///         [in] $sDefault        ȱʡ��ֵ
/// @return
///         ���ظýڶ�Ӧ�ļ������ַ���ֵ����INI�����ڸ���򷵻�ȱʡֵ
public function GetPrivateProfileString( $sAppName, $sKeyName, $sDefault )
{
	$tKeyfound;
	$dwLineNo;
	return $this->LocatePrivateProfileString( $sAppName, $sKeyName, $sDefault, 
	  $tKeyfound, $dwLineNo );
}
//---------------------------------------------------------
/// @brief
///          д��һ���ַ���������
/// @param
///         [in] $sAppName        ������
///         [in] $sKeyName        ������
///         [in] $sValue          ��ֵ
/// @return
///         д��ɹ��������棬���򷵻ؼ�
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
///          д��һ������������
/// @param
///         [in] $sAppName        ������
///         [in] $sKeyName        ������
///         [in] $nValue          ��ֵ
/// @return
///         д��ɹ��������棬���򷵻ؼ�
public function WritePrivateProfileInt( $sAppName, $sKeyName, $nValue )
{
	$sBuf = spintf( "%d", $nValue );
	return $this->WritePrivateProfileString( $sAppName, $sKeyName, $sBuf );
}
//---------------------------------------------------------
}
?>
