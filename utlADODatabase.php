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

class ConnectModeEnum
{
  const __default = self::adModeUnknown;
  const adModeUnknown        = 0;           // 默认值。权限尚未设置或不能确定权限。
  const adModeRead           = 1;           // 只读权限
  const adModeWrite          = 2;           // 只写权限
  
  // 读/写权限
  const adModeReadWrite      = 3;
  const adModeShareDenyRead  = 4;           // 禁止其他人以读权限打开连接
  const adModeShareDenyWrite = 8;           // 禁止其他人以写权限打开连接。
  
  const adModeShareExclusive = 12;          // 禁止其他人打开连接。
  const adModeShareDenyNone  = 16;          // 允许其他人以任何权限打开连接。不拒绝其他人的读或写访问。
  // 与 adModeShareDenyNone, adModeShareDenyWrite 或 adModeShareDenyRead 一起使用，
  // 对当前 Record 的所有子记录设置权限。
  const adModeRecursive      = 0x40000;//4194304;
}

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
  	$adUseClient = 3;
  	$this->ConnectString = $connstr;
  	$this->m_tConObj->CursorLocation = $adUseClient; 
  	$this->m_tConObj->Mode = ConnectModeEnum::adModeReadWrite;
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
	$tablenameArray = array();
	$bHasSysObj = false;
	$adSchemaTables = 20;
	$rs = $this->m_tConObj->OpenSchema( $adSchemaTables );
  while( !$rs->Eof )
  {
  	// 取出表名称
    $flddata = addslashes( $rs->Fields( "TABLE_NAME" )->Value );
    // 放入数组中
    $tablenameArray[] = $flddata;
      
    if ( $flddata == "sysobjects" )
    {
    	// 发现具有系统对象，准备重新从该表中获取表名称
      $bHasSysObj = true;
      break;
    }
   
   $rs->MoveNext();
  }
  
  if ( $bHasSysObj )
  {
  	$SysObj = new Com( "ADODB.Recordset" ); //实例化一个Recordset对象;
  	$CommandText = 
  	  "SELECT name AS TABLE_NAME FROM sysobjects " . 
      "WHERE ( type = 'U' ) OR ( type = 'V' ) ORDER BY name";
    
    $sysdata = array(); 
    try
    {
      $SysObj->Open( $CommandText, $this->m_tConObj );
      while( !$SysObj->Eof )
      {  	      
        $flddata = addslashes( $SysObj->Fields( "TABLE_NAME" )->Value );

        $sysdata[] = $flddata;
        $SysObj->MoveNext();
      }
      $tablenameArray = $sysdata;
    }
    catch( Exception $e )
	  {
	  }
  }
  else
  {
  }
  return $tablenameArray ;
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
class CursorType                            // 游标类型
{
  const __default = self::adOpenForwardOnly;
  // 前向游标，为缺省游标，提供最快的运行性能。用它打开recordset，
  // 从对至尾顺序取得所有结果。它不支持向后滚动，只允许在结果间单向移动。
  const adOpenForwardOnly    = 0;
  
  // 静态游标，反映第一次打开游标时表中数据的状态，游标无法查明底层
  // 表中的数据行是否更新过、删除过或添加了新的数据。不过与只能前移
  // 的游标不同，静态游标可以在结果间前后滚动。
  const adOpenKeyset         = 1;
  
  // 键盘驱动的游标，可以查询表中底层数据行的某些变化，但不是全部。
  // 它特别是可以准确反映数据是否更新过。但它不能查明其它用户是否曾
  // 删除过数据行（删除掉的数据行在recordset中会留下空洞）。键盘驱动
  // 的游标支持在结果间前后滚动。
  const adOpenDynamic        = 2;
  
  // 动态游标，是最丰富的游标类型。游标打开时可以查询其他用户对表的任何
  // 改动，而且支持滚动。
  const adOpenStatic         = 3;
}
//---------------------------------------------------------
class LockType                              // 加锁类型
{
  const __default = self::adLockReadOnly;
  // 缺省的上锁类型，只读方式上锁允许多个用户同时读取同样的数据，
  // 但不能改变数据。
  const adLockReadOnly        = 1;
  
  // 以悲观上锁方式打开数据对象。该方式假定在你编辑记录时会有其
  // 它用户访问数据。此时一旦你开始编辑记录，其它用户就不能访问
  // 该数据。
  const adLockPessimistic     = 2;
  
  // 以乐观上锁方式打开数据对象。该方式假定在你编辑记录时不会有
  // 其它用户访问数据。在完成改变之前，其它用户不能访问该记录。
  const adLockOptimistic      = 3;
  
  // 执行多行批处理更新时使用这种类型。
  const adLockBatchOptimistic = 4;
}
//---------------------------------------------------------
class CommandType                              // Options参数
{
	const __default = self::cmdUnknown;
	// 缺省值，不指定字符串的内容。
  const cmdUnknown            = 0;
  
  // 被执行的字符串包含一个命令文本。
  const cmdText               = 1;
  
  // 被执行的字符串包含一个表的名字
  const cmdTable              = 2;
  
  // 被执行的字符串包含一个存储过程名。 
  const cmdStoredProc         = 3;
  const cmdFile               = 4;
  const cmdTableDirect        = 5;
}
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

private $CursorType;                        // 游标类型
private $LockType;                          // 加锁类型
private $CommandType;                       // Options参数

private $m_bInEdit;                         // 是否处于修改状态
private $Eof;                               // 是否数据集的末尾
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
	
	$this->CursorType = CursorType::adOpenForwardOnly;
	$this->LockType = LockType::adLockOptimistic;
	$this->CommandType = CommandType::cmdText;
	
	$this->m_bInEdit = false;
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
	$this->m_tTable->Open( $this->CommandText, $this->m_pCon->m_tConObj, 
	  $this->CursorType, $this->LockType, $this->CommandType );
	  
	$this->Active = true;
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
/// @brief
///         取成员变量魔术函数，该函数好是好，但是本类的成员
///         函数调用不到，具体原因不明
/// @param
///         无
/// @return
///         返回相应变量的值
public function __get( $name )
{
	if ( $name == "Eof" )
	{
		return $this->m_tTable->Eof;
	}
  return $this->$name;
}
//---------------------------------------------------------
public function __set( $name, $value )
{	
	$this->$name = $value;
}
//---------------------------------------------------------
/// @brief
///         增加一条新的记录
/// @param
///         无
/// @return
///         无
public function Append()
{
	$this->m_tTable->AddNew();
}
//---------------------------------------------------------
/// @brief
///         进入记录修改状态，在此状态下修改记录数据才可以保存
/// @param
///         无
/// @return
///         无
public function Edit()
{
	$this->m_bInEdit = true;
}
//---------------------------------------------------------
/// @brief
///         根据字段名取一个字段
/// @param
///         [in]  $FldName      字段名称
/// @return
///         返回一个Field类型的对象
public function FieldByName( $FldName )
{
	return $this->m_tTable->Fields( $FldName );
}
//---------------------------------------------------------
/// @brief
///         根据索引取一个字段
/// @param
///         [in]  $FldIndex     字段下标
/// @return
///         返回一个Field类型的对象
public function FieldByIndex( $FldIndex )
{
	return $this->m_tTable->Fields( $FldIndex );
}
//---------------------------------------------------------
/// @brief
///         根据查找条件查找记录
/// @param
///         [in]  $sCondition   查找条件
/// @return
///         找到，返回真，否则返回假
private function InnerFind( $sCondition )
{
	// 先假设找到了
	$bResult = true;
	$this->m_tTable->Find( $sCondition );
	if ( $this->m_tTable->Eof )
	{
		// 到了记录集的末尾，返回假，表示找不到
		$bResult = false;
	}
	return $bResult;
}
//---------------------------------------------------------
/// @brief
///         根据查找条件查找记录
/// @param
///         [in]  $sConditionArray     查找条件的数组
/// @return
///         找到，返回真，否则返回假
public function Find( $sConditionArray )
{
	$bResult = false;
	// 先到第一条记录再说
	$this->First();
	
	while( !$bResult && !$this->m_tTable->Eof )
	{
		$count = count( $sConditionArray );
	  for( $i = 0; $i < $count; $i++ )
	  {
	  	$currRecNo = $this->RecNo();
		  if ( $this->InnerFind( $sConditionArray[ $i ] ) )
		  {
		  	if ( $count == ( $i + 1 ) )
		  	{
		  		// 数组的最后一个条件都找到了，说明所有条件都可以找到
		  	  $bResult = true;
		  	  break;
		  	}
		  	else if ( $currRecNo != $this->RecNo() )
		  	{
		  		// 当前记录号发生变更，则退出循环，需要重新从第一个条件再找
		  		break;
		  	}
		  }
		  else
		  {
		  	// 找不到，退出循环，终止查找
		  	break;
		  }
	  }
  }
  return $bResult;
}
//---------------------------------------------------------
/// @brief
///         删除当前记录
/// @param
///         无
/// @return
///         无
public function Delete()
{
	$this->m_tTable->Delete();
}
//---------------------------------------------------------
/// @brief
///         定位到第一条记录
/// @param
///         无
/// @return
///         无
public function First()
{
	$this->m_tTable->MoveFirst();
}
//---------------------------------------------------------
/// @brief
///         定位到最后一条记录
/// @param
///         无
/// @return
///         无
public function Last()
{
	$this->m_tTable->MoveLast();
}
//---------------------------------------------------------
/// @brief
///         定位到下一条记录
/// @param
///         无
/// @return
///         无
public function Next()
{
	$this->m_tTable->MoveNext();
}
//---------------------------------------------------------
/// @brief
///         取当前的记录号
/// @param
///         无
/// @return
///         返回当前的记录号
public function RecNo()
{
  return $this->m_tTable->AbsolutePosition;
}
//---------------------------------------------------------
/// @brief
///         定位到前一条记录
/// @param
///         无
/// @return
///         无
public function Prior()
{
	$this->m_tTable->MovePrevious();
}
//---------------------------------------------------------
/// @brief
///         将当前记录的修改保存到数据库中
/// @param
///         无
/// @return
///         无
public function Post()
{
	if ( $this->m_bInEdit )
	{
	  $this->m_tTable->Update();
	  $this->m_bInEdit = false;
	}
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
  	echo "记录数 = " . $PNRecord->RecordCount() . "<br>";
  	

    while ( !$PNRecord->Eof )
    {
    	echo $PNRecord->RecNo() . ",";
      for( $j = 0; $j < $PNRecord->Fieldcount(); $j++ )
      {
    	  $FldName = $PNRecord->FieldByIndex( $j )->Name;
        $flddata = addslashes( $PNRecord->FieldByName( $FldName )->Value );
        echo $flddata;
        echo ", ";
      }
      echo "<br />";
      $PNRecord->Next();
    }
  }
  else
  {
    echo "<br>打开 pnrecord 失败";
  }
  
  error_reporting( E_ALL );
  echo "<br><br>";
  $arr = $Con->GetTableNameList();
  
  for( $i = 0; $i < count( $arr ); $i++ )
  {
  	echo $arr[ $i ] . "<br />";
  }
  
  $cmd = new CADOCommand( $Con );
  
  if( !in_array( "PNTypeNameRecord", $arr ) )
  {
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
  }
  
  $cmd ->CommandText = 
   "update PNTypeNameRecord set TypeName = '555' WHERE TypeName = '333'";
  
  try
  {  
    $cmd->Execute();
  }
  catch( Exception $e )
  {
  	echo "<br>保存数据失败，原因：<br>" . $e->getMessage();
  }
   
  echo "<br><br>";
  $ra = new CADODataSet( $Con );
  $ra->CommandText = "SELECT * FROM PNTypeNameRecord ";
  try
  {
    $ra->Open();
  }
  catch( Exception $e )
	{
		echo "打开数据表出错，原因：<br>" . $e->getMessage();
	}
	
	error_reporting( E_ALL );

	$PNRecord->First();
	$condarr = array();
	$condarr[] = "PartType = '2'";
	$condarr[] = "PartNum = 610118";
	$condarr[] = "PartName = 'B'";

	try
	{
	  if ( $PNRecord->Find( $condarr ) )
	  {
		  echo "<br> found <br>";
		  
		  for( $j = 0; $j < $PNRecord->Fieldcount(); $j++ )
      {
    	  $FldName = $PNRecord->FieldByIndex( $j )->Name;
        $flddata = addslashes( $PNRecord->FieldByName( $FldName )->Value );
        echo $flddata;
        echo ", ";
      }
      echo "<br />";
	  }
	  else
	  {
		  echo "<br> not found <br>";
 	  }
  }
  catch( Exception $e )
	{
	  echo "查找数据出错，原因：<br>" . $e->getMessage();
	}
		
	// 下面的语句已经得到验证，暂时注释
	//try
	//{
	//	$ra->Append();
	//	$ra->Edit();
  //  $ra->FieldByName( "TypeName" )->Value = "123";
  //  $ra->Post();
  //}
  //catch( Exception $e )
	//{
	//echo "保存数据表出错，原因：<br>" . $e->getMessage();
	//}
?>