<?php

function insert_break(){	
	if(PHP_SAPI!='cli'){$brstr= "<br>";}else{$brstr= "\n";}
	echo $brstr;
}



?>
