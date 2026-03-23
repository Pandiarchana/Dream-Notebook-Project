<?php
    include("dbconnect.php");
    session_start();
?>

	<style type="text/css">
		body {
		    font-family: Arial, sans-serif;
		    background: #7588a7;
		    min-height: 100vh;
		    display: flex;
		    flex-direction: column;
		}


		.formbox {
		    flex: 1;
		    display: flex;
		    justify-content: center;
		    align-items: center;
		    align-items: flex-start;
    		padding: 100px 15px;

		}

		.uptbox {
		    background: rgba(255, 255, 255, 0.95);
		    padding: 30px 40px;
		    border-radius: 15px;
		    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
		    width: 700px;
		    max-width: 95%;
		}

		.uptbox h2 {
		    text-align: center;
		    color: #7588a7;
		    margin-bottom: 5px;
		}
		.uptbox h3 {
		    text-align: center;
		    color: #7588a7;
		    margin-bottom: 5px;
		}

		.uptbox p {
		    margin: 15px 0 5px;
		    font-weight: bold;
		    color: #333;
		}

		.uptbox input,
		.uptbox textarea {
		    width: 100%;
		    padding: 10px;
		    margin: 8px 0;
		    border: 1px solid #ccc;
		    border-radius: 8px;
		    font-size: 14px;
		    transition: 0.3s;
		}

		.uptbox input:focus,

		.uptbox textarea:focus {
		    border-color: #7588a7;
		    outline: none;
		    box-shadow: 0 0 5px rgba(117,136,167,0.5);
		}

	</style>


<?php

if(isset($_POST['del'])){
    $eid = $_POST['del'];

    $query = "delete from entry where eid=$eid";

    if(mysqli_query($connect,$query)){
        header("location:userpage.php");
        exit();
    }
}


else if(isset($_POST['update'])){
    $eid = $_POST['update'];
    $_SESSION['eid'] = $eid;

    $query = "select * from entry where eid=$eid";
    $result = mysqli_query($connect,$query);

    if($record = mysqli_fetch_array($result)){
    	print("<div class='formbox'>");
    	print("<div class='uptbox'>");
        print("<form action=Delete.php method=POST>");
        print("<h2>Update Your Entry</h2>");

        print("<label>Title:</label>");
        print("<input type=text name=title value='$record[title]'>");

        print("<label>Date:</label>");
        print("<input type=date name=date value= '$record[date]'>");

        print("<label>time:</label>");
        print("<input type=time name=time value= '$record[time]'>");

        print("<label>description:</label>");
        print("<textarea name=description rows=10 cols=30>$record[description]</textarea>");


        print("<tr><td colspan=2 style='text-align:center;'>
               <input type=submit name=sub value=Update></td></tr>");
        print("</table>");
        print("</form>");
        print("</div>");
        print("</div>");
    }
}

else if(isset($_POST['sub'])){
    $title = $_POST['title'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $description = $_POST['description'];
    $eid   = $_SESSION['eid'];

    $query = "UPDATE entry
              SET title='$title', date='$date', time='$time', description='$description'
              WHERE eid='$eid'";

    if(mysqli_query($connect, $query)){
        header("Location: userpage.php");
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($connect);
    }
}
?>
</div>