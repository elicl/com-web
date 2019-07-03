<?php
/**********************************************************
/// @file   utlwordrw.php
/// @brief  PHP��WORD��д������
/// @date   2019-07-02
/// @author elicl <elicl@163.com>
/// @par    ˵��
    ��ģ�鴴��һ�����Com�����WORD�Ķ�д�����࣬�������ݿ�
WORD��д���ܡ�
    ��ģ��һ����6���࣬�ֱ���:
    1��WORD�����ࣻ
    2��WORD�ĵ��ࣻ
    3��ѡ�����ࣻ
    4�������ࣻ
    5������ࣻ
    6�������ࡣ
    ֵ��ע����ǣ�WORD��Com����������Range�࣬�������о�������
WORD�ĵ���Range�ͱ���е������Range������дϸ΢�Ĳ��ģ�����
���Ƕ���Range������WORD�ĵ��������һ���޴��Range�����Ǹ�Range
������ֱ�Ӹ�ֵ��ʹ������WORD�ĵ�����¸�ֵ�����ݣ����Ǳ������
��һ��ȡ�����������ܲ������������Range�ǿ���ֱ�Ӹ�ֵ�ģ�ʹ
��������������ݱ���¸�ֵ�����ݡ���Ȼ�������RangeҲ�������ĵ�
��Rangeһ�����в��������Դ���ȡ�����估���(�ӱ�)��Ȼ���ٽ�һ��
���������Ǽ�����ˣ��������Range������ȡ�����в���ʱ����������
���Ķ��������Щ����ģ������޷�ͨ����ֵΪ�յķ�ʽ����������
һ������ɾ����
    ������Щ���ǣ�������Ϊ�ĵ��������Range�ǲ�ͬ�ģ����������
����һ��Range�Ļ���Ӧ�ò���Ĵ����дҲ���Æ��£��������ȥ��
Range����м�㡣
**********************************************************/
// typedef enum WdProtectionType
const wdNoProtection = 0xFFFFFFFF;
const wdAllowOnlyRevisions = 0;
const wdAllowOnlyComments = 1;
const wdAllowOnlyFormFields = 2;

// typedef enum WdUnits
const wdCharacter = 1;
const wdWord = 2;
const wdSentence = 3;
const wdParagraph = 4;
const wdLine = 5;
const wdStory = 6;
const wdScreen = 7;
const wdSection = 8;
const wdColumn = 9;
const wdRow = 10;
const wdWindow = 11;
const wdCell = 12;
const wdCharacterFormatting = 13;
const wdParagraphFormatting = 14;
const wdTable = 15;
const wdItem = 16;

// typedef enum WdMovementType
const wdMove = 0;
const wdExtend = 1;

// typedef enum WdDeleteCells
const wdDeleteCellsShiftLeft = 0;
const wdDeleteCellsShiftUp = 1;
const wdDeleteCellsEntireRow = 2;
const wdDeleteCelssEntireColumn = 3;

//---------------------------------------------------------
// WORD�����࣬�������ڴ�һ��WORD���̣�WORD���̲��ܴ�WORD�ļ�
// ������Ҫ�Ƕ�WORD��Com�����һ���򵥷�װ����Ҫ�ṩ3�����ܣ�
// 1������WORD�ĵ��Ƿ���ʾ��2����WORD�ĵ���3���ر�WORD���̡�
class CWordApp
{
private $m_tWordApp;
//---------------------------------------------------------
public function __construct()
{
	$this->m_tWordApp = new Com( "Word.Application" );
	// Ĭ����ʾWORD��Ϊ�˷�ֹ�쳣ʱ������ر�WORD
	$this->Visible = true;
}
//---------------------------------------------------------
public function __destruct()
{
	$this->Quit();
}
//---------------------------------------------------------
/// @brief
///         ȡ��Ա����ħ������
/// @param
///         ��
/// @return
///         ������Ӧ������ֵ
public function __get( $name )
{
	//if ( $name == "Visible" )
  //else if ( $name == "Documents" )
	//else if ( $name == "Selection" )
	
	return $this->m_tWordApp->$name;
}
//---------------------------------------------------------
public function __set( $name, $value )
{	
	//if ( $name == "Visible" )
	
	$this->m_tWordApp->Visible = $value;
}
//---------------------------------------------------------
/// @brief
///         ��һ��WORD�ļ�
/// @param
///         [in]  $sFileName    ���򿪵�WORD�ļ���
/// @return
///         �����ļ�����
public function Open( $sFileName )
{
	$tResult = false;
	try
	{
		// ���ļ�����ȡ���ļ�����
		$tResult = $this->Documents->Open( $sFileName );
	}
	catch( Exception $e )
	{
		echo $e->getMessage();
	}
	// �����ļ�����
	return new CWordDoc( $this->m_tWordApp, $tResult );
}
//---------------------------------------------------------
public function Quit()
{
	$this->m_tWordApp->Quit();
}
//---------------------------------------------------------
}
//---------------------------------------------------------
//---------------------------------------------------------
//---------------------------------------------------------
// WORD�ĵ��࣬������WORD�������һ��WORD�ĵ��󴴽�������
// ��WORD�ĵ����ж�д��
// ������WORD�����ഴ�����Ƕ�Office��Com�����WORD�ĵ����
// һ���򵥷�װ����Ҫ�ṩ���½ӿڹ��ܣ�
// 1���ر�WORD�ĵ���
// 2�����ĵ����Ϊָ���ļ�����
// 3�����ĵ�ִ���ĵ�������
// 4��ȡ�����ĵ��Ķ�������
// 5��ȡ�����ĵ���ĳ�����䣻
// 6��ȡ�����ĵ��ı������
// 7��ȡ�����ĵ���ĳ�����
class CWordDoc
{
private $m_tWordApp;
private $m_tWordDoc;
//---------------------------------------------------------
/// @brief
///         ���캯������װWORD����
/// @param
///         [in]  $tWordDoc     word����
/// @return
///         ��
public function __construct( $tWordApp, $tWordDoc )
{
	$this->m_tWordApp = $tWordApp;
	$this->m_tWordDoc = $tWordDoc;
}
//---------------------------------------------------------
public function __destruct()
{
	try
	{
		@$this->Close();
	}
	catch( Exception $e )
	{
	}
}
//---------------------------------------------------------
/// @brief
///         �ر�WORD�ļ�
/// @param
///         ��
/// @return
///         ִ�н��
public function Close()
{
	return $this->m_tWordDoc->Close();
}
//---------------------------------------------------------
/// @brief
///         ���Ϊ��һ���ļ�
/// @param
///         [in]  $sFileName    ���Ϊ���ļ���
/// @return
///         ����ִ�н��
public function SaveAs( $sFileName )
{
	return $this->m_tWordDoc->SaveAs( $sFileName );
}
//---------------------------------------------------------
/// @brief
///         ִ���ĵ�����
/// @param
///         [in]  $WdProtectionType   ��������
///         [in]  $bNoReset      			�Ƿ�λ
///         [in]  $sPassword      		��������
/// @return
///         ����ִ�н��
public function Protect( $WdProtectionType, $bNoReset, $sPassword )
{
	return $this->m_tWordDoc->Protect( $WdProtectionType, $bNoReset, $sPassword );
}
//---------------------------------------------------------
/// @brief
///         ȡ��ǰ�ĵ��Ķ�����
/// @param
///         ��
/// @return
///         ���ض����� 
public function ParagraphCount()
{
	return $this->m_tWordDoc->Range()->Paragraphs->Count;
}
//---------------------------------------------------------
/// @brief
///         ȡһ���������
/// @param
///         [in]  $iIndex       ����ı�ţ���1��ʼ
/// @return
///         ���ض������ݶ���
public function GetParagraph( $iIndex )
{
	return new CParagraph( $this->m_tWordDoc->Range(), $iIndex );
}
//---------------------------------------------------------
/// @brief
///         ȡ��ǰ�ĵ��ı����
/// @param
///         ��
/// @return
///         ���ر���� 
public function TableCount()
{
	return $this->m_tWordDoc->Range()->Tables->Count;
}
//---------------------------------------------------------
/// @brief
///         ȡ�õ�ǰ�ĵ���ĳ�����
/// @param
///         [in]  $iIndex            	����ţ���1��ʼ
/// @return
///         ����ָ��������
public function GetTable( $iIndex )
{
	// ��ѡ���ӹ�����������Ϊ������ͨ����Ҫʹ��ѡ���ӽ���ѡ�����
	return new CTable( new CSelection( $this->m_tWordApp->Selection ),
	  $this->m_tWordDoc->Range()->Tables->Item( $iIndex ) );
}
}
//---------------------------------------------------------
//---------------------------------------------------------
//---------------------------------------------------------
// ѡ�����࣬������WORD�������һ�����Դ�������WORD���̶�WORD
// ���и��ֲ������ƵĹ����ࡣ
// �����WORD�������һ�����Դ������Ƕ�Office��Com�����ѡ����
// �������һ���򵥷�װ����Ҫ�ṩ���½ӿڹ��ܣ�
// 1����WORD�ĵ��а�һ��End����
// 2����WORD�ĵ��а�һ��Home����
// 3����WORD�ĵ������ҽ��ж�ѡ��
// 4����WORD�ĵ������½��ж�ѡ��
// 5����WORD�ĵ����м��У�
// 6����WORD�ĵ����и��ƣ�
// 7����WORD�ĵ�����ճ����
// 8�����·������У�
// 9�����Ϸ������У�
// 10������ϲ���
class CSelection
{
private $m_tSelection;
/// @brief
///         ���캯������װѡ��������
/// @param
///         [in]  $tSelection   WORDѡ��������
/// @return
///         ��
public function __construct( $tSelection )
{
	$this->m_tSelection = $tSelection;
}
//---------------------------------------------------------
/// @brief
///         ��һ��End��
/// @param
///         [in]  $WdUnits      ������ʽ
/// @return
///         ����ִ�н��
public function EndKey( $WdUnits )
{
	return $this->m_tSelection->EndKey( $WdUnits );
}
//---------------------------------------------------------
/// @brief
///         ��һ��Home��
/// @param
///         [in]  $WdUnits      ������ʽ
/// @return
///         ����ִ�н��
public function HomeKey( $WdUnits )
{
	return $this->m_tSelection->HomeKey( $WdUnits );
}
//---------------------------------------------------------
/// @brief
///         �����ƶ�
/// @param
///         [in]  $WdUnits          ������ʽ
///         [in]  $iCount           ����
///         [in]  $WdMovementType   �ƶ�����
/// @return
///         ����ִ�н��
public function MoveRight( $WdUnits, $iCount, $WdMovementType )
{
	return $this->m_tSelection->MoveRight( $WdUnits, $iCount, $WdMovementType );
}
//---------------------------------------------------------
/// @brief
///         �����ƶ�
/// @param
///         [in]  $WdUnits          ������ʽ
///         [in]  $iCount           ����
///         [in]  $WdMovementType   �ƶ�����
/// @return
///         ����ִ�н��
public function MoveDown( $WdUnits, $iCount, $WdMovementType )
{
	return $this->m_tSelection->MoveDown( $WdUnits, $iCount, $WdMovementType );
}
//---------------------------------------------------------
/// @brief
///         ����
/// @param
///         ��
/// @return
///         ����ִ�н��
public function Cut()
{
	return $this->m_tSelection->Cut();
}
//---------------------------------------------------------
/// @brief
///         ����
/// @param
///         ��
/// @return
///         ����ִ�н��
public function Copy()
{
	return $this->m_tSelection->Copy();
}
//---------------------------------------------------------
/// @brief
///         ճ��
/// @param
///         ��
/// @return
///         ����ִ�н��
public function Paste()
{
	return $this->m_tSelection->Paste();
}
//---------------------------------------------------------
/// @brief
///         ���·�������
/// @param
///         ��
/// @return
///         ����ִ�н��
public function InsertRowsBelow()
{
	return $this->m_tSelection->InsertRowsBelow();
}
//---------------------------------------------------------
/// @brief
///         ���Ϸ�������
/// @param
///         ��
/// @return
///         ����ִ�н�� 
public function InsertRowsAbove()
{
	return $this->m_tSelection->InsertRowsAbove();
}
//---------------------------------------------------------
/// @brief
///         ������ϲ�
/// @param
///         ��
/// @return
///         ����ִ�н��
public function Merge()
{
	return $this->m_tSelection->Cells->Merge();
}
}
//---------------------------------------------------------
//---------------------------------------------------------
//---------------------------------------------------------
// �����࣬������WORD�ĵ����һ�����䴴�����Ƕ�WORD�������
// ���ֲ����Ĺ����ࡣ
// ÿ�����ּ���һ���س�����һ�����䡣ÿ��WORD�ĵ�����������
// �ȵĶ��乹�ɡ�������һ��WORD�ĵ����Ĳ��ֵĻ���Ҫ�ء����
// ��ÿ�����������Ҳ�Ƕ��䡣վ��WORD�ĵ��ĽǶȣ�����WORD��
// ���Ķ������ǿ�ͳ�Ƽ���ġ���Щ�����ֿ���λ��ĳЩ����У�
// վ�ڱ��ĽǶȣ���Щ�����ֿ�������ĳ�������С�
// �����Ƕ�Office��Com�����ԭ��������һ���򵥷�װ����Ҫ��
// �����½ӿڹ��ܣ�
// 1��ȡ�������ݣ�
// 2���޸Ķ������ݡ�
class CParagraph
{
private $m_tRange;
private $m_iIndex;
private $m_tParagraph;
/// @brief
///         ���캯������װ�������
/// @param
///         [in]  $tDoc         WORD����
///         [in]  $iIndex       ������
/// @return
///         ��
public function __construct( $tRange, $iIndex )
{
	$this->m_tRange = $tRange;
	$this->m_iIndex = $iIndex;

	// ��ȡָ������
	$this->m_tParagraph = $this->m_tRange->Paragraphs->Item( $this->m_iIndex );
}
//---------------------------------------------------------
/// @brief
///         ȡ��Ա����ħ������
/// @param
///         ��
/// @return
///         ������Ӧ������ֵ
public function __get( $name )
{
	if ( $name == "Range" )
	{
		// ȡ�����ֵ
		// ʹ�ñ�����ת��Ϊ�ַ�����Ŀ����
		// �����������ʱ����ʧЧ���������������»�ȡ��
		// һ���������ʧЧ���򱾺���ֱ�ӷ��ص�variant����ҲʧЧ�ˣ��޷���ʹ��
		// ת��Ϊ�ַ�����Ӱ���ˣ�������ʹ���Ƿ�������ģ������ܱ�����ʧЧ����Լ��
		return sprintf( "%s", $this->m_tParagraph->$name );
	}
	return $this->m_tParagraph->$name;
}
//---------------------------------------------------------
public function __set( $name, $value )
{	
	if ( $name == "Range" )
	{
		// �����Range���ԣ������ǿ������⸳ֵ��ȡֵ�ģ���ϧ
		// ��֪���ǲ���WORD��Com��������⣬���Ǳ��ʲôԭ��
		// �Զ����Range��ֵʱ����WORD 2007�²��ԣ�����һ����
		// ���ᷳ������
		// ��WORD 2007��"��ʼ"�˵��£�����������һ����ʾ�����ǵĹ��߰�ť��
		// ����ð�ť��WORD����ʾÿ������Ķ�����(���磬�س���)��
		// ��ʹ��Range���ԶԶ�����и�ֵ�����ֵ���������ַ�����Ӧ�Ķ���
		// �ڲ���ʾ������ʱ���ã�һ����ʾ�����Ǿͺܳ�����һ����ֵı��
		// ����û�������ַ��򲻻ᡣ�����ֵıʼǷ�����"*"�ţ�������СһЩ
		// һ�������������ķ��ţ�ʹ��UE�鿴�������������ֽ�0x00��
		// ��������һ�������$this->m_tParagraph->Range = $value������Ч����
		// �����ǣ���������û���κβ��ֵ����������Ҳ�������˼ά�о�
		// ȡ��$this->m_tParagraph->Range��ֵ���浽INI�ļ���ʱ����û�з���
		// ��������Ϊ0���ֽڵģ���˿��Զ϶�����Com������ж�������ʱ������ת
		// ����ת�������в������������ֽ�0��
		// ���ڲ���$value��������⣬��˶���$value����������ʲô��Ϊ�ˡ�
		// ���������о����õ�һ��˼·����Ȼ��Ӣ�ģ�����в������˫0�ֽ�
		// ����������������ĩβ����˫0�ֽڡ���ô�Ƿ������$value�Ļ�������
		// ���Ӷ�һ�������أ����$value�������ģ���ô�����˫0�ֽ�һ������
		// ����Ŀ������ɶ��丳ֵ���ٽ��������������ɾ�����Ƿ������
		// ����������أ�
		// ����Ĵ�����ǰ��ո�˼·���еģ�ʵ��֤�����˷���ȫ���С�
		
		// ���������������ԭ���ǣ�PHP���õ�������PASCAL���ַ������ڴ���ǰ��
		// �����ַ��������򣬶�C/C++���õ�ASCIIZ����������ĳ�������У�����ʱ
		// ������СС��ʧ�󣬵���0���������������ˡ�
		
		// �Զ��丳ֵ
		// ȡ����ֵ�����ݱ���Ķ�����
		$iLines = substr_count( $value, "\n" );
		// �ӵ�ǰ��������������
		$this->m_tParagraph->$name = "\n\n";
		// ����ȡ��ǰ��
		$this->m_tParagraph = $this->m_tRange->Paragraphs->Item( $this->m_iIndex );
		// ��ֵ��ǰ�������ӻس�����
		$this->m_tParagraph->$name = $value . "\n";
		// ȡ�����Ǹ������У�Я���������ֽ�0�Ŀ��У�
		$ToDel = $this->m_tRange->Paragraphs->Item( $this->m_iIndex + 1 + $iLines );
		// ��ֵΪ����ɾ����
		$ToDel->$name = "";
  }
}
}
//---------------------------------------------------------
//---------------------------------------------------------
//---------------------------------------------------------
class CTable
{
private $m_tSelection;
private $m_tTable;
/// @brief
///         ���캯������װ������
/// @param
///         [in]  $tSelection   ѡ��������
///         [in]  $tTables      ������
/// @return
///         ��
public function __construct( $tSelection, $tTable )
{
	$this->m_tSelection = $tSelection;
	$this->m_tTable = $tTable;
}
//---------------------------------------------------------
/// @brief
///         ȡ��ǰ��������
/// @param
///         [in]  $iRow         �к�
///         [in]  $iCol         �к�
/// @return
///         ���ر���� 
public function Cell( $iRow, $iCol )
{
	return new CCell( $this->m_tSelection, $this->m_tTable->Cell( $iRow, $iCol ) );
}
//---------------------------------------------------------
/// @brief
///         ɾ����������
/// @param
///         [in]  $iRow           �к�
///         [in]  $iCol           �к�
///         [in]  $WdDeleteCells  ɾ���ķ�ʽ
/// @return
///         ����ִ�н��
public function Delete( $iRow, $iCol, $WdDeleteCells )
{
	return $this->Cell( $iRow, $iCol )->Delete( $WdDeleteCells );
}
//---------------------------------------------------------
/// @brief
///         ��������·�����һ������
/// @param
///         [in]  $iRow         �к�
///         [in]  $iCol         �к�
/// @return
///         ����ִ�н�� 
public function InsertRowsBelow( $iRow, $iCol )
{
	$this->Cell( $iRow, $iCol )->Select();
	return $this->m_tSelection->InsertRowsBelow();
}
//---------------------------------------------------------
/// @brief
///         �ڸ�������Ϸ�����һ������
/// @param
///         [in]  $iRow         �к�
///         [in]  $iCol         �к�
/// @return
///         ����ִ�н��
public function InsertRowsAbove( $iRow, $iCol )
{
	$this->Cell( $iRow, $iCol )->Select();
	return $this->m_tSelection->InsertRowsAbove();
}
//---------------------------------------------------------
/// @brief
///         �ϲ���������
/// @param
///         [in]  $iBeginRow    ��ʼ�к�
///         [in]  $iBeginCol    ��ʼ�к�
///         [in]  $iEndRow      �����к�
///         [in]  $iEndCol      �����к�
/// @return
///         ��
public function MergeCells( $iBeginRow, $iBeginCol, $iEndRow, $iEndCol )
{
	if ( ( $iBeginRow > $iEndRow ) || ( $iBeginCol > $iEndCol ) )
	{
		return;
	}
	
	$iRows = $iEndRow - $iBeginRow;
	$iCols = $iEndCol - $iBeginCol;
	$cell = $this->Cell( $iBeginRow, $iBeginCol );
	$cell->Select();
	
	if ( $iRows >= 1 )
	{
		$this->m_tSelection->MoveDown( wdLine, $iRows, wdExtend );
	}
	
	if ( $iCols >= 1 )
	{
		$this->m_tSelection->MoveRight( wdCharacter, $iCols, wdExtend );
	}
	
	if ( ( $iRows >= 1 ) || ( $iCols >= 1 ) )
	{
		$this->m_tSelection->Merge();
	}
}
//---------------------------------------------------------
/// @brief
///         ȡѡ��������
/// @param
///         ��
/// @return
///         ����ѡ�������� 
public function GetSelection()
{
	return $this->m_tSelection;
}
}
//---------------------------------------------------------
//---------------------------------------------------------
//---------------------------------------------------------
class CCell
{
private $m_tSelection;
private $m_tCell;
/// @brief
///         ���캯������װ��Ԫ��
/// @param
///         [in]  $tSelection   ѡ��������
///         [in]  $tCell        ��Ԫ�����
/// @return
///         ��
public function __construct( $tSelection, $tCell )
{
	$this->m_tSelection = $tSelection;
	$this->m_tCell = $tCell;
}
//---------------------------------------------------------
/// @brief
///         ȡ��Ա����ħ������
/// @param
///         ��
/// @return
///         ������Ӧ������ֵ
public function __get( $name )
{
	//if ( $name == "Range" )
	// ȡ�����ֵ
	return $this->m_tCell->$name;
}
//---------------------------------------------------------
public function __set( $name, $value )
{	
	if ( $name == "Range" )
	{
		// �Զ��丳ֵ
		$this->m_tCell->$name = $value;
		// ��������ѡ��
		$this->Select();
		
		// ��һ��End��,�ƶ���굽����ĩβ
		$this->m_tSelection->EndKey( wdLine );
		// ��ǰ�ƶ�һ���ַ�(�����Ǹ�Ϊ0�������ַ�)
		$this->m_tSelection->MoveRight( wdCharacter, -1, wdExtend );
		// ɾ��֮
		$this->m_tSelection->Cut();
  }
}
//---------------------------------------------------------
/// @brief
///         ɾ����Ԫ��
/// @param
///         [in]  $WdDeleteCells  ɾ���ķ�ʽ
/// @return
///         ����ִ�н�� 
public function Delete( $WdDeleteCells )
{
	return $this->m_tCell->Delete( $WdDeleteCells );
}
//---------------------------------------------------------
/// @brief
///         �Ը��������ȫѡ������һЩ���ϣ������������в���ѡ��
///         ��Ӹ�λ�ò����У�ͨ����Ҫ��ָ���������ѡ�в���
/// @param
///         ��
/// @return
///         ����ִ�н�� 
public function Select()
{
	return $this->m_tCell->Select();
}
//---------------------------------------------------------
/// @brief
///         ȡ��ǰ����ı����
/// @param
///         ��
/// @return
///         ���ر���� 
public function TableCount()
{
	return $this->m_tCell->Tables->Count;
}
//---------------------------------------------------------
/// @brief
///         ȡ�����ĳ�����
/// @param
///         [in]  $iIndex            	����ţ���1��ʼ
/// @return
///         ����ָ��������
public function GetTable( $iIndex )
{
	// ��ѡ���ӹ�����������Ϊ������ͨ����Ҫʹ��ѡ���ӽ���ѡ�����
	return new CTable( $this->m_tSelection,
	  $this->m_tCell->Tables->Item( $iIndex ) );
}
//---------------------------------------------------------
/// @brief
///         ȡ��ǰ����Ķ�����
/// @param
///         ��
/// @return
///         ���ض����� 
public function ParagraphCount()
{
	return $this->m_tCell->Range()->Paragraphs->Count;
}
//---------------------------------------------------------
/// @brief
///         ȡһ���������
/// @param
///         [in]  $iIndex       ����ı�ţ���1��ʼ
/// @return
///         ���ض������ݶ���
public function GetParagraph( $iIndex )
{
	return new CParagraph( $this->m_tCell->Range(), $iIndex );
}
}
//---------------------------------------------------------
?>
