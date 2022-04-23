<html>
<head>
	<?php
    if (isset($_POST["query"])) $title = "Search for \"" . $_POST["query"] . "\"";
    else $title = "Search";
    include 'head.php';
    ?>
</head>

<body>
	<a href="/"><table><tr>
		<td><img src="/favicon.png" width="50"></td>
		<td><h1 style="margin: 0">Software<i>Rewind</i></h1><i>Archiving Software Since April 2022</i></td>
	</tr></table></a>
	<hr>
    <?php include 'nav.html'; ?>
    <hr>
    <!-- BEGIN Content -->
    <form action="search.php" method="post">
    	<input type="text" name="query">&nbsp;
        <input type="submit"><br>
    	<input type="radio" name="scope" checked="true"
        <?php if (isset($scope) && $scope=="title") echo "checked";?>
        value="title">Title
        <input type="radio" name="scope"
        <?php if (isset($scope) && $scope=="filename") echo "checked";?>
        value="filename">File Name
    </form>
    <br>
    <?php

    function test_input($data) {
    	$data = trim($data);
    	$data = stripslashes($data);
    	$data = htmlspecialchars($data);
    	return $data;
    }
    $queryScope = test_input($_POST["scope"]);
    $query = '%'.$_POST["query"].'%';
    if($query != "%%"){
    	$cleanquery = str_replace("%","",$query);
    	//echo "Results for \"" . $cleanquery . "\" in " . $queryScope;
    }
    ?>
    <?php
    include 'creds.php';

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
    	die("<b>Connection failed:</b> " . $conn->connect_error);
    }

    if ($queryScope == "title") {
    	// Query by system model
    	if ($query == "%%") {
    		return;
    	} else {
            $querytime = microtime(true);
    		$stmt = $conn->prepare("SELECT ID, Name, CategoryID FROM software WHERE Name LIKE ?");
    		$stmt->bind_param(s,$query);
    		$stmt->execute();
    		$result = $stmt->get_result();
            $querytime = microtime(true) - $querytime;
			if ($result->num_rows > 0) $results = $result->num_rows;
			else $results = "No";
    		echo $results . " results for \"" . $cleanquery . "\" in software titles<br><b>Query Time:</b> " . round($querytime, 5) . "ms";
    	}
    
    	if ($result->num_rows > 0) {
    		// output data of each row
    		while($row = $result->fetch_assoc()) {
    			echo "<h2>ID " . $row["ID"] . " - ". $row["Name"] . " (Category " . $row["CategoryID"] . ")</h2>";
    		}
    	}
    } else if ($queryScope == "filename") {
    	// Query by device name/manufacturer
        if ($query == "%%") {
    		return;
    	} else {
            $querytime = microtime(true);
            $stmt = $conn->prepare("SELECT ID, Path, Filename, Mirrors, Risk FROM files WHERE Filename LIKE ?");
    		$stmt->bind_param(ss,$query, $query);
    		$stmt->execute();
    		$result = $stmt->get_result();
            $querytime = microtime(true) - $querytime;
            echo $result->num_rows . " results for \"" . $cleanquery . "\" in filenames (took " . round($querytime, 5) . "ms)<hr>";
    	}
    
    	if ($result->num_rows > 0) {
    		// output data of each row
    		while($row = $result->fetch_assoc()) {
    			echo "<h2><a href=\"/devices.php?id=" . $row["ID"] . "\">". $row["Filename"] . " " . $row["Risk"] . "</a></h2>";
    		}
    	}
    }
    $conn->close();
    ?>
    <!-- END Content-->
    <hr>
	<address>&copy; 2022 <a href="http://nickandfloppy.com/">nick and floppy</a></address>
</body>
</html>
