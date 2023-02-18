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
        ],
        "AMI_VIR_INV" => [
            "DB" => AMI_INV_DB_NAME,
            "HOST" => AMI_INV_DB_HOST,
            "USER" => AMI_INV_DB_USER,
            "PASS" => AMI_INV_DB_PASS,
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
    echo "<p class='query-box'><code class='show-query'>$query</code></p>";
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

function call_chatgpt($msg,$model="text-davinci-003",$max_tokens = 100 , $temperature = 0.5){
    $curl = curl_init();
    $postfields = [
        'prompt' => $msg,
        'max_tokens' => $max_tokens,
        'temperature' => $temperature,
        // "explanation" => "none",
        // "format" => "json",
    ];
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.openai.com/v1/engines/$model/completions",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_POSTFIELDS => json_encode($postfields),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer '.CHATGPT_KEY
        ),
    ));

    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);
    return json_Decode($response);
}
