<?php

function app_list(){
    global $databaseConfigs;
    return $databaseConfigs;
}

function write_commands(){
    return [
        "INSERT",
        "REPLACE",
        "MERGE",
        "TRUNCATE",
        "CREATE",
        "ALTER",
        "SET",
        "CALL",
        "GRANT",
        "REVOKE",
        "UPDATE",
        "DROP",
        "DELETE",
        "UPSERT",
        "RENAME",
        "COMMIT",
        "ROLLBACK"
    ];
}

function read_commands(){
    return [
        "SELECT",
        "SHOW",
        "DESCRIBE",
        "EXPLAIN",
        "HELP",
    ];
}

// check if query is valid read query
function valid_read_query($query){
    $queryType = query_type($query);
    $allowedKeywords = read_commands();
    $restrictedKeywords = write_commands();
    return in_array($queryType, $allowedKeywords) && !in_array($queryType, $restrictedKeywords);
}

function query_type($query){
    $queryType = strtoupper(substr(trim($query), 0, strpos(trim($query), ' ')));
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

function chatgpt_api($prompt,$model="gpt-3.5-turbo",$max_tokens = 200 , $temperature = 0.5){
    $apiKey = CHATGPT_KEY;
    $url = 'https://api.openai.com/v1/chat/completions';

    $headers = array(
        "Authorization: Bearer {$apiKey}",
        "Content-Type: application/json"
    );

    // Define messages
    $messages = array();
    $messages[] = array("role" => "user", "content" => $prompt);

    // Define data
    $data = array();
    $data["model"] = $model;
    $data["messages"] = $messages;
    $data["max_tokens"] = $max_tokens;

    // init curl
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    $result = curl_exec($curl);
    if (curl_errno($curl)) {
         $error_msg = 'Error:' . curl_error($curl);
         curl_close($curl);
         return [
            "success" => false,
            "text" => null,
            "response" => null,
            "error_message" =>$error_msg,
        ];
    } else {
        curl_close($curl);
        $response_array = json_decode($result,true);
        if(isset($response_array['choices'])){
            $completion = $response_array['choices'][0]['message']['content'];
            return [
                "success" => true,
                "text" => $completion,
                "response" => $response_array,
                "error_message" =>"",
            ];
        }
        else{
            return [
                "success" => false,
                "text" => "",
                "response" => $response_array,
                "error_message" =>$response_array['error']['message'],
            ];
        }
    }
}