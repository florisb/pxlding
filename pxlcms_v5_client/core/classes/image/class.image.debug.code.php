<?php
/* include classfile */
  $sClassName = 'clsImage';
	$sClassFilename = 'class.image.php5.php';
  $aClassFile = file($sClassFilename);
?>
<html>
<head>
<title>PHP CLASSES - show class</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
<!--
body {
	font-family: "Courier New", Courier, mono;
	font-size: 12px;
	color: #333333;
}
.codegreen {
	color: #006600;
}
.codeblue {
	color: #000066;
}
.codered {
	color: #660000;
}
.codeblack {
	font-weight: bold;
	color: #000000;
}
.comment {
	color: #FF9933;
}
-->
</style>
<style type="text/css">
<!--
.debugline {
	background-color: #9999CC;
	font-weight: bold;
	color: #FFFFFF;	
}
-->
</style>
</head>
<body>
<h2>PHP CLASS <?php print $sClassName; ?></h2>
<?php
  foreach ($aClassFile as $iLineNumber => $sLine) {
	  $sLine = htmlspecialchars($sLine);
		
		if (isset($_GET["line"]) && ($_GET["line"] > 0) && ($_GET["line"] == $iLineNumber)) {
		  $sLine = '<span class="debugline">' . $sLine . '<span class="codered">DEBUG BREAKPOINT</span> an error occured on this line during the execution of the script</span>';
		}
		
		if ((strpos($sLine, 'function ') !== false) && (strpos($sLine, '/* ') === false)) {
		  $sLine = '<span class="codeblack">' . $sLine . '</span>';
		  $sLine = str_replace('function ','<span class="codered">function</span> ',$sLine);			
		}	
		if ((strpos($sLine, 'private ') !== false) && (strpos($sLine, '/* ') === false)) {
		  $sLine = str_replace('private ','<span class="codeblue">private</span> ',$sLine);			
		}
		if ((strpos($sLine, 'protected ') !== false) && (strpos($sLine, '/* ') === false)) {
		  $sLine = str_replace('protected ','<span class="codeblue">protected</span> ',$sLine);			
		}		
		if ((strpos($sLine, 'public ') !== false) && (strpos($sLine, '/* ') === false)) {
		  $sLine = str_replace('public ','<span class="codeblue">public</span> ',$sLine);			
		}
		if (strpos($sLine, 'class ' . $sClassName) !== false) {
		  $sLine = '<span class="codeblack">' . $sLine . '</span>';		  
		  $sLine = str_replace('class ','<span class="codegreen">class</span> ',$sLine);
		  $sLine = str_replace('implements ','<span class="codegreen">implements</span> ',$sLine);											
		}
		if (strpos($sLine, 'const ') !== false) {
		  $sLine = str_replace('const ','<span class="codered">const</span> ',$sLine);			
		}	
		if (strpos($sLine, '/* ') !== false) {
		  $sLine = '<span class="comment">' . $sLine . '</span>';			
		}	
		
		if (strpos($sLine, 'array()') !== false) {
		  $sLine = str_replace('array()','<span class="codeblue">array()</span>',$sLine);						
		}	
		
		if (strpos($sLine, '$this-&gt;') !== false) {
		  $sLine = str_replace('$this-&gt;','<span class="codegreen">$this-&gt;</span>',$sLine);						
		}	
		
		if ((strpos($sLine, 'include(') !== false) || (strpos($sLine, 'include (') !== false)) {
		  $sLine = str_replace('include','<span class="codered"><b>include</b></span>',$sLine);						
		}		
		
		if ((strpos($sLine, 'unset(') !== false) || (strpos($sLine, 'unset (') !== false)) {
		  $sLine = str_replace('unset','<span class="codeblue">unset</span>',$sLine);						
		}
		
		if ((strpos($sLine, 'if(') !== false) || (strpos($sLine, 'if (') !== false)) {
		  $sLine = str_replace('if','<span class="codeblue">if</span>',$sLine);						
		}
		
		if ((strpos($sLine, 'else{') !== false) || (strpos($sLine, 'else {') !== false)) {
		  $sLine = str_replace('else','<span class="codeblue">else</span>',$sLine);						
		}	
		
		if (strpos($sLine, 'exit;') !== false) {
		  $sLine = str_replace('exit','<span class="codeblue">exit</span>',$sLine);						
		}	
		
		if (strpos($sLine, 'break;') !== false) {
		  $sLine = str_replace('break','<span class="codeblue">break</span>',$sLine);						
		}	
		
		if (strpos($sLine, 'switch(') !== false) {
		  $sLine = str_replace('switch','<span class="codeblue">switch</span>',$sLine);						
		}
		
		if (strpos($sLine, 'case ') !== false) {
		  $sLine = str_replace('case','<span class="codered">case</span>',$sLine);						
		}	
		
		if (strpos($sLine, 'default:') !== false) {
		  $sLine = str_replace('default','<span class="codered">default</span>',$sLine);						
		}
		
		if ((strpos($sLine, 'print ') !== false) && (strpos($sLine, '/* ') === false)) {
		  $sLine = str_replace('print ','<span class="codeblue">print </span>',$sLine);						
		}																
		
		if ((strpos($sLine, '(') !== false) || (strpos($sLine, ')') !== false)) {
		  $sLine = str_replace('(','<span class="codeblue"><b>(</b></span>',$sLine);
		  $sLine = str_replace(')','<span class="codeblue"><b>)</b></span>',$sLine);						
		}	
		
		if ((strpos($sLine, '{') !== false) || (strpos($sLine, '}') !== false)) {
		  $sLine = str_replace('{','<span class="codeblue"><b>{</b></span>',$sLine);
		  $sLine = str_replace('}','<span class="codeblue"><b>}</b></span>',$sLine);						
		}	
		
		if ((strpos($sLine, '[') !== false) || (strpos($sLine, ']') !== false)) {
		  $sLine = str_replace('[','<span class="codeblue"><b>[</b></span>',$sLine);
		  $sLine = str_replace(']','<span class="codeblue"><b>]</b></span>',$sLine);						
		}	
		
		if ((strpos($sLine, '&lt;?php') !== false) || (strpos($sLine, '?&gt;') !== false)) {
		  $sLine = str_replace('&lt;?php','<span class="codered"><b>&lt;?php</b></span>',$sLine);
		  $sLine = str_replace('?&gt;','<span class="codered"><b>?&gt;</b></span>',$sLine);									
		}														
				
    echo "<a name=\"".$iLineNumber."\"></a><b>" . ($iLineNumber + 1) . "</b> : " . $sLine . "<br />\n";
  }
?>
</body>
</html>
