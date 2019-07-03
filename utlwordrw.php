<?php
/**********************************************************
/// @file   utlwordrw.php
/// @brief  PHP的WORD读写工具类
/// @date   2019-07-02
/// @author elicl <elicl@163.com>
/// @par    说明
    本模块创建一组基于Com组件的WORD的读写工具类，用于数据库
WORD读写功能。
    本模块一共有6个类，分别是:
    1、WORD进程类；
    2、WORD文档类；
    3、选择器类；
    4、段落类；
    5、表格类；
    6、网格类。
    值得注意的是，WORD的Com组件本身具有Range类，不过经研究表明，
WORD文档的Range和表格中的网格的Range还是有写细微的差别的，尽管
它们都是Range，比如WORD文档本身就是一个巨大的Range，但是该Range
并不能直接赋值，使得整个WORD文档变成新赋值的内容，而是必须从中
进一步取出段落或表格才能操作，而网格的Range是可以直接赋值的，使
得整个网格的内容变成新赋值的内容。当然，网格的Range也可以像文档
的Range一样进行操作，可以从中取出段落及表格(子表)，然后再进一步
操作，但是即便如此，将网格的Range按段落取出进行操作时，还是与文
档的段落操作有些许差别的，例如无法通过赋值为空的方式将网格的最后
一个段落删除。
    基于这些考虑，可以认为文档和网格的Range是不同的，而且如果再
增加一层Range的话，应用层面的代码编写也会变得啰嗦，因此索性去掉
Range这个中间层。
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
// WORD进程类，该类用于打开一个WORD进程，WORD进程才能打开WORD文件
// 本类主要是对WORD的Com组件的一个简单封装，主要提供3个功能：
// 1、控制WORD文档是否显示；2、打开WORD文档；3、关闭WORD进程。
class CWordApp
{
private $m_tWordApp;
//---------------------------------------------------------
public function __construct()
{
	$this->m_tWordApp = new Com( "Word.Application" );
	// 默认显示WORD，为了防止异常时不方便关闭WORD
	$this->Visible = true;
}
//---------------------------------------------------------
public function __destruct()
{
	$this->Quit();
}
//---------------------------------------------------------
/// @brief
///         取成员变量魔术函数
/// @param
///         无
/// @return
///         返回相应变量的值
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
///         打开一个WORD文件
/// @param
///         [in]  $sFileName    待打开的WORD文件名
/// @return
///         返回文件对象
public function Open( $sFileName )
{
	$tResult = false;
	try
	{
		// 打开文件，并取得文件对象
		$tResult = $this->Documents->Open( $sFileName );
	}
	catch( Exception $e )
	{
		echo $e->getMessage();
	}
	// 返回文件对象
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
// WORD文档类，该类由WORD进程类打开一个WORD文档后创建，用于
// 对WORD文档进行读写。
// 本类由WORD进程类创建，是对Office的Com组件中WORD文档类的
// 一个简单封装，主要提供以下接口功能：
// 1、关闭WORD文档；
// 2、将文档另存为指定文件名；
// 3、对文档执行文档保护；
// 4、取整个文档的段落数；
// 5、取整个文档的某个段落；
// 6、取整个文档的表格数；
// 7、取整个文档的某个表格。
class CWordDoc
{
private $m_tWordApp;
private $m_tWordDoc;
//---------------------------------------------------------
/// @brief
///         构造函数，封装WORD对象
/// @param
///         [in]  $tWordDoc     word对象
/// @return
///         无
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
///         关闭WORD文件
/// @param
///         无
/// @return
///         执行结果
public function Close()
{
	return $this->m_tWordDoc->Close();
}
//---------------------------------------------------------
/// @brief
///         另存为到一个文件
/// @param
///         [in]  $sFileName    另存为的文件名
/// @return
///         返回执行结果
public function SaveAs( $sFileName )
{
	return $this->m_tWordDoc->SaveAs( $sFileName );
}
//---------------------------------------------------------
/// @brief
///         执行文档保护
/// @param
///         [in]  $WdProtectionType   保护类型
///         [in]  $bNoReset      			是否复位
///         [in]  $sPassword      		保护密码
/// @return
///         返回执行结果
public function Protect( $WdProtectionType, $bNoReset, $sPassword )
{
	return $this->m_tWordDoc->Protect( $WdProtectionType, $bNoReset, $sPassword );
}
//---------------------------------------------------------
/// @brief
///         取当前文档的段落数
/// @param
///         无
/// @return
///         返回段落数 
public function ParagraphCount()
{
	return $this->m_tWordDoc->Range()->Paragraphs->Count;
}
//---------------------------------------------------------
/// @brief
///         取一个段落对象
/// @param
///         [in]  $iIndex       段落的编号，从1开始
/// @return
///         返回段落数据对象
public function GetParagraph( $iIndex )
{
	return new CParagraph( $this->m_tWordDoc->Range(), $iIndex );
}
//---------------------------------------------------------
/// @brief
///         取当前文档的表格数
/// @param
///         无
/// @return
///         返回表格数 
public function TableCount()
{
	return $this->m_tWordDoc->Range()->Tables->Count;
}
//---------------------------------------------------------
/// @brief
///         取该当前文档的某个表格
/// @param
///         [in]  $iIndex            	表格编号，从1开始
/// @return
///         返回指定表格对象
public function GetTable( $iIndex )
{
	// 将选择子钩连起来，因为表格对象通常需要使用选择子进行选择控制
	return new CTable( new CSelection( $this->m_tWordApp->Selection ),
	  $this->m_tWordDoc->Range()->Tables->Item( $iIndex ) );
}
}
//---------------------------------------------------------
//---------------------------------------------------------
//---------------------------------------------------------
// 选择器类，该类由WORD进程类的一个属性创建，是WORD进程对WORD
// 进行各种操作控制的工具类。
// 本类从WORD进程类的一个属性创建，是对Office的Com组件中选择器
// 属性类的一个简单封装，主要提供以下接口功能：
// 1、在WORD文档中按一个End键；
// 2、在WORD文档中按一个Home键；
// 3、在WORD文档中向右进行多选；
// 4、在WORD文档中向下进行多选；
// 5、对WORD文档进行剪切；
// 6、对WORD文档进行复制；
// 7、对WORD文档进行粘贴；
// 8、在下方插入行；
// 9、在上方插入行；
// 10、网格合并。
class CSelection
{
private $m_tSelection;
/// @brief
///         构造函数，封装选择器对象
/// @param
///         [in]  $tSelection   WORD选择器对象
/// @return
///         无
public function __construct( $tSelection )
{
	$this->m_tSelection = $tSelection;
}
//---------------------------------------------------------
/// @brief
///         按一个End键
/// @param
///         [in]  $WdUnits      操作方式
/// @return
///         返回执行结果
public function EndKey( $WdUnits )
{
	return $this->m_tSelection->EndKey( $WdUnits );
}
//---------------------------------------------------------
/// @brief
///         按一个Home键
/// @param
///         [in]  $WdUnits      操作方式
/// @return
///         返回执行结果
public function HomeKey( $WdUnits )
{
	return $this->m_tSelection->HomeKey( $WdUnits );
}
//---------------------------------------------------------
/// @brief
///         向右移动
/// @param
///         [in]  $WdUnits          操作方式
///         [in]  $iCount           数量
///         [in]  $WdMovementType   移动类型
/// @return
///         返回执行结果
public function MoveRight( $WdUnits, $iCount, $WdMovementType )
{
	return $this->m_tSelection->MoveRight( $WdUnits, $iCount, $WdMovementType );
}
//---------------------------------------------------------
/// @brief
///         向下移动
/// @param
///         [in]  $WdUnits          操作方式
///         [in]  $iCount           数量
///         [in]  $WdMovementType   移动类型
/// @return
///         返回执行结果
public function MoveDown( $WdUnits, $iCount, $WdMovementType )
{
	return $this->m_tSelection->MoveDown( $WdUnits, $iCount, $WdMovementType );
}
//---------------------------------------------------------
/// @brief
///         剪切
/// @param
///         无
/// @return
///         返回执行结果
public function Cut()
{
	return $this->m_tSelection->Cut();
}
//---------------------------------------------------------
/// @brief
///         复制
/// @param
///         无
/// @return
///         返回执行结果
public function Copy()
{
	return $this->m_tSelection->Copy();
}
//---------------------------------------------------------
/// @brief
///         粘贴
/// @param
///         无
/// @return
///         返回执行结果
public function Paste()
{
	return $this->m_tSelection->Paste();
}
//---------------------------------------------------------
/// @brief
///         在下方插入行
/// @param
///         无
/// @return
///         返回执行结果
public function InsertRowsBelow()
{
	return $this->m_tSelection->InsertRowsBelow();
}
//---------------------------------------------------------
/// @brief
///         在上方插入行
/// @param
///         无
/// @return
///         返回执行结果 
public function InsertRowsAbove()
{
	return $this->m_tSelection->InsertRowsAbove();
}
//---------------------------------------------------------
/// @brief
///         在网格合并
/// @param
///         无
/// @return
///         返回执行结果
public function Merge()
{
	return $this->m_tSelection->Cells->Merge();
}
}
//---------------------------------------------------------
//---------------------------------------------------------
//---------------------------------------------------------
// 段落类，该类由WORD文档类的一个段落创建，是对WORD段落进行
// 各种操作的工具类。
// 每段文字加上一个回车就是一个段落。每个WORD文档都有数量不
// 等的段落构成。段落是一个WORD文档正文部分的基本要素。表格
// 中每个网格的内容也是段落。站在WORD文档的角度，整个WORD文
// 档的段落数是可统计计算的。这些段落又可能位于某些表格中，
// 站在表格的角度，这些段落又可能是在某个网格中。
// 本类是对Office的Com组件中原生段落类一个简单封装，主要提
// 供以下接口功能：
// 1、取段落内容；
// 2、修改段落内容。
class CParagraph
{
private $m_tRange;
private $m_iIndex;
private $m_tParagraph;
/// @brief
///         构造函数，封装段落对象
/// @param
///         [in]  $tDoc         WORD对象
///         [in]  $iIndex       段落编号
/// @return
///         无
public function __construct( $tRange, $iIndex )
{
	$this->m_tRange = $tRange;
	$this->m_iIndex = $iIndex;

	// 读取指定段落
	$this->m_tParagraph = $this->m_tRange->Paragraphs->Item( $this->m_iIndex );
}
//---------------------------------------------------------
/// @brief
///         取成员变量魔术函数
/// @param
///         无
/// @return
///         返回相应变量的值
public function __get( $name )
{
	if ( $name == "Range" )
	{
		// 取段落的值
		// 使用本方法转换为字符串的目的是
		// 本段落对象随时可能失效，例如段落对象重新获取。
		// 一旦段落对象失效，则本函数直接返回的variant对象也失效了，无法再使用
		// 转换为字符串则不影响了，后续的使用是方便自如的，不再受本对象失效的制约。
		return sprintf( "%s", $this->m_tParagraph->$name );
	}
	return $this->m_tParagraph->$name;
}
//---------------------------------------------------------
public function __set( $name, $value )
{	
	if ( $name == "Range" )
	{
		// 段落的Range属性，本来是可以随意赋值，取值的，可惜
		// 不知道是不是WORD的Com组件有问题，还是别的什么原因
		// 对段落的Range赋值时，在WORD 2007下测试，发现一个令
		// 人厌烦的现象：
		// 在WORD 2007的"开始"菜单下，工具条中有一个显示段落标记的工具按钮，
		// 点击该按钮后，WORD会显示每个段落的段落标记(例如，回车符)。
		// 当使用Range属性对段落进行赋值后，如果值包含中文字符，相应的段落
		// 在不显示段落标记时还好，一旦显示段落标记就很出现了一个奇怪的标记
		// 符。没有中文字符则不会。这个奇怪的笔记符很像"*"号，但又略小一些
		// 一共有两个这样的符号，使用UE查看，发现是两个字节0x00。
		// 可以想象，一定是语句$this->m_tParagraph->Range = $value产生的效果。
		// 糟糕的是，这个语句我没有任何插手的能力，而且采用逆向思维研究
		// 取出$this->m_tParagraph->Range的值保存到INI文件的时候，是没有发现
		// 有那两个为0的字节的，因此可以断定，是Com组件在判定有中文时进行了转
		// 换，转换过程中产生了那两个字节0。
		// 由于不是$value本身的问题，因此对于$value本身不可能有什么作为了。
		// 经过反复研究，得到一个思路：既然纯英文，或空行不会产生双0字节
		// 有中文又总是在最末尾才有双0字节。那么是否可以在$value的基础上再
		// 增加多一个换行呢？如果$value包含中文，那么多余的双0字节一定是在
		// 多余的空行里。完成段落赋值后，再将多余的垃圾空行删除，是否可以完
		// 美解决问题呢？
		// 下面的代码就是按照该思路进行的，实测证明，此法完全可行。
		
		// 初步分析，问题的原因是，PHP采用的是类似PASCAL的字符串，内存中前端
		// 保有字符串长度域，而C/C++采用的ASCIIZ串，估计在某个环节中，适配时
		// 出现了小小的失误，导致0结束符当作数据了。
		
		// 对段落赋值
		// 取待赋值的数据本身的段落数
		$iLines = substr_count( $value, "\n" );
		// 从当前行增加两个空行
		$this->m_tParagraph->$name = "\n\n";
		// 重新取当前行
		$this->m_tParagraph = $this->m_tRange->Paragraphs->Item( $this->m_iIndex );
		// 赋值当前行且增加回车换行
		$this->m_tParagraph->$name = $value . "\n";
		// 取下面那个垃圾行（携带了两个字节0的空行）
		$ToDel = $this->m_tRange->Paragraphs->Item( $this->m_iIndex + 1 + $iLines );
		// 赋值为空来删除它
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
///         构造函数，封装表格对象
/// @param
///         [in]  $tSelection   选择器对象
///         [in]  $tTables      表格对象
/// @return
///         无
public function __construct( $tSelection, $tTable )
{
	$this->m_tSelection = $tSelection;
	$this->m_tTable = $tTable;
}
//---------------------------------------------------------
/// @brief
///         取当前表格的网格
/// @param
///         [in]  $iRow         行号
///         [in]  $iCol         列号
/// @return
///         返回表格数 
public function Cell( $iRow, $iCol )
{
	return new CCell( $this->m_tSelection, $this->m_tTable->Cell( $iRow, $iCol ) );
}
//---------------------------------------------------------
/// @brief
///         删除表格的网格
/// @param
///         [in]  $iRow           行号
///         [in]  $iCol           列号
///         [in]  $WdDeleteCells  删除的方式
/// @return
///         返回执行结果
public function Delete( $iRow, $iCol, $WdDeleteCells )
{
	return $this->Cell( $iRow, $iCol )->Delete( $WdDeleteCells );
}
//---------------------------------------------------------
/// @brief
///         在网格的下方插入一个新行
/// @param
///         [in]  $iRow         行号
///         [in]  $iCol         列号
/// @return
///         返回执行结果 
public function InsertRowsBelow( $iRow, $iCol )
{
	$this->Cell( $iRow, $iCol )->Select();
	return $this->m_tSelection->InsertRowsBelow();
}
//---------------------------------------------------------
/// @brief
///         在该网格的上方插入一个新行
/// @param
///         [in]  $iRow         行号
///         [in]  $iCol         列号
/// @return
///         返回执行结果
public function InsertRowsAbove( $iRow, $iCol )
{
	$this->Cell( $iRow, $iCol )->Select();
	return $this->m_tSelection->InsertRowsAbove();
}
//---------------------------------------------------------
/// @brief
///         合并表格的网格
/// @param
///         [in]  $iBeginRow    起始行号
///         [in]  $iBeginCol    起始列号
///         [in]  $iEndRow      结束行号
///         [in]  $iEndCol      结束列号
/// @return
///         无
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
///         取选择器对象
/// @param
///         无
/// @return
///         返回选择器对象 
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
///         构造函数，封装表单元格
/// @param
///         [in]  $tSelection   选择器对象
///         [in]  $tCell        单元格对象
/// @return
///         无
public function __construct( $tSelection, $tCell )
{
	$this->m_tSelection = $tSelection;
	$this->m_tCell = $tCell;
}
//---------------------------------------------------------
/// @brief
///         取成员变量魔术函数
/// @param
///         无
/// @return
///         返回相应变量的值
public function __get( $name )
{
	//if ( $name == "Range" )
	// 取段落的值
	return $this->m_tCell->$name;
}
//---------------------------------------------------------
public function __set( $name, $value )
{	
	if ( $name == "Range" )
	{
		// 对段落赋值
		$this->m_tCell->$name = $value;
		// 整个网格选中
		$this->Select();
		
		// 按一下End键,移动光标到网关末尾
		$this->m_tSelection->EndKey( wdLine );
		// 向前移动一个字符(就是那个为0的垃圾字符)
		$this->m_tSelection->MoveRight( wdCharacter, -1, wdExtend );
		// 删除之
		$this->m_tSelection->Cut();
  }
}
//---------------------------------------------------------
/// @brief
///         删除单元格
/// @param
///         [in]  $WdDeleteCells  删除的方式
/// @return
///         返回执行结果 
public function Delete( $WdDeleteCells )
{
	return $this->m_tCell->Delete( $WdDeleteCells );
}
//---------------------------------------------------------
/// @brief
///         对该网格进行全选操作，一些场合，例如对网格进行部分选中
///         或从该位置插入行，通常需要对指定网格进行选中操作
/// @param
///         无
/// @return
///         返回执行结果 
public function Select()
{
	return $this->m_tCell->Select();
}
//---------------------------------------------------------
/// @brief
///         取当前网格的表格数
/// @param
///         无
/// @return
///         返回表格数 
public function TableCount()
{
	return $this->m_tCell->Tables->Count;
}
//---------------------------------------------------------
/// @brief
///         取网格的某个表格
/// @param
///         [in]  $iIndex            	表格编号，从1开始
/// @return
///         返回指定表格对象
public function GetTable( $iIndex )
{
	// 将选择子钩连起来，因为表格对象通常需要使用选择子进行选择控制
	return new CTable( $this->m_tSelection,
	  $this->m_tCell->Tables->Item( $iIndex ) );
}
//---------------------------------------------------------
/// @brief
///         取当前网格的段落数
/// @param
///         无
/// @return
///         返回段落数 
public function ParagraphCount()
{
	return $this->m_tCell->Range()->Paragraphs->Count;
}
//---------------------------------------------------------
/// @brief
///         取一个段落对象
/// @param
///         [in]  $iIndex       段落的编号，从1开始
/// @return
///         返回段落数据对象
public function GetParagraph( $iIndex )
{
	return new CParagraph( $this->m_tCell->Range(), $iIndex );
}
}
//---------------------------------------------------------
?>
