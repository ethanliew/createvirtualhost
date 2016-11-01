<?php
/* 
	@Product	Simple PHP virtual host generator ( for xampp )
	@NOTE	Right click on xampp icon and click on run as administrator!!!
	@Example	http://localhost/create-virtualhost.php?xampproot=D:/xampp&devdomain=abc.dev&docroot=E:/abc

	@Author	Ethan Liew <ethanliew.now@gmail.com>
	@Since 	2016-11-01
	@Version	1.0
	
	@Github	https://github.com/ethanliew/createvirtualhost.git
	
	@Disclaimer:
	- Please read through the code and no warranty for any injuries on your existing device or settings
	- Recommend for windows xampp user only 
	- Recommend for php development only 
	
	
	

	please check if you have default  default htdocs virtual host access
	=============================================
	# sample htdocs
	<VirtualHost *:80>
	  ServerName localhost
	  DocumentRoot "D:/xampp/htdocs"
	  <Directory "D:/xampp/htdocs">
		AllowOverride All
		Require all Granted
	  </Directory>
	</VirtualHost>

	template for a new virtual host
	======================
	#locafood.a
	#https://delanomaloney.com/2013/07/10/how-to-set-up-virtual-hosts-using-xampp/
	#ngrok http -host-header:localfood.a 80
	<VirtualHost *:80>
	  ServerName locafood.a
	  ServerAlias www.locafood.a
	  DocumentRoot "E:/LocaFood"
	  <Directory "E:/LocaFood">
		AllowOverride All
		Require all Granted
	  </Directory>
	</VirtualHost>	
	
*/

// set default timezone
date_default_timezone_set("Asia/Kuala_Lumpur");

// change your hosts file if different from here
define( 'FP_HOSTS' , 'C:\Windows\System32\drivers\etc\hosts');

// disable php notice warning
error_reporting( error_reporting() & ~E_NOTICE );

// function to check if request is null or empty
function isnullorempty( $text ){	return !isset( $text ) || empty( $text ); }

$lb = PHP_EOL;		 // line break
$ymdhis = date('Ymd_His');

// auto define httpd-vhosts.conf from xampp root path. example: d:/xampp
$xampp_root = isnullorempty( $_REQUEST['xampproot'] ) ? '' : $_REQUEST['xampproot'];	
$dev_domain = isnullorempty( $_REQUEST['devdomain'] ) ? '' : $_REQUEST['devdomain'];	// dev domain in /etc/hosts file
$doc_root = isnullorempty( $_REQUEST['docroot'] ) ? '' : $_REQUEST['docroot'];			// doc root where you dev your php proj

// check compulsary params
if( strlen( $xampp_root )==0 ) { echo '<div>xampproot param needed. example: xampproot=D:/xampp</div>';  }
if( strlen( $dev_domain )==0 ) { echo '<div>devdomain param needed. example: devdomain=abc.dev</div>';  }
if( strlen( $doc_root )==0 ) { echo '<div>docroot param needed. example: docroot=E:/phpproject</div>';  }

// show example usage if user get an error
if(  strlen( $xampp_root )==0 || strlen( $dev_domain )==0 ||  strlen( $doc_root )==0 ) { 
	echo 'example: http://localhost/create-virtualhost.php?xampproot=D:/xampp&devdomain=abc.dev&docroot=E:/abc';exit; 
}

try
{

// check if windows hosts file exist or not
if ( !file_exists( FP_HOSTS ) ) {    echo "<div>The windows hosts file ".FP_HOSTS." may not exists</div>"; exit; }

// backup hosts file
if (!copy(FP_HOSTS, FP_HOSTS.'.'. $ymdhis.'.bak')) {    echo "<div>Fail to backup windows hosts file</div>"; exit;}

// open hosts file
$content_hosts = file_get_contents(FP_HOSTS);

// check if dev domain existed
if( strpos(  $content_hosts, $dev_domain ) > -1 ) {    echo "<div>dev domain( $dev_domain ) existed in windows hosts. process terminated.</div>"; exit;}

// open hosts file and append dev domain mapping
$content_hosts .= $lb;
$content_hosts .= '#'.$dev_domain.$lb;
$content_hosts .= '127.0.0.1 '.$dev_domain.$lb;
file_put_contents(FP_HOSTS, $content_hosts);

// replace forward slash to backslash for physical file path
$xampp_root = str_replace('\\', '/', $xampp_root );	

// check if vhosts file existed
$file = $xampp_root.'\apache\conf\extra\httpd-vhosts.conf';
if ( !file_exists($file) ) {    echo "<div>The file $file may not exists</div>"; exit; }

// check if dev domain existed in vhosts config file
if( strpos(  $file, $dev_domain )  > -1) {    echo "<div>dev domain( $dev_domain ) existed in vhosts. process terminated.</div>"; exit;}

// backup httpd-vhost.conf  file
if (!copy($file, $file.'.'. $ymdhis.'.bak')) {    echo "<div>Fail to backup httpd-vhosts.conf file</div>"; exit;}

// open vhosts file and append new virtual host setting
$current = file_get_contents($file);
$current .= $lb;
$current .= '#'.$dev_domain.$lb;
$current .= '<VirtualHost *:80>'.$lb;
$current .= '  ServerName '.$dev_domain.$lb;
$current .= '  ServerAlias www.'.$dev_domain.$lb;
$current .= '  DocumentRoot "'.$doc_root.'"'.$lb;
$current .= '  <Directory "'.$doc_root.'">'.$lb;
$current .= '	AllowOverride All'.$lb;
$current .= '	Require all Granted'.$lb;
$current .= '  </Directory>'.$lb;
$current .= '</VirtualHost>'.$lb;
file_put_contents($file, $current);

// restart apache
//exec('echo adminpassword | runas /user:administrator fullPathToProgram',$output);
//echo shell_exec('service httpd restart &');

// display result to user
echo "<div style=\"background-color:yellow; font-size:1.2em;\" > Please restart your apache to start using it at http://$dev_domain </div>";
echo "<div style=\"background-color:yellow; font-size:1.2em;\" >Your development domain( $dev_domain )  at $doc_root had been created.</div><br/>";
echo "<div style=\"background-color:yellow; font-size:1.2em;\" >Thanks for using created-virtualhost.php by Ethan Liew https://github.com/ethanliew/createvirtualhost.git</div><br/>";
echo "<div style=\"background-color:yellow; font-size:1.2em;\">Please check your updated settings at below (".$file.")</div><br/>";
echo "<xmp>";
echo file_get_contents($file);
echo "</xml>";


}catch(Exception $ex){
	print_r($ex);
}


?>