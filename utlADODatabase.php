<?php

/**********************************************************
/// @file   utlADODatabase.php
/// @brief  PHP数据库访问工具类
/// @date   2019-06-05
/// @author elicl <elicl@163.com>
/// @par    说明
    本模块创建一个基于ADO的数据库连接类及数据库访问类，用于
数据库功能。
    注意，本模块采用的COM方式，通过本类可以确定PHP对COM的支
持是比较良好的。
**********************************************************/
class CADOCon
// 数据库连接类，用于数据库连接，全局仅需要一个数据库连接对象即可
// 被多个数据集对象使用
{
//---------------------------------------------------------
public $ConnectString;                      // 连接的字符串
public $m_tConObj;                          // 数据库连接资源
//---------------------------------------------------------
/// @brief
///         构造函数
/// @param
///         [in]  $sdsn         数据源名称
///         [in]  $suser        登录用户名
///         [in]  $spassword    登录密码
/// @return
///         无
public function __construct( $connstr )
{
	// 告诉PHP，报告所有错误，主要是
  // 如果不报告错误，就不知道发生了什么问题
  error_reporting( E_ALL );
  $this->m_tConObj = new Com( "ADODB.Connection" ); //实例化一个Connection对象
  if ( $this->m_tConObj )
  {
  	$this->ConnectString = $connstr;
  	$this->m_tConObj->Open( $connstr );
  }
  else
  {
  	exit( "创建组件对象失败: " . $this->m_tConObj );
  }
  // 这个时候，可以关闭所有错误了，以便关闭运行期间的各种警告错误。
  error_reporting( 0 );
}
//---------------------------------------------------------
/// @brief
///         析构函数
/// @param
///         无
/// @return
///         无
function __destruct()
{
	 $this->m_tConObj->close();
}
//---------------------------------------------------------
public function GetTableNameList()
{
	$bHasSysObj = false;
	$adSchemaTables = 20;
	$rs = $this->m_tConObj->OpenSchema( $adSchemaTables );
  while( !$rs->Eof )
  {  	      
    for( $j = 0; $j < $rs->Fields->Count; $j++ )
    {
    	$FldName = $rs->Fields( $j )->Name;
      $flddata = addslashes( $rs->Fields( $FldName )->Value );
      
      if ( $flddata == "sysobjects" )
      {
      	$bHasSysObj = true;
      	break;
      }
    }
    $rs->MoveNext();
  }
  
  if ( $bHasSysObj )
  {
  	$SysObj = new Com( "ADODB.Recordset" ); //实例化一个Recordset对象;
  	$CommandText = 
  	  "SELECT name AS TABLE_NAME FROM sysobjects " . 
      "WHERE ( type = 'U' ) OR ( type = 'V' ) ORDER BY name";
     
    try
    {
      $SysObj->Open( $CommandText, $this->m_tConObj, 1, 3 );  
      return $SysObj;
    }
    catch( Exception $e )
	  {
		  $rs->MoveFirst();
	  }
  }
  else
  {
  	return $rs;
  }
}
//---------------------------------------------------------
}
//---------------------------------------------------------
//---------------------------------------------------------
//---------------------------------------------------------
class CADOCommand
// 数据库Command工具类，主要用于执行各种数据库操作，
// 该类对象需要使用一个CADOCon对象作为数据库连接
// 该类对象可以通过SQL语句实现创建数据表，为已有的数据表添加
// 字段等各种功能。
{
/// @brief
///         构造函数
/// @param
///         [in]  $conObj       数据库数连接对象
/// @return
///         无
public $m_pCon;                             // 数据库连接对象
public $m_pCmd;                             // command对象
// 定义各种属性
private $CommandText;                       // 数据集SQL语句
public function __construct( $conObj )
{
	$this->m_pCon = $conObj;
	$this->m_pCmd = new Com( "ADODB.command" ); //实例化一个command对象
	$this->m_pCmd->ActiveConnection = $conObj->m_tConObj;
}
//---------------------------------------------------------
public function __destruct()
{
}
//---------------------------------------------------------
public function __get( $name )
{
  return $this->$name;
}
//---------------------------------------------------------
public function __set( $name, $value )
{
	if ( $name == "CommandText" )
	{
		// 利用魔法函数实现对Command对象的相应属性的赋值
	  $this->m_pCmd->CommandText = $value;
	}
	
	$this->$name = $value;
}
public function Execute()
{
  $this->m_pCmd->Execute();
}
//---------------------------------------------------------
}
//---------------------------------------------------------
//---------------------------------------------------------
//---------------------------------------------------------
class CADODataSet
// 数据集工具类，主要提供对Recordset的一个简单封装，实现数据
// 集的便捷操作
{
//---------------------------------------------------------
public $m_pCon;                             // 数据库连接对象
public $m_tTable;                           // 数据集资源
// 定义各种属性
private $Active;                            // 数据集是否已经打开
private $CommandText;                       // 数据集SQL语句
//---------------------------------------------------------
/// @brief
///         构造函数
/// @param
///         [in]  $conObj       数据库数连接对象
/// @return
///         无
public function __construct( $conObj )
{
	$this->m_pCon = $conObj;
	$this->m_tTable = new Com( "ADODB.Recordset" ); //实例化一个Recordset对象
	$this->Active = false;
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
///         关闭数据集
/// @param
///         无
/// @return
///         无
public function Close()
{
	if ( $this->Active )
	{
 	  $this->m_tTable->Close();
 	}
}
//---------------------------------------------------------
/// @brief
///         打开数据集
/// @param
///         无
/// @return
///         无
public function Open()
{
	$this->m_tTable->Open( $this->CommandText, $this->m_pCon->m_tConObj, 1, 3 );
	$this->Active = true;

 	$table =  $this->m_tTable;
  while( !$table->Eof )
  {
    for( $j = 0; $j < $table->Fields->Count; $j++ )
    {
    	$FldName = $table->Fields( $j )->Name;
      $flddata = addslashes( $table->Fields( $FldName )->Value );
      
      echo $flddata;
      echo ", ";
    }
    echo "<br />";
    $table->MoveNext();
  }
}
//---------------------------------------------------------
/// @brief
///         取数据集的字段数
/// @param
///         无
/// @return
///         当前数据集的字段数
public function FieldCount()
{
	return $this->m_tTable->Fields->Count;
}
//---------------------------------------------------------
/// @brief
///         取数据集的记录数
/// @param
///         无
/// @return
///         当前数据集的记录数
public function RecordCount()
{
	return $this->m_tTable->RecordCount;
}
//---------------------------------------------------------
public function __get( $name )
{
  return $this->$name;
}
//---------------------------------------------------------
public function __set( $name, $value )
{	
	$this->$name = $value;
}
//---------------------------------------------------------
}
//---------------------------------------------------------

  // 以下为一些简单的测试代码
  
  $connstr = "provider=sqloledb.1;;Password=PN123456;Persist Security Info=True;" .
             "User ID=PN;Initial Catalog=PNMNG;Data Source=192.168.133.128";
  $Con = new CADOCon( $connstr );
    
  $PNRecord = new CADODataSet( $Con );
  $PNRecord->CommandText = "SELECT * FROM PNRecord";
  
  try
  {
    $PNRecord->Open();
  }
  catch( Exception $e )
	{
		echo "打开数据表出错，原因：<br>" . $e->getMessage();
	}
  
  if ( $PNRecord->Active )
  {
  	echo "打开 pnrecord 成功<br>";
  	echo "字段数 = " . $PNRecord->Fieldcount();
  	echo "记录数 = " . $PNRecord->RecordCount();
  }
  else
  {
    echo "<br>打开 pnrecord 失败";
  }
  
  $cmd = new CADOCommand( $Con );
  $cmd->CommandText = 
    "create table PNTypeNameRecord \n " .
    " ( \n" . 
    "PTN_ID int identity(1, 1) primary key, \n" .
    "TypeName varchar(30) \n" .
    ") \n";
    
  try
  {  
    $cmd->Execute();
  }
  catch( Exception $e )
  {
  	echo "<br>创建数据表出错，原因：<br>" . $e->getMessage();
  }
  
  error_reporting( E_ALL );
  echo "<br><br>";
  $rs = $Con->GetTableNameList();
  
  
  while( !$rs->Eof )
      {          
        for( $j = 0; $j < $rs->Fields->Count; $j++ )
        {
    	    $FldName = $rs->Fields( $j )->Name;
          $flddata = addslashes( $rs->Fields( $FldName )->Value );
      
          echo $flddata;
          echo ", ";
        }
    
        echo "<br />";
        $rs->MoveNext();
      }
?>