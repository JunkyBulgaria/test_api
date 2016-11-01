<?php
if (!function_exists('mysql_connect'))
  die('This PHP environment doesn\'t have MySQL support built in.');

class SQL
{
  var $link_id;
  var $query_result;
  var $num_queries = 0;

  function connect($db_host, $db_username, $db_password, $db_name) 
  {
    $this->link_id = @mysql_connect($db_host, $db_username, $db_password);
    if ($this->link_id)
    {
        if($db_name)
        {
            if (@mysql_select_db($db_name, $this->link_id))
                return $this->link_id;
            else
                die("can't select db");
        }
    }
    else 
    {
        die(mysql_error());
    }
  }
  

  function query($sql){
    $this->query_result = @mysql_query($sql, $this->link_id);

    if ($this->query_result){
      ++$this->num_queries;
      return $this->query_result;
    } else {
        $error = "
            <pre>
                QUERY: \n {$sql} \n\n
                ERROR: <span style=\"color:red\">" . mysql_error() . " </span>
            </pre>
        ";
      die($error);
    }
  }

  function db($db_name) {
    if ($this->link_id){
      if (@mysql_select_db($db_name, $this->link_id)) return $this->link_id;
        else die(mysql_error());
    } else die(mysql_error());
  }

  function insert($tbl,$data){
        foreach($data as $field=>$value){
                $fields[] = $field;
                $values[] = $this->quote_smart($value);
        }
         
        $sql = "INSERT INTO `{$tbl}` (`";
        $sql .= implode("`,`",$fields);
        $sql .= "`) VALUES ('";
        $sql .= implode("','",$values);
        $sql .= "')";

        $this->query($sql); 
        return $this->insert_id();
  }
  
  function update($tbl,$data,$where){    
      $cols = array();
        foreach($data as $field=>$value){
            $cols[] = "`{$field}`='".$this->quote_smart($value)."'"; 
        }
        
        $sql = "UPDATE `{$tbl}` SET";
        $sql .= implode(",",$cols);
        $sql .= " WHERE {$where}";  
        $this->query($sql);
        return true;
  }
  
  function delete($tbl,$where)
  {
            $this->query("DELETE FROM `{$tbl}` WHERE ".$where);    
  }
  
  function num_rows($query_id = 0)
  {
    return ($query_id) ? @mysql_num_rows($query_id) : false;
  }
  
  function fetch_object($query_id = 0){
    return ($query_id) ? @mysql_fetch_object($query_id) : false;
  }
  
  function mysql_escape($value){
  $value = str_replace(array('<script','</script>'),array("",""),$value);
  if( is_array($value) ) {
    return array_map( array(&$this,'quote_smart') , $value);
  } else {
    if( $value === '' ) $value = NULL;
        if (function_exists('mysql_real_escape_string'))
          return mysql_real_escape_string($value, $this->link_id);
          else return mysql_escape_string($value);
    }
  }

  function close(){
    global $tot_queries;
    $tot_queries += $this->num_queries;
    if ($this->link_id){
      if ($this->query_result) @mysql_free_result($this->query_result);
      return @mysql_close($this->link_id);
    } else return false;
  }

}

?>
