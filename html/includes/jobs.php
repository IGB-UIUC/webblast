<?php
include "includes/txt2html.php";

$statusDeleting=10;

$queryJobsArray= "SELECT j.name,j.submitDate,j.completeDate,j.queriesadded,j.queriescompleted,j.id,j.status, s.name AS statusname,j.priority FROM blast_jobs j, users u, status s WHERE u.netid=\"".$_SESSION['username']."\" AND j.userid=u.id AND s.id=j.status AND j.status!=".$statusDeleting." ORDER BY id DESC";
$userJobsArray = $sqlDataBase->query($queryJobsArray);
$statusCompleted=3;

if(isset($_GET['action']) && isset($_GET['job']))
{
   $jobId = mysql_real_escape_string($_GET['job']);
   $actionJob=new Job($sqlDataBase,$config_array);
   $actionJob->LoadJob($jobId);
   
   if($actionJob->GetUserId()==$_SESSION['userid'])
   {
	if($_GET['action']=='incprior')
        {
		$blastnId = 1;
		if( ((($actionJob->GetQueriesAdded() < 10000 && $actionJob->GetBlastId()==$blastnId) || ($actionJob->GetQueriesAdded() < 4000 && $actionJob->GetBlastId()!= $blastnId)) && $actionJob->GetPriority()<1) 
			|| ((($actionJob->GetQueriesAdded() < 1000 && $actionJob->GetBlastId()==$blastnId) || ($actionJob->GetQueriesAdded() < 400 && $actionJob->GetBlastId()!= $blastnId)) && $actionJob->GetPriority()<2)  )
		{
                	$actionJob->SetPriority($actionJob->GetPriority()+1);
		}
		else
		{
			echo "<br>
				<table class='table table-striped'>
				<tr>
					<td colspan='3'><center><b>Priority Rules</b></center></td>
				</tr>
				<tr>
					<td><b>Priority #</b></td>
					<td><b># Queries</b></td>
					<td><b>Program</b></td>
				</tr>
				<tr>
					<td>1</td>
					<td><10000</td>
					<td>BLASTN</td>
				</tr>
				<tr>
                                        <td>1</td>
                                        <td><4000</td>
                                        <td>BLASTX,BLASTP,TBLASTN,TBLASTX</td>
                                </tr>	
				<tr>
                                        <td>2</td>
                                        <td><1000</td>
                                        <td>BLASTN</td>
                                </tr>
                                <tr>
                                        <td>2</td>
                                        <td><400</td>
                                        <td>BLASTX,BLASTP,TBLASTN,TBLASTX</td>
                                </tr>
				</table><br><br>";
		}
        }

        if($_GET['action']=='decprior')
        {
                if(($actionJob->GetPriority()-1)>=0)
                {
                        $actionJob->SetPriority($actionJob->GetPriority()-1);
                }
        }

	if($_GET['action']=='delete')
	{
		$actionJob->DeleteJob();
	}
	
	if($_GET['action']=='reset')
	{
                $actionJob->ResetJob();
	}
	
	if($_GET['action']=='cancel')
	{
                $actionJob->CancelJob();
	}
	
	if($_GET['action']=='concat')
	{
		$httphost  = $_SERVER['HTTP_HOST'];
		$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
		$extra = "download.php?job=".$actionJob->GetJobId()."&filetype=result&token=".$actionJob->GetToken();
		header("Location: http://$httphost$uri/$extra");
	}
	
	if($_GET['action']=='csv')
	{
                $httphost  = $_SERVER['HTTP_HOST'];
                $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                $extra = "download.php?job=".$actionJob->GetJobId()."&filetype=csv&token=".$actionJob->GetToken();
                header("Location: http://$httphost$uri/$extra");

	}

	if($_GET['action']=='transfer')
	{
		$actionJob->TransferToDropBox();
	}
	
	if(isset($_POST['scpSend']))
        {
                $scpHost = escapeshellcmd($_POST['scpHost']);
                $scpUser = escapeshellcmd($_POST['scpUser']);
                $scpPass = escapeshellcmd($_POST['scpPass']);
                $scpPath = escapeshellcmd($_POST['scpPath']);

                $scpJob = new Job($sqlDataBase,$config_array);
                $scpJob->LoadJob($_POST['jobToSCP']);
                $_GET['job']=$_POST['jobToSCP'];
		$scpJob->SCPJob($scpHost,$scpPath,$scpUser,$scpPass);
        }
	else
        {
		//Set Default SCP variables
		$scpDefaultHost = "file-server.igb.illinois.edu";
		$firstLetter = substr($_SESSION['username'],0,1);
		if(in_array($firstLetter,range('a','m')))
		{
        		$scpPathRange =  "a-m";
		}
		elseif(in_array($firstLetter,range('n','z')))
		{
		        $scpPathRange = "n-z";
		}


                $scpHost = "file-server.igb.uiuc.edu";
                $scpUser = $_SESSION['username'];
                $scpPass = $_SESSION['password'];
                $scpPath = "/file-server/home/".$scpPathRange."/".$_SESSION['username']."/";
        }

        if($_GET['action']=='transfercostum' || isset($_POST['scpSend']))
        {
                echo "<form action=\"index.php?view=jobs&job=".$_GET['job']."&action=transfercustom\" method=\"POST\">";
                echo "<table class='table table-striped table-condensed'>";
		echo "<th colspan=\"2\">Secure File Copy (SCP)</th>";
                echo "<tr><td>Job #: </td><td><select name=\"jobToSCP\">";
                foreach($userJobsArray as $id=>$assoc)
                {
                        echo "<option value=".$assoc['id'];
                        if($assoc['id']==$_GET['job'])
                        {
                                echo " SELECTED";
                        }
                        echo ">".$assoc['id']."</option>";
                }
                echo "<tr><td>Host: </td><td> <input type=\"text\" name=\"scpHost\" value=\"".$scpHost."\"></td></tr>";
                echo "<tr><td>Path: </td><td><input type=\"text\" name=\"scpPath\" value=\"".$scpPath."\"></td></tr>";
                echo "<tr><td>Username:</td><td> <input type=\"text\" name=\"scpUser\" value=\"".$scpUser."\"></td></tr>";
                echo "<tr><td>Password:</td><td> <input type=\"password\" name=\"scpPass\" value=\"".$scpPass."\"></td></tr>";
                echo "<tr><td></td><td><input type=\"submit\" name=\"scpSend\" value=\"Transfer\"></td></tr>";
                echo "</table>";

                echo "</form>";
        }
	
   }	
}
?>
<a href="index.php?view=jobs">Click To Refresh Jobs</a><br><br>
Use the +/- sign to increase/decrease a job's priority.
<table class='table table-stripped table-condensed'>
<tr>
<th>+/-</th>
<th>Priority</th>
<th>Job ID</th>
<th>Job Name</th>
<th>Submit Date</th>
<th>Complete Date</th>
<th>Progress</th>
<th>Status</th>
<th>Options</th>
<th>Results</th>
</tr>
<?php
if($userJobsArray)
{
	foreach($userJobsArray as $id=>$assoc)
	{
		echo "<tr>";
		echo "<td><a href=\"index.php?view=jobs&job=".$assoc['id']."&action=incprior\"><img src=\"images/plus-icon.png\"></a><a href=\"index.php?view=jobs&job=".$assoc['id']."&action=decprior\"><image src=\"images/minus-icon.png\"></a></td>";
                echo "<td>".$assoc['priority']."</b>";
		echo "<td>".$assoc['id']."</td>";
		echo "<td>".$assoc['name']."</td>";
		echo "<td>".$assoc['submitDate']."</td>";
		echo "<td>".$assoc['completeDate']."</td>";
		echo "<td><a href=\"index.php?view=queries&job=".$assoc['id']."&action=showqueries\"><div class=\"progress-container\"><div style=\"width: ".(($assoc['queriescompleted'] / $assoc['queriesadded'])*100)."%\"> ".$assoc['queriescompleted']."/".$assoc['queriesadded']." </div></div></a></td>";
		echo "<td>". ucwords($assoc['statusname']) ."</td>";
		echo "<td>";
		echo "<a href=\"index.php?view=jobs&job=".$assoc['id']."&action=delete\">Delete</a>";
		echo " | <a href=\"index.php?view=jobs&job=".$assoc['id']."&action=cancel\">Cancel</a>";
		echo "</td><td>";
		if($assoc['status'] == $statusCompleted)
		{
			echo "<a href=\"index.php?view=jobs&job=".$assoc['id']."&action=concat\">Download</a>";	
			echo " | <a href=\"index.php?view=jobs&job=".$assoc['id']."&action=csv\">CSV</a>";
			echo " | <a href=\"index.php?view=csvheader&job=".$assoc['id']."\">CSV Titles</a>";
		}
		echo "</td></tr>";
	
	}
}
?>
</table>
