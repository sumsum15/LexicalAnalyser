<?php

/*
Lexical analyser

Sharlaieva Arina
*/


/*
Function checks the number and values of command line argumets. Prints --help if needed.
*/

function cmd_arguments($arg_cnt, $arguments)
{
    if ($arg_cnt == 2)
    {
        if (strcmp($arguments[1], "--help") == 0)
        {
            echo "Program will read instructions in IPPcode20 from stdin and print it's XML representstion to stdout.\n";
		exit(0);
        }
        else
        {
            exit(10);
        }
    }
    if($arg_cnt > 2)       
        exit(10);
        
}


// Argument type processing

function get_variable_type($element, $instr, $c)
{
    $position  = strripos($element, "@");
    $instr = strtoupper($instr);
    if ($position !== false)
    {
        // type "label"
        if(strcmp($instr, "CALL") === 0 || strcmp($instr, "JUMP") === 0 || strcmp($instr, "LABEL") === 0)
            $type = "label";
        if((strcmp($instr, "JUMPIFEQ") === 0 || strcmp($instr, "JUMPIFNEQ") === 0) && $c==1)
            $type = "label";
        $array = explode("@", $element);
        // type "var"
        if (strcasecmp($array[0], "GF") === 0 or strcasecmp($array[0], "LF") === 0 or strcasecmp($array[0], "TF") === 0)
            $type = "var"; 
        else
            $type = strtolower($array[0]);
    }
    else
    {   // if literal does not contain "@"

        if(strcasecmp($element, "int") === 0 or strcasecmp($element, "bool") === 0 or strcasecmp($element, "string") === 0)
        {
            
            if(strcasecmp($instr, "LABEL") === 0 || strcasecmp($instr, "CALL") === 0 or strcasecmp($instr, "JUMP") === 0)
                $type = "label";
            else if((strcasecmp($instr, "JUMPIFEQ") === 0 || strcasecmp($instr, "JUMPIFNEQ") === 0) && $c == 1)
                $type = "label";
            else
                $type = "type";
        }
        else       
            $type = "label";
    }
    
    return $type; 
}

// Arguments processing

function get_element($string)
{
    $position  = strpos($string, "@");
    if ($position !== false) //Element has "@"
    {
        //  var and bool variables must be printed correctly
        $array = explode("@", $string,2);
        if (strcasecmp($array[0], "string") == 0)
            {
                return $array[1];
            }
        if (strcasecmp($array[0], "GF") === 0 or strcasecmp($array[0], "LF") === 0 or strcasecmp($array[0], "TF") === 0)
        {
            $array[0] = strtoupper($array[0]);
            $element = $array[0]."@".$array[1];
            return $element;
        }
        if(strcasecmp($array[0], "bool") === 0)
        {  
            if(strcasecmp($array[1], "false") == 0 or strcasecmp($array[1], "true") == 0)
                $array[1] = strtolower($array[1]);
            else    //unknown bool value (can be True or False only)
                exit(23);

        }
        $element =$array[1];
        return $element;
    }
    // Element doe not contain @
    else
    {
        $element = $string;
        return $element;
    }
    
}

/*
    Function checks if non-string literal is not empty
    t - argument type
    el - argument
*/

function check_type($t, $el)
{
    if(strcasecmp($t, 'string')!=0)
    {
        $e = get_element($el);
        if (strcasecmp($e, '') ==0)
            exit(23);
    }
}

/*
 Function checks instruction name and number of instruction's arguments
    array - instruction
*/

function check_arguments($array)
{   
    if(!empty($array))
    {
        $command = strtoupper($array[0]);
        $cnt = count($array) -2;

        $zero_argument = array(
            "CREATEFRAME", "PUSHFRAME", "POPFRAME", "RETURN", "BREAK"
        );
        $one_argument = array(
            "DEFVAR", "CALL", "PUSHS", "POPS", "WRITE", "LABEL", "JUMP", "EXIT", "DPRINT"
        );
        $two_arguments = array(
            "MOVE", "INT2CHAR", "READ", "STRLEN", "TYPE"
        );
        $three_arguments = array(
            "ADD", "SUB", "MULL", "IDIV", "LT", "GT", "EQ", "AND", "OR", "NOT", "STR2INT", "CONCAT", "GETCHAR", "SETCHAR", "JUMPIFEQ", "JUMPIFNEQ"
        );

        if(!empty($command))
        {    
            // Unknown instruction opcode
            if (!in_array($command, $zero_argument) and !in_array($command, $one_argument) and !in_array($command, $two_arguments) and !in_array($command, $three_arguments))
                exit(22);
            
        };
    };
    // Checks number of arguments
    switch ($cnt)
    {
        case 0:
            if (!in_array($command, $zero_argument))
                exit(23);
        break;
        
        case 1:
            if (!in_array($command, $one_argument))
                exit(23);
        break;
        
        case 2:
            if (!in_array($command, $two_arguments))
                exit(23);
        break;
        
        case 3:
        if (!in_array($command, $three_arguments))       
            exit(23);
        break;
    // can't be >3 arguments   
        default:
                exit(23);
        break;
    }
   
}

// Function cuts comments

function cut_comments($s)
{
    $position = strpos($s, "#");
    
    if ($position == 0){
        $s = trim($s);
        $s = substr($s, 0, $position);  
        }
    if($position != false)
    {
        $s = substr($s, 0, $position);
    }
    // Line has no comments
    return $s;
}


cmd_arguments($argc, $argv);    //Command line arguments processing

//  Header of XML file 

$program = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>'.'<program> </program>');
$program->addAttribute('language', 'IPPcode20');

$ln = 1;  // Instruction number

/* Checking header
Each file must begin whith "IPP20code" header
*/
try{
    $header  = fgets(STDIN);
}
catch(exception $e)
{
    exit(11);
}


while($header[0] === "#")
{
    $header  = fgets(STDIN);
}

$header = preg_split("/\s+/", $header);
if (strcmp($header[0],".IPPcode20") !== 0)
{
    exit(21);
}


while(FALSE !== ($string = fgets(STDIN)))
{
   // If line is empty
    if( strcasecmp($string, "") ==0 or strcasecmp($string, "\n") == 0)
        continue;

    // Delete whitespaces   
    $string = trim($string);
    $string = $string."\n";

    $str=strpos($string, "#");
    if($str != false)
    {
        $string = substr($string, 0, $str);
        $string = $string."\n";
    }

    // Cut comments or whitespaces at the beginning of the line

    $arr = preg_split("~\s+~", $string);
    if(strcmp($string[0], "#") == 0)
        continue;
    // if line starts whith whitespace  
    if(strcmp($arr[0], "") == 0 || strcmp($arr[0], "\n") == 0 || strcmp($arr[0], "\t") == 0)
        continue;
    
    if (count($arr) <2)
    {
        if($command != "\n" || $command != "" || $command != "\t")
        continue;

    }
    
    // Instructions' arguments checking
    check_arguments($arr);
    
    // Create XML tree

    $instruction[$ln] = $program->addChild('instruction');
    $instruction[$ln]->addAttribute('order', $ln);
    $instruction[$ln]->addAttribute('opcode', $arr[0]);

    for ($i =1; $i<count($arr) -1; $i++)
    {
        $argument[$i] = $instruction[$ln]->addChild("arg".$i, get_element($arr[$i]));
        $type = get_variable_type($arr[$i], $arr[0], $i);
        check_type($type, $arr[$i]);
        $argument[$i]->addAttribute('type', $type);
    }  
    $ln++;
}

    // Print XML tree

$dom = new DOMDocument("1.0");
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($program->asXML());
fclose(STDIN); 
try{
    $dom->save("php://stdout");
}
catch (exception $e) {
    exit(12);
}

exit(0);

?>
