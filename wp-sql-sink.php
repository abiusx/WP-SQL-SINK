<?php
/**
 * this script holds the sink handlers. 
 * 
 */


global $inputs;
$inputs=array();
foreach ($_GET as $k=>$v)
	$inputs[0][$k]=$v;
foreach ($_GET as $k=>$v)
	$inputs[1][$k]=$v;
global $file;
$file="/tmp/logs/sql";
function store_data()
{
	global $file;
	$list=glob($file."*");
	sort($list);

	$t=preg_match('/(\d+)\.{1,5}.*$/', end($list), $matches);
	if ($t)
		$number=$matches[1];
	else
		$number="0";
	$number=str_pad($number+1, 3,"0",STR_PAD_LEFT);
	rename($file.".txt", $file.$number.".txt");
}
register_shutdown_function("store_data");
function dump_log($in,$out,$GET=true)
{
	global $file;
	$f=$file.".txt";
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
	global $inputs;
	foreach ($inputs[0] as $input) //get
		dump_log($input,$query);
	foreach ($inputs[1] as $input) //post
		dump_log($input,$query,false);
	if (count($inputs[0])==0 and count($inputs[1])==0)
		dump_log("",$query);
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
function mysqli_query_($link=null,$query=null);
{
	$args=func_get_args();
	sink_handler($query);
	return call_user_func_array("mysql_query", $args);
}
function mysqli_real_query_($link=null,$query=null);
{
	$args=func_get_args();
	sink_handler($query);
	return call_user_func_array("mysql_query", $args);
}
function mysqli_multi_query_($link=null,$query=null);
{
	$args=func_get_args();
	sink_handler($query);
	return call_user_func_array("mysql_query", $args);
}
function mysql_db_query_($dbname=null,$query=null);
{
	$args=func_get_args();
	sink_handler($query);
	return call_user_func_array("mysql_query", $args);
}
