<?php

class AnnotationsParser {

	public static function parse($text) {
		$r = array();
		foreach(explode("\n", $text) as $line) {
			$line = trim($line);
	
			if (strncmp($line, '/**', 3) === 0)
				$line = substr($line, 3);

			if (substr($line,-2) == '*/')
				$line = substr($line, 0, -2);

			$line = preg_replace('/^\*+/', '', $line);

			$a = self::parseLine(trim($line));
			if (!$a)
				continue;
			
			$r[$a->getName()] = $a;
      	}
      	return $r;
	}

    /**
    * @return Annotation|false
    */
    private static function parseLine($line) {
    	//echo __METHOD__."($line)\n";
    	if (empty($line) || $line[0]!=='@')
    		return false;
    	$annotationName  = '';
    	$annotationAttrs = array();
    	
    	$state = State::START;
    	for ($i=0, $len=strlen($line); $i<$len && $state!=State::END; $i++) {
    		$c = $line[$i];
    		
    		switch ($state) {
    			case State::START:
    				$state = Lexer::isAnnotationMarker($c)
    					? State::NAME
    					: State::NOTANNOTATION;;
    				break;
    				
    			case State::NAME:
    				$p = self::getName($line, $len, $i);
    				if ($p==-1) {
    					$state = State::NOTANNOTATION;
    				} else {
    					$annotationName = substr($line, $i, $p);
    					$state = State::BEFOREBODY;
    					$i += $p-1;
    					//echo "\tNAME=$annotationName\n";
    				}
    				break;
    				
    			case State::NOTANNOTATION:
    				return false;
    			
    			case State::BEFOREBODY:
    				if (Lexer::isAttributesListStart($c)) {
    					$p = self::getBraketsBlock($line, $len, $i, $c);
    					if ($p!=-1) {
    						$block = substr($line, $i, $p);
    						$i+=$p-1;
    						//echo "\tAttr=$block\n";
    						$annotationAttrs = self::getAttributes($block);
    					} else
    						throw new ErrorException("Error parsing $annotationName annotation attributes");
    				}
    				
    				if (Lexer::isSpace($c))
    					continue;
    				
    				$state = State::END;
    				break;
    				
    			default:
    				throw new RuntimeException("Wrong state: $state");
    		}
    	}
    	return new Annotation($annotationName, $annotationAttrs);
    }
    
    /**
    * @return pos|-1
    */
    private static function getName($line, $len, $pos) {
    	$isFirst = false;
    	for ($i = $pos; $i < $len; $i++) {
    		$c = $line[$i];
    		if ( ($isFirst && Lexer::isAlpha($c)) || (!$isFirst && Lexer::isAlphanumeric($c)) ) {
    			$isFirst = false;
    			continue;
    		}
    		break;
    	}
    	return $isFirst ? -1 : $i-$pos;
    }
    
    private static function getBraketsBlock($line, $len, $pos, $brace) {
    	$stack = new SplStack();
    	$stack->push($brace);
    	for ($i=$pos+1; $i<$len && !$stack->isEmpty(); $i++) {
    		$c = $line[$i];
    		if ($c=='\\') {
    			$i++;
    			continue;
    		}
    		if (Lexer::isClosingFor($stack->top(), $c))
    			$stack->pop();
    		else if (Lexer::isPairingSymbol($c))
    			$stack->push($c);
    	}
    	return $stack->isEmpty() ? $i-$pos : -1;
    }
    
    
    
    private static function getAttributes($block) {
    	$result = array();
    	$state = State::START;
    	
    	for($i=0, $len=strlen($block); $i<$len; $i++) {
    		$c = $block[$i];
    		switch ($state) {
    			case State::START:
    				$state = Lexer::isAttributesListStart($c) ? State::ATTRNAME : State::ERROR;
    				$attrName = ''; 
    				$attrVal  = null;
    				break;	
    				
    			case State::ATTRNAME:
    				if (Lexer::isSpace($c)) continue;
    				$p = self::getName($block, $len, $i);
    				if ($p==-1)
    					$state = State::ERROR;
    				else {
						$attrName = substr($block, $i, $p);
						$i+=$p-1;
						$state = State::ATTRSEPARATOR;
    				}
    				break;
    				
    			case State::ATTRSEPARATOR:
    				if (Lexer::isSpace($c)) continue;
    				if(Lexer::isAttributeValueSeparator($c))
    					$state = State::ATTRVALUE;
    				break;
    				
    			case State::ATTRVALUE:
    				if (Lexer::isSpace($c)) continue;
    				if (Lexer::isPairingSymbol($c)) {
    					
    					$p = self::getBraketsBlock($block, $len, $i, $c);
    					if ($p==-1)
    						$state = State::ERROR;
    					else {
    						$val = substr($block, $i, $p);
    						$attrVal = $val[0] == "'" // json_decode do not parse strings like ' ... ' 
    							? substr($val,1,-1)
    							: json_decode($val);
    						$i += $p-1;
    						$state = State::ATTRDONE;
    					}
    				} else { 
    					// Dirty but effective way
    					$attrVal=strtok(substr($block, $i), ' ,)');
    					$i+=strlen($attrVal)-1;
    					$state = State::ATTRDONE;
    				}
    				break;
    				
    			case State::ATTRDONE:
    				if (!empty($attrName))
    					$result[$attrName] = $attrVal;
    				$attrName = '';
    				$attrVal = null;
    				if (Lexer::isComma($c))
    					$state = State::ATTRNAME;
    				if (Lexer::isAttributesListEnd($c))
    					return $result;
    				break;
    			
    			case State::ERROR:
    			default:
    				throw new RuntimeException("Wrong state: $state");
    			
    		}
    	}
    	return $result;
    }
    

}

class State {
	const START          = 1;
	const NOTANNOTATION  = 2;
	const NAME           = 3;
	const BEFOREBODY     = 4;
	const END            = 5;
	const ATTRNAME       = 6;
	const ATTRSEPARATOR  = 7;
	const ATTRVALUE      = 8;
	const ATTRDONE       = 9;
	const ERROR          = -1;
}

class Lexer {
	public static function isAnnotationMarker($c) { return $c == '@'; }
	
	public static function isAttributesListStart($c) { return $c == '('; }
	public static function isAttributesListEnd($c) { return $c == ')'; }
	
	public static function isAttributeValueSeparator($c) {return $c == '='; }
	
	public static function isPairingSymbol($c) {
		return in_array($c, array('{', '(', '[', '"', "'") );
	}
	
	public static function isComma($c) {return $c==',';}
	
	public static function isClosingFor($for, $c) {
		switch ($for) {
			case '{': return $c=='}';
			case '[': return $c==']';
			case '(': return $c==')';
			case '"': return $c=='"';
			case "'": return $c=="'";
		}
		return false;
	}
	
	public static function isAlpha($c) {
		return ctype_alpha($c);
	}
	
	public static function isNumeric($c) {
		return ctype_digit($c);
	}
	
	public static function isAlphanumeric($c) {
		return Lexer::isAlpha($c) || Lexer::isNumeric($c); 
	}
	
	public static function isSpace($c) {
		return ctype_space($c);
	}
}

