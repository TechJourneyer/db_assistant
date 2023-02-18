<?php
require 'vendor/autoload.php';
 
// Using Medoo namespace.
use Medoo\Medoo;

require_once 'config.php';
require_once ROOTDIR.'Log_master.php';

$logger = new Log_master();
$logger->auto_delete_log(5);
// $logger->display_logs(); // ??


// Specify the table and columns to retrieve
$table = "user";
$columns = ["user_name", "full_name"];

$input = trim($_POST['prompt']);
$app = trim($_POST['app']);

$creds = app_list();
$selected_app_creds = $creds[$app];

// Connect to the database
$database = new Medoo([
    'database_type' => 'mysql',
    'database_name' => $selected_app_creds['DB'],
    'server' => $selected_app_creds['HOST'],
    'username' => $selected_app_creds['USER'],
    'password' => $selected_app_creds['PASS'],
]);

// Execute the query

$results = $database->query("SHOW TABLES")->fetchAll();
$table_list_array  = array_column($results, 0);
$table_list = implode(",",$table_list_array);

$logger->info("input : $input");

$prompt = "Table names : $table_list \n";
$prompt .= "Query : $input \n";
$prompt .= "Show matching table name in comma separated format for above query \n";


$response  = call_chatgpt($prompt);
if(isset($response->choices[0]->text)){
    $relevant_tables = $response->choices[0]->text;
    $logger->info("table_list : $relevant_tables");

    $relevant_tables_array = explode(",",$relevant_tables);
    $tables = array_map('trim', $relevant_tables_array);
    $prompt_for_query = "Table names with columns \n";
    if(empty($tables)){
        echo "No relevant tables found";
    }
    foreach ($tables as  $table) {
        $results = $database->query("SHOW COLUMNS FROM $table")->fetchAll();
        $columns_array  = array_column($results, 0);
        $columns = implode(",",$columns_array);
        $prompt_for_query .= "$table ($columns) \n";
    }

    $prompt_for_query .= "Write a plain mysql query to get results for : $input \n";

    $query_response  = call_chatgpt($prompt_for_query);
    if(isset($query_response->choices[0]->text)){
        $auto_generated_query = $query_response->choices[0]->text;
        if (strpos(strtolower($auto_generated_query), 'limit') === false) {
            $auto_generated_query .= " limit 100";
        }
        $auto_generated_query = str_replace(";",   "",$auto_generated_query);
        // echo PHP_EOL . "QUERY : " . $auto_generated_query . PHP_EOL;
        $logger->warning("Final query : $auto_generated_query");
        show_query($auto_generated_query);
        if(valid_read_query($auto_generated_query)){
            $query_result = $database->query($auto_generated_query)->fetchAll(PDO::FETCH_ASSOC);
            if(!empty($query_result)){
                echo showTable($query_result);
            }
            else{
                echo "No results found in database";
            }
        }
    }
}
else{
    $logger->warning("Api response failed");
    echo "No results found";
}