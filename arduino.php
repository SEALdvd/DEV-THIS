<?php

// voor arduino

// DB settings
define('DB_USER', "dannyvan_arduino");
define('DB_PASSWORD', "arduino");
define('DB_DATABASE', "dannyvan_arduino");
define('DB_SERVER', "localhost");


/** DB connect class **/
class DB_Connect {
  // constructor
  function __construct() {
  }

  // destructor
  function __destruct() {
    // $this->close();
  }

  // Connecting to database
  public function connect() {
    // connecting to mysqli
    $con = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD);
    // selecting database
    mysqli_select_db($con, DB_DATABASE);
    // return database handler
    return $con;
  }

  // Closing database connection
  public function close() {
    $con = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD);
    mysqli_close($con);
  }
}

//functions database
class DB_Functions {
  private $db;
  //put your code here
  // constructor
  function __construct() {
    // connecting to database
    $this->db = new db_connect();
    $this->db->connect();
  }
  // destructor
  function __destruct() {
  }

  // Post knop - van arduino knop
  public function post($id_receiver, $id_sender, $time, $number) {
    $result = mysqli_query($this->db->connect(), "INSERT INTO dataCollection(id_receiver, id_sender, s_time , s_number) VALUES('$id_receiver','$id_sender','$time', '$number')") or die( mysqli_error($this->db->connect()));
    if($result){
      return true;
    }
    else{
      return false;
    }
  }

  //Get - elke 10 seconden
  public function get($id_receiver) {
    $result = mysqli_query($this->db->connect(),"SELECT * FROM dataCollection WHERE id_receiver = '$id_receiver'") or die(mysqli_error($this->db->connect()));
    $no_of_rows = mysqli_num_rows($result);
    if ($no_of_rows > 0) {
      return $result;
    } else {
      return false;
    }
  }
}

// 'main'
if (isset($_POST['tag']) && $_POST['tag'] != '') {

  // get tag
  $tag = $_POST['tag'];
  $db = new db_functions();

  // response Array
  $response = array("tag" => $tag, "success" => 0, "error" => 0);
  switch (true) {

    // GET van de gegevens
    case ($tag == 'get'):
      $id_receiver = $_GET['id_receiver'];
      if($db->get($id_receiver) !== false) {
        $get = $db->get($id_receiver);
        $response["get"] = array();
        while($row = mysqli_fetch_array( $get, MYSQLI_BOTH )) {

          $postArray = array(
            'id' => $row["id"],
            'id_sender' => $row["id_sender"],
            'id_receiver' => $row["id_receiver"],
            'time' => $row["s_time"],
            'number' => $row["s_number"]
          );
          array_push($response["get"], $postArray);
        }
        $response['success'] = 1;
        // JSON encode
        echo json_encode($response);
      }
      else {
        $response["success"] = 0;
        $response["pictures"] = "Nothing found";
        echo json_encode($response);
      }
      break;


    // POST de gegevens
    case ($tag == 'post'):
      $id_sender = $_POST['id_sender'];
      $id_receiver = $_POST['id_receiver'];
      $number = $_POST['s_number'];
      $time = $_POST['s_time'];

      //If picture exist
      if($db->post($id_receiver, $id_sender, $time, $number) !== false) {
          //Response
          $response['success'] = 1;
          // JSON encode
          echo json_encode($response);
      }
      //Error handling
      else{
        $response['success'] = 0;
        $response['message'] = 'Something went wrong.';
        echo json_encode($response);
      }
      break;

    // default for case
    default:
      echo "Invalid Request";
      break;
  }

  //Default
} else {
  echo "Access Denied";
}
