<?php
    require_once __DIR__.'/vendor/autoload.php';

    use DialogFlow\Client;
    use DialogFlow\Model\Query;
    use DialogFlow\Method\QueryApi;
    
    if(isset($_POST['user_query'])) {
        $query = $_POST['user_query'];
        try {
            $client = new Client('f4bc3c425f1c4e6b9c52f21493decb19');
            $queryApi = new QueryApi($client);
        
            $meaning = $queryApi->extractMeaning($query, [
                'sessionId' => '1234567890',
                'lang' => 'en',
            ]);
            $response = new Query($meaning);
    
            echo $response->getResult()->getFulfillment()->getSpeech();
        } catch (\Exception $error) {
            echo $error->getMessage();
        }
    }
?>