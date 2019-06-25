<?php
/**********************************************************
/// @file   LineFile.php
/// @brief  PHP实现的INI文件文本行文件读取工具类
/// @date   2019-06-21
/// @author elicl <elicl@163.com>
/// @par    说明
    本模块采用PHP实现一个INI文本文件读取工具类，读取时按INI
文件的格式进行初步分析，统计文本行数及每行的长度、类型，行的
长度包括回车换行符。行类型包括节行、注释行、普通数据行。
**********************************************************/
//---------------------------------------------------------
class LineStru
{
	const BLANKLINE   = 0;        // 空行
	const NORMALLINE  = 1;        // 普通行
	const SECTIONLINE = 2;        // 节行
	const REMARKLINE  = 3;        // 注释行
	
	public $m_dwPos = 0;
	public $m_dwLength = 0;
	public $m_tLineAttr = self::BLANKLINE;
}
//---------------------------------------------------------
//---------------------------------------------------------
//---------------------------------------------------------
class LineFile
{
const STABLANK     = 0;           // 空闲状态 
const STANORMAL    = 1;           // 普通状态
const STALBRACKET  = 2;           // 遇到左中括号
const STARBRACKET  = 3;           // 遇到右中括号
const STAREMARK    = 4;           // 遇到注释符号

const MAXLINES     = 1024;        // 最大行长度

private $m_dwLineCount = 0;       // 文件总行数
private $m_sFileName;             // 文件名
private $m_taLines;               // 行数据数组

private $m_LineSta;               // 行分析状态

private $m_IsEncounter0D;         // 是否遇到了0X0D
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
///         设置INI的文件名
/// @param
///         [in]  $sFileName    INI的文件名
/// @return
///         无
public function SetFileName( $sFileName )
{
	$this->m_sFileName = $sFileName;
}
//---------------------------------------------------------
/// @brief
///         取INI的文件名
/// @param
///         无
/// @return
///         INI的文件名
public function GetFileName()
{
	return $this->m_sFileName;
}
//---------------------------------------------------------
/// @brief
///         统计INI文件的总行数，按照INI文件的格式进行统计
///         计算总函数，并形成各行的行数据数组
/// @param
///         无
/// @return
///         无
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
		// 文件不存在，直接返回
		return;
	}
	
	try
	{
		// 以二进制的方式打开文件进行读写
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
		// 取一行信息
		$ALine = $this->GetALine( $handle );
		if ( $ALine->m_dwLength > 0 )
		{
			// 保存行
			$this->m_taLines[] = $ALine;
		}
		else
		{
			// 非有效行，退出循环
			break;
		}
	}
	// 先关闭文件再说
	fclose( $handle );
	
	// 第一行的位置赋值为0
	$this->m_taLines[ 0 ]->m_dwPos = 0;
	for( $i = 1; $i < $this->TotalLine(); $i++ )
	{
		$LastLine = $this->m_taLines[ $i - 1 ];
		// 当前行的位置等于前一行的位置加前一行的长度
		$this->m_taLines[ $i ]->m_dwPos = $LastLine->m_dwPos + $LastLine->m_dwLength;
	}
}
//---------------------------------------------------------
/// @brief
///         读取一行信息
/// @param
///         [in]  $handle       INI的文件句柄
/// @return
///         一个行信息数据
public function GetALine( $handle )
{
	$bDone = false;
	
	// 创建一个行信息数据
	$ALine = new LineStru();
	
	// 行分析状态机初值为空
	$this->m_LineSta = LineFile::STABLANK;
	
	while( !$bDone )
	{
		// 取一个字符
    $ch = fgetc( $handle );
    
    if ( feof( $handle ) )
    {
    	// 到了文件末尾，直接退出循环。
    	break;
    }
    
    $ALine->m_dwLength++;              // 行长度加1

    switch( $this->m_LineSta )
    {
    case LineFile::STABLANK:           // 初始状态
      switch( $ch )
      {
      // 空格、回车、换行，状态不变，继续分析
      case "\x20":
      case "\x0D":
      case "\x0A":
        break;
      case "[":
        // 左中括号，进入该状态
        $this->m_LineSta = LineFile::STALBRACKET;
        break;
      case ";":
        // 分号，进入注释行状态
        $this->m_LineSta = LineFile::STAREMARK;
        break;
      default:
        //  非空格及特殊字符，进入普通数据状态
        $this->m_LineSta = LineFile::STANORMAL;
        break;
      }
    case LineFile::STANORMAL:          // 普通数据
      // 状态保持不变，继续分析
      break;
    case LineFile::STALBRACKET:        // 已经遇到左中括号
      switch( $ch )
      {
      case "]":
        // 左中括号状态下又遇到右中括号，记录新状态
        $this->m_LineSta = LineFile::STARBRACKET;
        break;
      default:
        break;
      }
      break;
    case LineFile::STARBRACKET:        // 已经遇到右中括号
      switch( $ch )
      {
      case "\x0D":
      case "\x0A":
        break;
      default:
        // 右中括号下还有其它非回车、换行符，再次当作普通数据行
        $this->m_LineSta = LineFile::STANORMAL;
        break;
      }
      break;
    case LineFile::STAREMARK;          // 遇到注释行
      // 状态保持不变，继续分析
      break;
    default:
    	break;
    }
    
    switch( $ch )
    {
    case "\x0D":
      // 当前遇到回车，设置标志
      $this->m_IsEncounter0D = true;
      break;
    case "\x0A":
      if ( $this->m_IsEncounter0D )
      {
      	// 回车后遇到换行，结束一行的分析
      	$bDone = true;
      }
      // 清除回车标志
      $this->m_IsEncounter0D = false;
      break;
    default:
      // 回车后有其它字符，作废回车标志
      $this->m_IsEncounter0D = false;
      break;
    }
  }
  
  switch( $this->m_LineSta )
  {
  case LineFile::STANORMAL:
    // 普通数据行
    $ALine->m_tLineAttr = LineStru::NORMALLINE;
  	break;
  case LineFile::STARBRACKET:
    // 节行
  	$ALine->m_tLineAttr = LineStru::SECTIONLINE;
  	break;
  case LineFile::STAREMARK:
    // 注释行
    $ALine->m_tLineAttr = LineStru::REMARKLINE;
  	break;
  default:
    // 空行(只有回车、换行)
    $ALine->m_tLineAttr = LineStru::BLANKLINE;
    break;
  }
	return $ALine;
}
//---------------------------------------------------------
/// @brief
///         计算总行数
/// @param
///         无
/// @return
///         当前文件的总行数
public function TotalLine()
{
	return Count( $this->m_taLines );
}
//---------------------------------------------------------
/// @brief
///         取指定行的类型
/// @param
///         [in]  $LineNo       行号，从0开始
/// @return
///         当前行的类型：空行、节行、注释行、普通数据行四类
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
///         取指定行的长度
/// @param
///         [in]  $LineNo       行号，从0开始
/// @return
///         当前行的长度
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
///         取指定行的文件偏移位置
/// @param
///         [in]  $LineNo       行号，从0开始
/// @return
///         当前行的文件偏移位置
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
///         读取指定行的文件内容
/// @param
///         [in]  $LineNo       行号，从0开始
/// @return
///         当前行的文件内容
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
			// 剔除所有空白字符，包括空格、回车、换行、制表符等
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
