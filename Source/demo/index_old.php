<style>
  /* CSS style for the alternating names */
  .name1 {
    background-color: black;
    color: white;
    text-align: center;
    font-size: 24px;
    margin: 0;
  }

  .name2 {
    background-color: black;
    color: white;
    text-align: center;
    font-size: 24px;
    margin: 0;
  }

  .centered-paragraph {
    text-align: center;
  }

  .orange-box {
    background-color: #FDB826;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    width: 40%;
    margin: 0 auto;
  }

  /* Style for the button */
  .button {
    background-color: #C20430;
    color: white;
    text-align: center;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
  }
  .button2 {
    background-color: #C20430;
    color: white;
    text-align: center;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    font-size: 18px;
    cursor: pointer;
  }
  
  /* Center-align the button */
  .button-container {
    text-align: center;
  }
  
  .php-text
  {
     font-size: 24px;
    text-align: center;
  }
  
  .individuals-button{
   text-align: center;
  
  }
  .container {
    display: flex;
    justify-content: center; /* Center items horizontally */
    align-items: center;     /* Center items vertically */
         /* Take the full viewpaort height */
}

/* Optional: Add some space between the items */
.individuals-button {
    margin-left: 10px;
}


</style>
</head>
<title>PHP Test</title>
</head>



<body>

<div class="name1">Course Planner</div>
<div class="name2">Course Management Software for UoG</div>

<div class="centered-paragraph">
  <p>The coursesMacro.xslm file contains a program that when a user enters their completed courses, they can get a highlighted list of acceptable courses they can take next semester.</p>
</div>

<div class="orange-box">
  <ol>
    <li>Open courseMacro.xslm</li>
    <li>Open "input" tab</li>
    <li>Enter course codes of completed courses in column "A" under the header</li>
    <li>Click Run</li>
    <li>Browse highlighted courses in "input" spreadsheet</li>
  </ol>
</div>

<div class="centered-paragraph">
  <p>Limitations:</p>
</div>

<div class="orange-box">
  <ol>
    <li>Currently don't recognize credit amounts as prerequisites i.e 12 credits including ZOO*1000</li>
    <li>Some prerequisites have irregular format that may cause them to not be highlighted</li>
    <li>Restrictions are currently not taken into account when determining eligible courses, please manually verify if a course you want to take is restricted</li>
  </ol>
</div>
<br>

<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST">
<div class="button-container">
  <input class="button" type="submit" value="Download - Winx64, 1.0.0" name="button">
  <p><?php
    include_once("database.php");
    $result = $db->query('SELECT COUNT(*) FROM downloads');
    if($result) {
      echo $result->fetch_array()[0] . ' Downloads';
    }
  ?></p>
</div>
</form>
<br>
<div class="container">
    <div class="individuals-button">
        <form action="" method="post">
            <input class="button" name="restApi" type="submit" value="REST API">
            <input class="button" name="apiDoc" type="submit" value="API Doc">
        </form>
    </div>
</div>


</div>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $button = $_REQUEST["button"];
  $demoNames = array(
    'carterDemo' => 'carterDemo.php',
    'ryanDemo' => 'ryanDemo.php',
    'jerrittDemo' => 'jerrittDemo.php',
    'eddieDemo' => 'eddieDemo.php',
    'noahDemo' => 'noahDemo.php',
    'talhaDemo' => 'talhaDemo.php',
    'braydenDemo' => 'braydenDemo.php',
  );

  if (array_key_exists('restApi', $_REQUEST)) {
    header('Location: /rest/api.php');
    exit();
  }

  if (array_key_exists('apiDoc', $_REQUEST)) {
    header('Location: /readme.html');
    exit();
  }

  if(empty($button)) {

  } else {

    include_once('database.php');
    $stmt = $db->prepare("INSERT INTO downloads (id, ip_address, downloaded_on) VALUES (DEFAULT, ?, DEFAULT)");
    $client_ip = $_SERVER['REMOTE_ADDR'];
    $stmt->bind_param('s', $client_ip);
    $stmt->execute();

    //header('Content-Description: File Transfer');
    //header('Content-Type: application/octet-stream');
    //header('Content-Disposition: attachment; filename="coursePlanner.xlsm"');
    //header('Expires: 0');
    //header('Cache-Control: must-revalidate');
    //header('Pragma: public');
    //header('Content-Length: ' . filesize("coursePlanner.xlsm"));
    //flush(); // Flush system output buffer
    //readfile('coursePlanner.xlsm');
    //die();
    header('Location: https://cis3760f23-10.socs.uoguelph.ca/coursePlanner.xlsm');
  }
}
?>
</body>
</html>
