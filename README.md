Web Blast

Web interface to submit blast jobs to a cluster.  It splits the blast queries into chunks and distributes them across a cluster.


INSTALL GUIDE

* Create a blastweb cluster user to run webblast scripts on the nodes
* Copy BLASWEB_HOME_DIR to the blastweb user home directory so it is accessible from the nodes
* Set webblast.conf path to webblast.conf which is located in the BLASWEB_HOME_DIR in webblast/includes/config.php , BLASTWEB_HOME_DIR

NOT DONE YET STILL WORKING ON MAKING IT MORE PORTABLE
