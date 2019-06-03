<?php
/**********************************************************
/// @file   utlHashtable.php
/// @brief  PHPʵ�ֵĹ�ϣ������
/// @date   2019-06-03
/// @author elicl <elicl@163.com>
/// @par    ˵��
    ��ģ�����PHPʵ��һ����ϣ�����࣬����Ҫʹ�õ���ϣ��ʱ��
����һ��CHashTable���󼴿ɡ�
    ������ݼ�ֵ����һ����ϣ���������Ǽǵ���ϣ���飬����ʱ����
�ü�ֵ��λ��Ӧ�Ĺ�ϣ���ݶ��󣬲�ȡ������Ӧ������ֵ
**********************************************************/	
class CHashNode
{
	public $m_pnextnode;                 // ��һ�������ָ��
	public $m_sKey;                      // ��ֵ
	public $m_svalue;                    // ����ֵ

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
  ����Ϊ��ģ��Ĳ��Դ���
  
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
