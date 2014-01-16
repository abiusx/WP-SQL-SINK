<?php
class SinkInstaller extends PHPParser_NodeVisitorAbstract
{
	static $sinks=array();
    static $functions=array(
        "mysql_query",
        "mysqli_query",
        "mysqli_real_query",
        "mysqli_multi_query",
        "mysql_db_query"
        );
    static $classes=array(
        "PDO",
        "mysqli"
        );
    static $changed=false;
    static $installMode=true; //whether in install mode or uninstall mode
    public function leaveNode(PHPParser_Node $node) {
        if ($node instanceof PHPParser_Node_Stmt_Class) # class definition, check for extends
        {
            if (isset($node->extends))
            {
                $baseClass=$node->extends;
                if (!self::$installMode)
                    $baseClass=substr($baseClass,0,-1); //remove the trailing _
                if (in_array($baseClass, self::$classes))
                {
                    self::$changed=true;   
                    $node->extends=$baseClass.(self::$installMode?"_":"");
                }
            }
        }
        elseif ($node instanceof PHPParser_Node_Expr_FuncCall) # function call
        {
            if (isset($node->name) && isset($node->name->parts))
            {
                $functionName=$node->name->parts[0];
                if (!self::$installMode)
                    $functionName=substr($functionName,0,-1); //remove the trailing _
                if (in_array($functionName, self::$functions))
                {
                    self::$changed=true;   
                    $node->name->parts[0]=$functionName.(self::$installMode?"_":"");
                }
            }
        }
    }

    static function process($wpdir)
    {
        $files=(getAllPhpFiles($wpdir));

        $parser = new PHPParser_Parser(new PHPParser_Lexer);    
        $traverser     = new PHPParser_NodeTraverser;
        $prettyPrinter = new PHPParser_PrettyPrinter_Default;

        $traverser->addVisitor(new SinkInstaller);
        $n=0;
        $changes=array();
        foreach ($files as $file)
        {
            $n++;
            if (($n)%80==0) 
                echo PHP_EOL;
            else
                echo ".";
            $syntax_tree = $parser->parse(file_get_contents($file));
            self::$changed=false;
            $filtered = $traverser->traverse($syntax_tree);
            if (self::$changed)
            {
                $newCode = '<?php ' . $prettyPrinter->prettyPrint($filtered);
                file_put_contents($file, $newCode);   
                $changes[]=$file;
            }
        }
        echo PHP_EOL;
        return $changes;
    }
}
