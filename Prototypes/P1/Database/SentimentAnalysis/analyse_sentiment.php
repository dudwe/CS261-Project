<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once("SentimentAnalyzer.php");

function teach() {

    static $sentiment_analyser;

    if ($sentiment_analyser === NULL) {
        $sentiment_analyser = new SentimentAnalyzerTest(new SentimentAnalyzer());

        $sentiment_analyser->trainAnalyzer(__DIR__."/training/data.pos", 0);
        $sentiment_analyser->trainAnalyzer(__DIR__."/training/data.neg", 0);
    }

    return $sentiment_analyser;

}



function analyse_headline_sentiment($headline) {

    $sat = new SentimentAnalyzerTest(new SentimentAnalyzer());

    $sat->trainAnalyzer(__DIR__."/training/data.pos", "positive", 0);
    $sat->trainAnalyzer(__DIR__."/training/data.neg", "negative", 0);

    $analysis = $sat->analyzeSentence($headline);

    return $analysis;

}

function analyse_article_sentiment($article) {

    $sat = new SentimentAnalyzerTest(new SentimentAnalyzer());

    $sat->trainAnalyzer("training/data.pos", "positive", 0);
    $sat->trainAnalyzer("training/data.neg", "negative", 0);

    $analysis = $sat->analyzeDocument($article);

    echo "Sentiment: " . $analysis["sentiment"] .
        "<br>P(positive) = " . $analysis["accuracy"]["positivity"] .
        "<br>P(negative) = " . $analysis["accuracy"]["negativity"] .
        "<br>";

    return $analysis;

}

?>
