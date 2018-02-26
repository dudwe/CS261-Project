<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once("SentimentAnalyzer.php");

function analyse_headline_sentiment($headline) {

    $sat = new SentimentAnalyzerTest(new SentimentAnalyzer());

    $sat->trainAnalyzer("training/data.pos", "positive", 0);
    $sat->trainAnalyzer("training/data.neg", "negative", 0);

    $analysis = $sat->analyzeSentence($headline);

    echo "Sentence: " . $headline . 
        "<br>Sentiment: " . $analysis["sentiment"] .
        "<br>P(positive) = " . $analysis["accuracy"]["positivity"] .
        "<br>P(negative) = " . $analysis["accuracy"]["negativity"] .
        "<br>";

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
