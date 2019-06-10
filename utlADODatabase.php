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

class ConnectModeEnum
{
  const __default = self::adModeUnknown;
  const adModeUnknown        = 0;           // Ĭ��ֵ��Ȩ����δ���û���ȷ��Ȩ�ޡ�
  const adModeRead           = 1;           // ֻ��Ȩ��
  const adModeWrite          = 2;           // ֻдȨ��
  
  // ��/дȨ��
  const adModeReadWrite      = 3;
  const adModeShareDenyRead  = 4;           // ��ֹ�������Զ�Ȩ�޴�����
  const adModeShareDenyWrite = 8;           // ��ֹ��������дȨ�޴����ӡ�
  
  const adModeShareExclusive = 12;          // ��ֹ�����˴����ӡ�
  const adModeShareDenyNone  = 16;          // �������������κ�Ȩ�޴����ӡ����ܾ������˵Ķ���д���ʡ�
  // �� adModeShareDenyNone, adModeShareDenyWrite �� adModeShareDenyRead һ��ʹ�ã�
  // �Ե�ǰ Record �������Ӽ�¼����Ȩ�ޡ�
  const adModeRecursive      = 0x40000;//4194304;
}

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
  	$adUseClient = 3;
  	$this->ConnectString = $connstr;
  	$this->m_tConObj->CursorLocation = $adUseClient; 
  	$this->m_tConObj->Mode = ConnectModeEnum::adModeReadWrite;
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
	$tablenameArray = array();
	$bHasSysObj = false;
	$adSchemaTables = 20;
	$rs = $this->m_tConObj->OpenSchema( $adSchemaTables );
  while( !$rs->Eof )
  {
  	// ȡ��������
    $flddata = addslashes( $rs->Fields( "TABLE_NAME" )->Value );
    // ����������
    $tablenameArray[] = $flddata;
      
    if ( $flddata == "sysobjects" )
    {
    	// ���־���ϵͳ����׼�����´Ӹñ��л�ȡ������
      $bHasSysObj = true;
      break;
    }
   
   $rs->MoveNext();
  }
  
  if ( $bHasSysObj )
  {
  	$SysObj = new Com( "ADODB.Recordset" ); //ʵ����һ��Recordset����;
  	$CommandText = 
  	  "SELECT name AS TABLE_NAME FROM sysobjects " . 
      "WHERE ( type = 'U' ) OR ( type = 'V' ) ORDER BY name";
    
    $sysdata = array(); 
    try
    {
      $SysObj->Open( $CommandText, $this->m_tConObj );
      while( !$rs->Eof )
      {  	      
        $flddata = addslashes( $rs->Fields( "TABLE_NAME" )->Value );

        $sysdata[] = $flddata;
        $rs->MoveNext();
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
class CursorType                            // �α�����
{
  const __default = self::adOpenForwardOnly;
  // ǰ���α꣬Ϊȱʡ�α꣬�ṩ�����������ܡ�������recordset��
  // �Ӷ���β˳��ȡ�����н��������֧����������ֻ�����ڽ���䵥���ƶ���
  const adOpenForwardOnly    = 0;
  
  // ��̬�α꣬��ӳ��һ�δ��α�ʱ�������ݵ�״̬���α��޷������ײ�
  // ���е��������Ƿ���¹���ɾ������������µ����ݡ�������ֻ��ǰ��
  // ���α겻ͬ����̬�α�����ڽ����ǰ�������
  const adOpenKeyset         = 1;
  
  // �����������α꣬���Բ�ѯ���еײ������е�ĳЩ�仯��������ȫ����
  // ���ر��ǿ���׼ȷ��ӳ�����Ƿ���¹����������ܲ��������û��Ƿ���
  // ɾ���������У�ɾ��������������recordset�л����¿ն�������������
  // ���α�֧���ڽ����ǰ�������
  const adOpenDynamic        = 2;
  
  // ��̬�α꣬����ḻ���α����͡��α��ʱ���Բ�ѯ�����û��Ա���κ�
  // �Ķ�������֧�ֹ�����
  const adOpenStatic         = 3;
}
//---------------------------------------------------------
class LockType                              // ��������
{
  const __default = self::adLockReadOnly;
  // ȱʡ���������ͣ�ֻ����ʽ�����������û�ͬʱ��ȡͬ�������ݣ�
  // �����ܸı����ݡ�
  const adLockReadOnly        = 1;
  
  // �Ա���������ʽ�����ݶ��󡣸÷�ʽ�ٶ�����༭��¼ʱ������
  // ���û��������ݡ���ʱһ���㿪ʼ�༭��¼�������û��Ͳ��ܷ���
  // �����ݡ�
  const adLockPessimistic     = 2;
  
  // ���ֹ�������ʽ�����ݶ��󡣸÷�ʽ�ٶ�����༭��¼ʱ������
  // �����û��������ݡ�����ɸı�֮ǰ�������û����ܷ��ʸü�¼��
  const adLockOptimistic      = 3;
  
  // ִ�ж������������ʱʹ���������͡�
  const adLockBatchOptimistic = 4;
}
//---------------------------------------------------------
class CommandType                              // Options����
{
	const __default = self::cmdUnknown;
	// ȱʡֵ����ָ���ַ��������ݡ�
  const cmdUnknown            = 0;
  
  // ��ִ�е��ַ�������һ�������ı���
  const cmdText               = 1;
  
  // ��ִ�е��ַ�������һ���������
  const cmdTable              = 2;
  
  // ��ִ�е��ַ�������һ���洢�������� 
  const cmdStoredProc         = 3;
  const cmdFile               = 4;
  const cmdTableDirect        = 5;
}
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

private $CursorType;                        // �α�����
private $LockType;                          // ��������
private $CommandType;                       // Options����

private $m_bInEdit;                         // �Ƿ����޸�״̬
private $Eof;                               // �Ƿ����ݼ���ĩβ
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
	
	$this->CursorType = CursorType::adOpenForwardOnly;
	$this->LockType = LockType::adLockOptimistic;
	$this->CommandType = CommandType::cmdText;
	
	$this->m_bInEdit = false;
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
	$this->m_tTable->Open( $this->CommandText, $this->m_pCon->m_tConObj, 
	  $this->CursorType, $this->LockType, $this->CommandType );
	  
	$this->Active = true;
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
/// @brief
///         ȡ��Ա����ħ���������ú������Ǻã����Ǳ���ĳ�Ա
///         �������ò���������ԭ����
/// @param
///         ��
/// @return
///         ������Ӧ������ֵ
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
///         ����һ���µļ�¼
/// @param
///         ��
/// @return
///         ��
public function Append()
{
	$this->m_tTable->AddNew();
}
//---------------------------------------------------------
/// @brief
///         �����¼�޸�״̬���ڴ�״̬���޸ļ�¼���ݲſ��Ա���
/// @param
///         ��
/// @return
///         ��
public function Edit()
{
	$this->m_bInEdit = true;
}
//---------------------------------------------------------
/// @brief
///         �����ֶ���ȡһ���ֶ�
/// @param
///         [in]  $FldName      �ֶ�����
/// @return
///         ����һ��Field���͵Ķ���
public function FieldByName( $FldName )
{
	return $this->m_tTable->Fields( $FldName );
}
//---------------------------------------------------------
/// @brief
///         ��������ȡһ���ֶ�
/// @param
///         [in]  $FldIndex     �ֶ��±�
/// @return
///         ����һ��Field���͵Ķ���
public function FieldByIndex( $FldIndex )
{
	return $this->m_tTable->Fields( $FldIndex );
}
//---------------------------------------------------------
/// @brief
///         ���ݲ����������Ҽ�¼
/// @param
///         [in]  $sCondition   ��������
/// @return
///         �ҵ��������棬���򷵻ؼ�
private function InnerFind( $sCondition )
{
	// �ȼ����ҵ���
	$bResult = true;
	$this->m_tTable->Find( $sCondition );
	if ( $this->m_tTable->Eof )
	{
		// ���˼�¼����ĩβ�����ؼ٣���ʾ�Ҳ���
		$bResult = false;
	}
	return $bResult;
}
//---------------------------------------------------------
/// @brief
///         ���ݲ����������Ҽ�¼
/// @param
///         [in]  $sConditionArray     ��������������
/// @return
///         �ҵ��������棬���򷵻ؼ�
public function Find( $sConditionArray )
{
	$bResult = false;
	// �ȵ���һ����¼��˵
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
		  		// ��������һ���������ҵ��ˣ�˵�����������������ҵ�
		  	  $bResult = true;
		  	  break;
		  	}
		  	else if ( $currRecNo != $this->RecNo() )
		  	{
		  		// ��ǰ��¼�ŷ�����������˳�ѭ������Ҫ���´ӵ�һ����������
		  		break;
		  	}
		  }
		  else
		  {
		  	// �Ҳ������˳�ѭ������ֹ����
		  	break;
		  }
	  }
  }
  return $bResult;
}
//---------------------------------------------------------
/// @brief
///         ɾ����ǰ��¼
/// @param
///         ��
/// @return
///         ��
public function Delete()
{
	$this->m_tTable->Delete();
}
//---------------------------------------------------------
/// @brief
///         ��λ����һ����¼
/// @param
///         ��
/// @return
///         ��
public function First()
{
	$this->m_tTable->MoveFirst();
}
//---------------------------------------------------------
/// @brief
///         ��λ�����һ����¼
/// @param
///         ��
/// @return
///         ��
public function Last()
{
	$this->m_tTable->MoveLast();
}
//---------------------------------------------------------
/// @brief
///         ��λ����һ����¼
/// @param
///         ��
/// @return
///         ��
public function Next()
{
	$this->m_tTable->MoveNext();
}
//---------------------------------------------------------
/// @brief
///         ȡ��ǰ�ļ�¼��
/// @param
///         ��
/// @return
///         ���ص�ǰ�ļ�¼��
public function RecNo()
{
  return $this->m_tTable->AbsolutePosition;
}
//---------------------------------------------------------
/// @brief
///         ��λ��ǰһ����¼
/// @param
///         ��
/// @return
///         ��
public function Prior()
{
	$this->m_tTable->MovePrevious();
}
//---------------------------------------------------------
/// @brief
///         ����ǰ��¼���޸ı��浽���ݿ���
/// @param
///         ��
/// @return
///         ��
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

  // ����ΪһЩ�򵥵Ĳ��Դ���
  
  $connstr = "provider=sqloledb.1;;Password=PN123456;Persist Security Info=True;" .
             "User ID=PN;Initial Catalog=PNMNG;Data Source=127.0.0.1";
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
  	echo "��¼�� = " . $PNRecord->RecordCount() . "<br>";
  	

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
    echo "<br>�� pnrecord ʧ��";
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
  	  echo "<br>�������ݱ����ԭ��<br>" . $e->getMessage();
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
  	echo "<br>��������ʧ�ܣ�ԭ��<br>" . $e->getMessage();
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
		echo "�����ݱ����ԭ��<br>" . $e->getMessage();
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
	  echo "�������ݳ���ԭ��<br>" . $e->getMessage();
	}
		
	// ���������Ѿ��õ���֤����ʱע��
	//try
	//{
	//	$ra->Append();
	//	$ra->Edit();
  //  $ra->FieldByName( "TypeName" )->Value = "123";
  //  $ra->Post();
  //}
  //catch( Exception $e )
	//{
	//echo "�������ݱ����ԭ��<br>" . $e->getMessage();
	//}
?>