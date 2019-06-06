<?php

/**********************************************************
/// @file   utlADODatabase.php
/// @brief  PHP���ݿ���ʹ�����
/// @date   2019-06-05
/// @author elicl <elicl@163.com>
/// @par    ˵��
    ��ģ�鴴��һ������ADO�����ݿ������༰���ݿ�����࣬����
���ݿ⹦�ܡ�
    ע�⣬��ģ����õ�COM��ʽ��ͨ���������ȷ��PHP��COM��֧
���ǱȽ����õġ�
**********************************************************/
class CADOCon
// ���ݿ������࣬�������ݿ����ӣ�ȫ�ֽ���Ҫһ�����ݿ����Ӷ��󼴿�
// ��������ݼ�����ʹ��
{
//---------------------------------------------------------
public $ConnectString;                      // ���ӵ��ַ���
public $m_tConObj;                          // ���ݿ�������Դ
//---------------------------------------------------------
/// @brief
///         ���캯��
/// @param
///         [in]  $sdsn         ����Դ����
///         [in]  $suser        ��¼�û���
///         [in]  $spassword    ��¼����
/// @return
///         ��
public function __construct( $connstr )
{
	// ����PHP���������д�����Ҫ��
  // �����������󣬾Ͳ�֪��������ʲô����
  error_reporting( E_ALL );
  $this->m_tConObj = new Com( "ADODB.Connection" ); //ʵ����һ��Connection����
  if ( $this->m_tConObj )
  {
  	$this->ConnectString = $connstr;
  	$this->m_tConObj->Open( $connstr );
  }
  else
  {
  	exit( "�����������ʧ��: " . $this->m_tConObj );
  }
  // ���ʱ�򣬿��Թر����д����ˣ��Ա�ر������ڼ�ĸ��־������
  error_reporting( 0 );
}
//---------------------------------------------------------
/// @brief
///         ��������
/// @param
///         ��
/// @return
///         ��
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
  	$SysObj = new Com( "ADODB.Recordset" ); //ʵ����һ��Recordset����;
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
// ���ݿ�Command�����࣬��Ҫ����ִ�и������ݿ������
// ���������Ҫʹ��һ��CADOCon������Ϊ���ݿ�����
// ����������ͨ��SQL���ʵ�ִ������ݱ�Ϊ���е����ݱ����
// �ֶεȸ��ֹ��ܡ�
{
/// @brief
///         ���캯��
/// @param
///         [in]  $conObj       ���ݿ������Ӷ���
/// @return
///         ��
public $m_pCon;                             // ���ݿ����Ӷ���
public $m_pCmd;                             // command����
// �����������
private $CommandText;                       // ���ݼ�SQL���
public function __construct( $conObj )
{
	$this->m_pCon = $conObj;
	$this->m_pCmd = new Com( "ADODB.command" ); //ʵ����һ��command����
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
		// ����ħ������ʵ�ֶ�Command�������Ӧ���Եĸ�ֵ
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
// ���ݼ������࣬��Ҫ�ṩ��Recordset��һ���򵥷�װ��ʵ������
// ���ı�ݲ���
{
//---------------------------------------------------------
public $m_pCon;                             // ���ݿ����Ӷ���
public $m_tTable;                           // ���ݼ���Դ
// �����������
private $Active;                            // ���ݼ��Ƿ��Ѿ���
private $CommandText;                       // ���ݼ�SQL���
//---------------------------------------------------------
/// @brief
///         ���캯��
/// @param
///         [in]  $conObj       ���ݿ������Ӷ���
/// @return
///         ��
public function __construct( $conObj )
{
	$this->m_pCon = $conObj;
	$this->m_tTable = new Com( "ADODB.Recordset" ); //ʵ����һ��Recordset����
	$this->Active = false;
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
///         �ر����ݼ�
/// @param
///         ��
/// @return
///         ��
public function Close()
{
	if ( $this->Active )
	{
 	  $this->m_tTable->Close();
 	}
}
//---------------------------------------------------------
/// @brief
///         �����ݼ�
/// @param
///         ��
/// @return
///         ��
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
///         ȡ���ݼ����ֶ���
/// @param
///         ��
/// @return
///         ��ǰ���ݼ����ֶ���
public function FieldCount()
{
	return $this->m_tTable->Fields->Count;
}
//---------------------------------------------------------
/// @brief
///         ȡ���ݼ��ļ�¼��
/// @param
///         ��
/// @return
///         ��ǰ���ݼ��ļ�¼��
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

  // ����ΪһЩ�򵥵Ĳ��Դ���
  
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
		echo "�����ݱ����ԭ��<br>" . $e->getMessage();
	}
  
  if ( $PNRecord->Active )
  {
  	echo "�� pnrecord �ɹ�<br>";
  	echo "�ֶ��� = " . $PNRecord->Fieldcount();
  	echo "��¼�� = " . $PNRecord->RecordCount();
  }
  else
  {
    echo "<br>�� pnrecord ʧ��";
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
  	echo "<br>�������ݱ����ԭ��<br>" . $e->getMessage();
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