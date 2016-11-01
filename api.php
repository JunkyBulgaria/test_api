<?php
include("dep/config.php");
include("dep/mysql.php");
$db = new SQL();
$db->connect($host, $user, $pass, $database);
if(!isset($_SERVER['REQUEST_METHOD'])) die("NO REQUEST METHOD SET"); if(!isset($_SERVER['PATH_INFO'])) die("NO SERVER PATH INFO");
$m = $_SERVER['REQUEST_METHOD']; // post / get / delete / put ? drugi ne trqbvat za sega
$request = explode('/', trim($_SERVER['PATH_INFO'],'/')); // array
$table = preg_replace('/[^a-z0-9_]+/i','', array_shift($request)); // regex + escape string po-nadolu
$id = array_shift($request); // 0 trqbva da e id
$input = file_get_contents('php://input');

if ($table != "news") die("WRONG TABLE!");  // hardcode za sega tui kato shte izpolzvame samo 1 tablica? 

switch($m)
{
    case 'GET':
            if (!is_numeric($id)) die("WRONG ID!"); if (!isset($table) || !isset($id)) die("Table or ID was not set, please try again. [GET]");
            if ($id <= 0) die("ID should be higher than 0, please try again.");
            $result = $db->query("select * from `" . $db->mysql_escape($table) . "` where id=" . $db->mysql_escape($id));
              for ($i=0; $i<$db->num_rows($result); $i++) {
                echo ($i>0 ? ',' : '') . json_encode($db->fetch_object($result), JSON_PRETTY_PRINT);
              }
        break;
    case 'DELETE':
            if (!is_numeric($id)) die("WRONG ID!"); if (!isset($table) || !isset($id)) die("Table or ID was not set, please try again.");
            if ($id <= 0) die("ID should be higher than 0, please try again. [DELETE]");
            if ($db->query("select * from `$table` where id=$id"))
            {
                if ($db->query("delete from `$table` where id=$id"))
                    echo "OK";
            }
            else echo "ERROR";
        break;
    case 'POST':
            if (!isset($table)) die("Table or ID was not set, please try again. [POST]");
            if (isset($_POST["title"])) $title_POST = $db->mysql_escape($_POST["title"]); else die("NO TITLE");
            if (isset($_POST["date"])) $date_POST =  "'" . $db->mysql_escape($_POST["date"]) . "'"; else $date_POST = "NOW()";
            if (isset($_POST["text"])) $text_POST = $db->mysql_escape($_POST["text"]); else die("NO TEXT");
            if ($db->query("INSERT INTO `$table` (`title`, `date`, `text`) VALUES ('".$title."', ".$date.", '".$text."')"))
                echo "OK";
            else echo "ERROR";
        break;
    case 'PUT':
            $vars = array();
            parse_str($input, $vars);
            $id = $vars['id']; $title = $db->mysql_escape($vars['title']); $db->mysql_escape($date = $vars['date']); $text = $db->mysql_escape($vars['text']);
            if(!is_numeric($id)) die("ERROR")
            $sql = "UPDATE `$table` SET";
                if (isset($title)) $sql .= " `title` = '" . $title . "'"; 
                if (isset($date)) $sql .= ", `date` = " . $date; 
                if (isset($text)) $sql .= ", `text` = '" . $text . "'"; 
            $sql .= " WHERE `id` = " . $id . ";";

            if ($db->query($sql)) echo "OK"; else echo "ERROR";
        break;
}
?>