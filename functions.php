<?php

function app_list(){
    return [
        "MEDIA" => [
            "DB" => MEDIA_DB_NAME,
            "HOST" => MEDIA_DB_HOST,
            "USER" => MEDIA_DB_USER,
            "PASS" => MEDIA_DB_PASS,
        ],
        "ADP" => [
            "DB" => DB_NAME,
            "HOST" => DB_HOST,
            "USER" => DB_USER,
            "PASS" => DB_PASS,
        ]
    ];
}

function write_commands(){
    return [
        "drop",
        "alter",
        "update",
        "insert",
        "create",
        "truncate",
        "replace",
        "delete",
    ];
}

// check if query is valid read query
function valid_read_query($query){
    $query = strtolower($query);
    foreach(write_commands() as $command){
        $command = $command . " ";
        if (strpos($query, $command) !== false) {
            return false;
        } 
    }
    return true;
}

function show_query($query){
    echo "<p><code class='show-query'>$query</code></p>";
}

function show_error($msg){

}

function show_warning($msg){
    
}

// show tables
function showTable($result) {
    $table = "<pre class='text-center'><table border='1' style='border-collapse: collapse; width: 100%;'>";
    $table .= "<thead><tr>";
    // get column names
    $columns = array_keys($result[0]);

    foreach($columns as $column) {
        $table .= "<th>$column</th>";
    }
    $table .= "</tr></thead>";
    $table .= "<tbody>";
    // get data
    foreach($result as $row) {
        $table .= "<tr>";
        foreach($columns as $column) {
            $table .= "<td>" . $row[$column] . "</td>";
        }
        $table .= "</tr>";
    }
    $table .= "</tbody></table></pre>";
    return $table;
}
