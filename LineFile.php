<?php
/**********************************************************
/// @file   LineFile.php
/// @brief  PHPʵ�ֵ�INI�ļ��ı����ļ���ȡ������
/// @date   2019-06-21
/// @author elicl <elicl@163.com>
/// @par    ˵��
    ��ģ�����PHPʵ��һ��INI�ı��ļ���ȡ�����࣬��ȡʱ��INI
�ļ��ĸ�ʽ���г���������ͳ���ı�������ÿ�еĳ��ȡ����ͣ��е�
���Ȱ����س����з��������Ͱ������С�ע���С���ͨ�����С�
**********************************************************/
//---------------------------------------------------------
class LineStru
{
	const BLANKLINE   = 0;        // ����
	const NORMALLINE  = 1;        // ��ͨ��
	const SECTIONLINE = 2;        // ����
	const REMARKLINE  = 3;        // ע����
	
	public $m_dwPos = 0;
	public $m_dwLength = 0;
	public $m_tLineAttr = self::BLANKLINE;
}
//---------------------------------------------------------
//---------------------------------------------------------
//---------------------------------------------------------
class LineFile
{
const STABLANK     = 0;           // ����״̬ 
const STANORMAL    = 1;           // ��ͨ״̬
const STALBRACKET  = 2;           // ������������
const STARBRACKET  = 3;           // ������������
const STAREMARK    = 4;           // ����ע�ͷ���

const MAXLINES     = 1024;        // ����г���

private $m_dwLineCount = 0;       // �ļ�������
private $m_sFileName;             // �ļ���
private $m_taLines;               // ����������

private $m_LineSta;               // �з���״̬

private $m_IsEncounter0D;         // �Ƿ�������0X0D
//---------------------------------------------------------
public function __construct()
{
}
//---------------------------------------------------------
public function __destruct()
{
}
//---------------------------------------------------------
/// @brief
///         ����INI���ļ���
/// @param
///         [in]  $sFileName    INI���ļ���
/// @return
///         ��
public function SetFileName( $sFileName )
{
	$this->m_sFileName = $sFileName;
}
//---------------------------------------------------------
/// @brief
///         ȡINI���ļ���
/// @param
///         ��
/// @return
///         INI���ļ���
public function GetFileName()
{
	return $this->m_sFileName;
}
//---------------------------------------------------------
/// @brief
///         ͳ��INI�ļ���������������INI�ļ��ĸ�ʽ����ͳ��
///         �����ܺ��������γɸ��е�����������
/// @param
///         ��
/// @return
///         ��
public function CountTotalLines()
{
	$handle;
	
	$this->m_taLines = array();
	
	if ( !$this->m_sFileName )
	{
		return;
	}
	
	if ( !file_exists( $this->m_sFileName ) )
	{
		// �ļ������ڣ�ֱ�ӷ���
		return;
	}
	
	try
	{
		// �Զ����Ƶķ�ʽ���ļ����ж�д
	  $handle = fopen( $this->m_sFileName, "rb" );
  }
  catch( Exception $e )
  {
  	echo $e->getMessage();
  }
	
	if ( !$handle )
	{
		return;
	}
	
	while( true )
	{
		// ȡһ����Ϣ
		$ALine = $this->GetALine( $handle );
		if ( $ALine->m_dwLength > 0 )
		{
			// ������
			$this->m_taLines[] = $ALine;
		}
		else
		{
			// ����Ч�У��˳�ѭ��
			break;
		}
	}
	// �ȹر��ļ���˵
	fclose( $handle );
	
	// ��һ�е�λ�ø�ֵΪ0
	$this->m_taLines[ 0 ]->m_dwPos = 0;
	for( $i = 1; $i < $this->TotalLine(); $i++ )
	{
		$LastLine = $this->m_taLines[ $i - 1 ];
		// ��ǰ�е�λ�õ���ǰһ�е�λ�ü�ǰһ�еĳ���
		$this->m_taLines[ $i ]->m_dwPos = $LastLine->m_dwPos + $LastLine->m_dwLength;
	}
}
//---------------------------------------------------------
/// @brief
///         ��ȡһ����Ϣ
/// @param
///         [in]  $handle       INI���ļ����
/// @return
///         һ������Ϣ����
public function GetALine( $handle )
{
	$bDone = false;
	
	// ����һ������Ϣ����
	$ALine = new LineStru();
	
	// �з���״̬����ֵΪ��
	$this->m_LineSta = LineFile::STABLANK;
	
	while( !$bDone )
	{
		// ȡһ���ַ�
    $ch = fgetc( $handle );
    
    if ( feof( $handle ) )
    {
    	// �����ļ�ĩβ��ֱ���˳�ѭ����
    	break;
    }
    
    $ALine->m_dwLength++;              // �г��ȼ�1

    switch( $this->m_LineSta )
    {
    case LineFile::STABLANK:           // ��ʼ״̬
      switch( $ch )
      {
      // �ո񡢻س������У�״̬���䣬��������
      case "\x20":
      case "\x0D":
      case "\x0A":
        break;
      case "[":
        // �������ţ������״̬
        $this->m_LineSta = LineFile::STALBRACKET;
        break;
      case ";":
        // �ֺţ�����ע����״̬
        $this->m_LineSta = LineFile::STAREMARK;
        break;
      default:
        //  �ǿո������ַ���������ͨ����״̬
        $this->m_LineSta = LineFile::STANORMAL;
        break;
      }
    case LineFile::STANORMAL:          // ��ͨ����
      // ״̬���ֲ��䣬��������
      break;
    case LineFile::STALBRACKET:        // �Ѿ�������������
      switch( $ch )
      {
      case "]":
        // ��������״̬���������������ţ���¼��״̬
        $this->m_LineSta = LineFile::STARBRACKET;
        break;
      default:
        break;
      }
      break;
    case LineFile::STARBRACKET:        // �Ѿ�������������
      switch( $ch )
      {
      case "\x0D":
      case "\x0A":
        break;
      default:
        // ���������»��������ǻس������з����ٴε�����ͨ������
        $this->m_LineSta = LineFile::STANORMAL;
        break;
      }
      break;
    case LineFile::STAREMARK;          // ����ע����
      // ״̬���ֲ��䣬��������
      break;
    default:
    	break;
    }
    
    switch( $ch )
    {
    case "\x0D":
      // ��ǰ�����س������ñ�־
      $this->m_IsEncounter0D = true;
      break;
    case "\x0A":
      if ( $this->m_IsEncounter0D )
      {
      	// �س����������У�����һ�еķ���
      	$bDone = true;
      }
      // ����س���־
      $this->m_IsEncounter0D = false;
      break;
    default:
      // �س����������ַ������ϻس���־
      $this->m_IsEncounter0D = false;
      break;
    }
  }
  
  switch( $this->m_LineSta )
  {
  case LineFile::STANORMAL:
    // ��ͨ������
    $ALine->m_tLineAttr = LineStru::NORMALLINE;
  	break;
  case LineFile::STARBRACKET:
    // ����
  	$ALine->m_tLineAttr = LineStru::SECTIONLINE;
  	break;
  case LineFile::STAREMARK:
    // ע����
    $ALine->m_tLineAttr = LineStru::REMARKLINE;
  	break;
  default:
    // ����(ֻ�лس�������)
    $ALine->m_tLineAttr = LineStru::BLANKLINE;
    break;
  }
	return $ALine;
}
//---------------------------------------------------------
/// @brief
///         ����������
/// @param
///         ��
/// @return
///         ��ǰ�ļ���������
public function TotalLine()
{
	return Count( $this->m_taLines );
}
//---------------------------------------------------------
/// @brief
///         ȡָ���е�����
/// @param
///         [in]  $LineNo       �кţ���0��ʼ
/// @return
///         ��ǰ�е����ͣ����С����С�ע���С���ͨ����������
public function GetLineAttr( $LineNo )
{
	if ( $LineNo < $this->TotalLine() )
	{
		return $this->m_taLines[ $LineNo ]->m_tLineAttr;
	}
	return LineStru::BLANKLINE;
}
//---------------------------------------------------------
/// @brief
///         ȡָ���еĳ���
/// @param
///         [in]  $LineNo       �кţ���0��ʼ
/// @return
///         ��ǰ�еĳ���
public function GetLineLength( $LineNo )
{
	$dwResult = 0;
	if ( $LineNo < $this->TotalLine() )
	{
		$dwResult = $this->m_taLines[ $LineNo ]->m_dwLength;
	}
	return $dwResult;
}
//---------------------------------------------------------
/// @brief
///         ȡָ���е��ļ�ƫ��λ��
/// @param
///         [in]  $LineNo       �кţ���0��ʼ
/// @return
///         ��ǰ�е��ļ�ƫ��λ��
public function GetLinePos( $LineNo )
{
	$dwResult = 0;
	if ( $LineNo < $this->TotalLine() )
	{
		$dwResult = $this->m_taLines[ $LineNo ]->m_dwPos;
	}
	return $dwResult;
}
//---------------------------------------------------------
/// @brief
///         ��ȡָ���е��ļ�����
/// @param
///         [in]  $LineNo       �кţ���0��ʼ
/// @return
///         ��ǰ�е��ļ�����
public function GetLineData( $LineNo )
{
	$Result = false;
	if ( $LineNo < $this->TotalLine() )
	{
		$handle = fopen( $this->m_sFileName, "rb" );
		if ( $handle )
		{
			fseek( $handle, $this->GetLinePos( $LineNo ), SEEK_SET );
			$Result = fread( $handle, $this->GetLineLength( $LineNo ) );
			// �޳����пհ��ַ��������ո񡢻س������С��Ʊ����
			$Result = trim( $Result );
			fclose( $handle );
		}	
  }
  return $Result;
}
//---------------------------------------------------------
}
//---------------------------------------------------------
?>
