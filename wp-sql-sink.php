<?php
/**
 * this script holds the sink handlers. 
 * 
 */


global $sqlsink_inputs;
$sqlsink_inputs=array();
foreach ($_GET as $k=>$v)
	$sqlsink_inputs[0][$k]=$v;
foreach ($_GET as $k=>$v)
	$sqlsink_inputs[1][$k]=$v;
global $sqlsink_file;
function sqlsink_get_filename($file)
{
	$list=glob($file."*");
	sort($list);

	$t=preg_match('/(\d+)\.{1,5}.*$/', end($list), $matches);
	if ($t)
		$number=$matches[1];
	else
		$number="0";
	$number=str_pad($number+1, 3,"0",STR_PAD_LEFT);
	return $file.$number.".txt";
}
$sqlsink_file=sqlsink_get_filename("/tmp/logs/sql");
function sqlsink_dump_log($in,$out,$GET=true)
{
	global $sqlsink_file;
	$f=$sqlsink_file.".txt";
	if ($GET)
		$GET="GET";
	else
		$GET="POST";
	$data="IN {$GET} 1 ".strlen($in)." ".$in.PHP_EOL;
	file_put_contents($f, $data,FILE_APPEND);
	$data="OUT sql 1 ".strlen($out)." ".$out.PHP_EOL;
	file_put_contents($f, $data,FILE_APPEND);
}
function sink_handler($query)
{
	global $sqlsink_inputs;
	foreach ($sqlsink_inputs[0] as $input) //get
		sqlsink_dump_log($input,$query);
	foreach ($sqlsink_inputs[1] as $input) //post
		sqlsink_dump_log($input,$query,false);
	if (count($sqlsink_inputs[0])==0 and count($sqlsink_inputs[1])==0)
		sqlsink_dump_log("",$query);
	//put your sink handling logic here
}


class PDO_ extends PDO
{

	function exec($query=null)
	{
		$args=func_get_args();
		sink_handler($query);
 		$reflector = new ReflectionClass(get_class($this));
        $parent = $reflector->getParentClass();
        $method = $parent->getMethod('exec');
        return $method->invokeArgs($this, $args);
	}
	function query($query=null)
	{
		$args=func_get_args();
		sink_handler($query);
 		$reflector = new ReflectionClass(get_class($this));
        $parent = $reflector->getParentClass();
        $method = $parent->getMethod('query');
        return $method->invokeArgs($this, $args);
      }
}
class mysqli_ extends mysqli
{
	function query($query=null)
	{
		$args=func_get_args();
		sink_handler($query);
 		$reflector = new ReflectionClass(get_class($this));
        $parent = $reflector->getParentClass();
        $method = $parent->getMethod('query');
        return $method->invokeArgs($this, $args);
	}
	function real_query($query=null)
	{
		$args=func_get_args();
		sink_handler($query);
 		$reflector = new ReflectionClass(get_class($this));
        $parent = $reflector->getParentClass();
        $method = $parent->getMethod('real_query');
        return $method->invokeArgs($this, $args);
	}
	function multi_query($query=null)
	{
		$args=func_get_args();
		sink_handler($query);
 		$reflector = new ReflectionClass(get_class($this));
        $parent = $reflector->getParentClass();
        $method = $parent->getMethod('multi_query');
        return $method->invokeArgs($this, $args);
	}
}

function mysql_query_($query=null)
{
	$args=func_get_args();
	sink_handler($query);
	return call_user_func_array("mysql_query", $args);
}
function mysqli_query_($link=null,$query=null)
{
	$args=func_get_args();
	sink_handler($query);
	return call_user_func_array("mysql_query", $args);
}
function mysqli_real_query_($link=null,$query=null)
{
	$args=func_get_args();
	sink_handler($query);
	return call_user_func_array("mysql_query", $args);
}
function mysqli_multi_query_($link=null,$query=null)
{
	$args=func_get_args();
	sink_handler($query);
	return call_user_func_array("mysql_query", $args);
}
function mysql_db_query_($dbname=null,$query=null)
{
	$args=func_get_args();
	sink_handler($query);
	return call_user_func_array("mysql_query", $args);
}
