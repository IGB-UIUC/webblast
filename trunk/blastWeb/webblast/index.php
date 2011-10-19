<?php
session_start();
include "includes/header.php";
function __autoload($class_name) {
    require_once 'classes/' . $class_name . '.php';
}

include "includes/config.php";

//initialize ldap authentication object
$authen=new LdapAuth($config_array['ldap_config']['ldap_host'],$config_array['ldap_config']['ldap_peopleDN'],$config_array['ldap_config']['ldap_groupDN'],$config_array['ldap_config']['ldap_ssl'],$config_array['ldap_config']['ldap_port']);

//Initialize database
$sqlDataBase= new SQLDataBase($config_array['sql_config']['sql_host'],$config_array['sql_config']['sql_database'],$config_array['sql_config']['sql_user'],$config_array['sql_config']['sql_pass']);
echo "initialize mysql";
include "includes/authenticate.php";
if(isset($_SESSION['username']) && isset($_SESSION['password']))
{
	if($authen->Authenticate($_SESSION['username'],$_SESSION['password'],""))
	{
		include "includes/logout.php";
		echo "<font color=\"blue\"><b>You may now increase/decrease your job's priority using the +/- signs under the View Jobs tab. 
<br>This feature is best used when your job is small and there are very large jobs ahead of it in the queue.</b></font><br><br>";
		include "includes/ncbiLastUpdate.php";
		include "includes/navigation.php";
		if(isset($_GET['view']))
		{
			if($_GET['view']=='jobs')
			{
				include "includes/jobs.php";
			}
			elseif($_GET['view']=='queries')
			{
				include "includes/queries.php";
			}
			elseif($_GET['view']=='results')
			{
				include "includes/results.php";
			}
			elseif($_GET['view']=='query')
			{
				include "includes/query.php";
			}
			elseif($_GET['view']=='draw')
                        {
                                include "includes/draw.php";
                        }
			elseif($_GET['view']=='csvheader')
			{
				include "includes/csvheader.php";
			}
			elseif($_GET['view']=='managedatabases')
                        {
                                include "includes/databases.php";
                        }
			elseif($_GET['view']=='createdatabase')
                        {
                                include "includes/databaseUpload.php";
                        }
			elseif($_GET['view']=='clusterstatus')
                        {
                                include "includes/clusterStatus.php";
                        }
			elseif($_GET['view']=='test')
			{
				include "includes/uploadfasta_simple_test.php";
				include "includes/mainform_simple_test.php";
			}
			else
			{	
				echo "form submitted try to upload fasta";
				include "includes/uploadfasta_simple.php";
                                include "includes/mainform_simple.php";
			}
		}
		else {
			include "includes/uploadfasta_simple.php";
     	                include "includes/mainform_simple.php";
		}
		
	}
	else {
		include "includes/login.php";
		echo "authentication failed";
	}
}
else {
	include "includes/login.php";
}
include "includes/footer.php";
?>
