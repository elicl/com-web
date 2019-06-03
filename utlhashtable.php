<?php
/**********************************************************
/// @file   utlHashtable.php
/// @brief  PHP实现的哈希表工具类
/// @date   2019-06-03
/// @author elicl <elicl@163.com>
/// @par    说明
    本模块采用PHP实现一个哈希表工具类，在需要使用到哈希表时，
创建一个CHashTable对象即可。
    该类根据键值计算一个哈希索引，并登记到哈希数组，可随时根据
该键值定位相应的哈希数据对象，并取出其相应的数据值
**********************************************************/	
class CHashNode
{
	public $m_pnextnode;                 // 下一个对象的指针
	public $m_sKey;                      // 键值
	public $m_svalue;                    // 数据值

  public function __construct( $key, $value, $tExitNode = NULL )
	{
		$this->m_sKey = $key;
		$this->m_svalue = $value;
		$this->m_pnextnode = $tExitNode;
	}
	public function __destruct()
	{
	}
}
//---------------------------------------------------------	
class CHashTable
{
  private $m_taData;
  private $m_iSize;
//---------------------------------------------------------
	public function __construct( $isize )
	{
		$this->m_iSize = $isize;
		$this->m_taData = new SplFixedArray( $isize );
	}
//---------------------------------------------------------	
	 public function __destruct()
	 {
   }
//---------------------------------------------------------
  private function HashFunc( $key )
  {
    $strlen  = strlen($key);
    $hashval = 0;
 
    for( $i = 0; $i < $strlen; $i++ )
    {
    	$hashval += ord( $key[ $i ] );
    }
       
    return $hashval % $this->m_iSize;
  }
//---------------------------------------------------------
  public function insert( $key, $val )
  {
  	$index = $this->HashFunc( $key );
  	if ( isset( $this->m_taData[ $index ] ) )
  	{
  		$newNode = new CHashNode( $key, $val, $this->m_taData[ $index ] );
  	}
  	else
  	{
  		$newNode = new CHashNode( $key, $val );
  	}
  	$this->m_taData[ $index ] = $newNode;
  }
//---------------------------------------------------------
  public function find( $key )
  {
  	$index   = $this->HashFunc( $key );
  	$current = $this->m_taData[ $index ];
  	
  	while( isset( $current ) )
  	{
  		if ( $current->m_sKey == $key )
  		{
  			return $current->m_svalue;
  		}
  		$current = $current->m_pnextnode;
  	}
  	return NULL;
  }
//---------------------------------------------------------
}
/*
  以下为本模块的测试代码
  
  $Obj = new CHashTable( 20 );
  
  $Obj->insert( 'key1', 'key1' );
  $Obj->insert( 'key2', 'value2' );
  $Obj->insert( 'key12', 'value12' );
  $Obj->insert( 'key21', 'value21' );
 
  echo $Obj->find( 'key1' ) . "<br>";
  echo $Obj->find( 'key2' ) . "<br>";
  echo $Obj->find( 'key12' ) . "<br>";
  echo $Obj->find( 'key21' ) . "<br>";
 */
?>
