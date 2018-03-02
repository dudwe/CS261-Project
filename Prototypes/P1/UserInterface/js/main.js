"use strict"; //Strict Mode.

  //TODO LIMIT Favourites
  //TODO News
  //TODO LIMIT CHARS ON NEWS DESCRIPTION
  //TODO GRAPH OUTPUT
  //TODO LINK FAVOURITE WITH DB LAYER
  //TODO LINK POLL WITH PARSING LAYER
  //TODO ADD SHORT EXPLAINATION IN FAV MODAL OF WHAT POLING DOES AND WHAT FAVOURITING DOES.


$(document).ready(function() {
  $(".button-collapse").sideNav();
  $('.modal').modal();
  $("#fav-search, #query").val("");
  $("#company-no-result, #sector-no-result, #query-error-message").hide();
  $('ul.tabs').tabs();

  var timeout = 10000; //10 second timeout to AJAX responses.
  var waiting = false; //Flag for if the chatbot is waiting for a response.
  var speechEnabled = false; //Flag for if speech synthesis is enabled.
  var pollLoop = 1000 * 60; //Milliseconds between each notification poll.
  var maxFavourites = 15;

/*----------------------------------------------------------------------------*/
/* Initialisation*/

  function initialisation() {
    getFavourites();
    $("#fav-save").click(saveFavourites);
    $("#btn-send").click(submitQuery); //Redirect button click and ENTER to submitQuery function.

    //TODO REMOVE    
    displayGraphResponse("2019", "GRAPH");
    scrollToChatBottom();
    //TODO REMOVE
  }

/*----------------------------------------------------------------------------*/
/*Speech API*/

  const artyom = new Artyom();
  var support_speech = artyom.speechSupported();
  var support_recogn = artyom.recognizingSupported();
  console.log("Speech Synthesis Supported: " + support_speech);
  console.log("Speech Recognition Supported: " + support_recogn);

  var settings = {
    continuous: true,
    onResult: function(text) {
      console.log(text);
      if (waiting) {

      }
      else {
        $("#query").val($("#query").val() + text);
      }
    },
    onStart: function() {
      console.log("Dictation started by the user"); //###
    },
    onEnd: function() {
      console.log("Dictation stopped by the user"); //###
      $("#query").val($("#query").val() + "hello");
      checkQuery(); //###
    }
  };

  var UserDictation = artyom.newDictation(settings);

  function startRecognition() {
    UserDictation.start();
  }

  function stopRecognition() {
    UserDictation.stop();
  }

  //Speech Synthesis output if speech is enabled.
  function say(speech) {
    if (speechEnabled) {
      artyom.say(speech);
      console.log("VOICE OUTPUT: " + speech);
    }
  }

  //Toggles speech synthesis.
  $("#btn-speech").click(function() {
    var buttonText = $(this).children().first();
    if (buttonText.text() === "volume_off") {
      if (support_speech) { //Speech synthesis is supported.
        buttonText.text("volume_up");
        speechEnabled = true;
      }
      else { //Speech synthesis is not supported.
        Materialize.Toast.removeAll(); //Remove all current toast notifications.
        Materialize.toast("Speech synthesis is not supported in your browser.", 2000, "rounded"); //Notify that synthesis is not supported.
      }
    }
    else { //Mute volume.
      buttonText.text("volume_off");
      speechEnabled = false;
    }
  });

  //Toggles voice input.
  $("#btn-mic").click(function() {
    var buttonText = $(this).children().first();
    if (buttonText.text() === "mic") { //Start recording.
      if (support_recogn) { //Speech recognition is supported.
        buttonText.text("fiber_manual_record");
        $(this).addClass("btn-record");
        startRecognition();
      }
      else { //Speech recognition is not supported.
        Materialize.Toast.removeAll();
        Materialize.toast("Speech recognition is not supported in your browser.", 2000, "rounded");
      }
    }
    else { //Stop recording.
      buttonText.text("mic");
      $(this).removeClass("btn-record");
      stopRecognition();
    }
  });

/*----------------------------------------------------------------------------*/
/*Display*/

  //timestamp :: String || Date
  //borderType :: left-border || right-border | right-border-error
  //timestampType :: timestamp--left || timestamp--right
  //responseType :: chat-query || chat-response | chat-response-error
  //body :: jQuery Object || HTML || String
  function displayChatTemplate(timestamp, borderType, timestampType, responseType, body) {
    var template = $("<div class='chat-border'><div class='row timestamp-row'><p></p></div><div class='row'><div class='chat'></div></div></div>");
    var divider = $("<div class='response-divider'></div>");
    template.addClass(borderType);
    template.after(divider);
    template.find("p").addClass(timestampType).text("Received: " + timestamp);
    template.find(".chat").addClass(responseType).append(body);
    $("#chat-window").append(template);
  }

  //Adds a new text query to the chat window.
  function displayQuery(timestamp, query) {
    displayChatTemplate(timestamp, "left-border", "timestamp--left", "chat-query", "<p></p>");
    $(".chat-query:last > p").text(query);
  }

  //Displays an error in a red-themed chat response.
  function displayErrorResponse(timestamp, response) {
    displayChatTemplate(timestamp, "right-border-error", "timestamp--right", "chat-response-error", "<p></p>");
    $(".chat-response-error:last > p").text(response);
  }

  //Adds a new text reponse to the chat window.
  function displayGraphResponse(timestamp, response) {
    displayChatTemplate(timestamp, "right-border", "timestamp--right", "chat-response", "<p></p><canvas class='response-graph'></canvas>");
    $(".chat-response:last > p").text(response);
    createLineGraph(); //Displays a graph in the response.
  }

  //TODO
  //Gets a jQuery object for displaying information on a company stock.
  function getStockDisplay(stockName, sharePrice, pointChange, percentageChange) {
    var stockTable = $("<table class='centered table-no-format'><tbody><tr>" +
      "<td><p class='stock-name'></p></td><td>" +
      "<p class='stock-performance'><i class='stock-icon material-icons'></i>" +
      "<span class='stock-info-shareprice'></span><span class='stock-currency'>GBP</span>" +
      "<span class='stock-info-pointchange'></span><span class='stock-info-percentagechange'></span></p></td></tr></tbody></table>");

    if (pointChange > 0) { //Stock is rising.
      stockTable.find(".stock-performance").addClass("stock-rise").find(".stock-icon").text("keyboard_arrow_up");
    }
    else if (pointChange < 0) { //Stock is falling.
      stockTable.find(".stock-performance").addClass("stock-fall").find(".stock-icon").text("keyboard_arrow_down");
    }
    else { //Stock is neutral.
      stockTable.find(".stock-performance").addClass("stock-flat").find(".stock-icon").text("remove");
    }

    //Includes stock information.
    stockTable.find(".stock-name").text(stockName); //Include stock name.
    stockTable.find(".stock-info-shareprice").text(sharePrice);
    stockTable.find(".stock-info-pointchange").text(pointChange);
    stockTable.find(".stock-info-percentagechange").text(" (" + percentageChange + ")");

    return stockTable; //Return the jQuery object to be included in the chat window.
  }

  //TODO
  function getSpeechDisplay(speech) {
    var speechRow = $("<div class='m-0 p-0'><span class='quote'></span><span class='speech'></span><span class='quote'></span><div>");
    speechRow.find(".quote").text('"');
    speechRow.find(".speech").text(speech);
    return speechRow;
  }

  //TODO
  function displayResponseList(timestamp, response) {
    displayChatTemplate(timestamp, "right-border", "timestamp--right", "chat-response", "<p class='chat-pad'></p>");
    for (var i = 0; i < response.length; i++) {
      var responseRow = $("<div></div>").addClass("row chat-response-row").append(response[i]);
      $(".chat-response:last > .chat-pad").append(responseRow);
    }
  }

  //TODO
  //infoList :: [{info: String, value: String}]
  function getInfoListDisplay(infoList) {
    console.log(infoList);
    var infoTable = $("<table class='info-table bordered'></table>");
    for (var i = 0; i < infoList.length; i++) {
      var infoRow = $("<tr><td class='info-table-name'></td><td class='info-table-value'></td></tr>");
      infoRow.find(".info-table-name").text(infoList[i].info + ": ");
      infoRow.find(".info-table-value").text(infoList[i].value);
      infoTable.append(infoRow);
    }
    return infoTable;
  }

  //Shows the loading icon.
  function showLoading() {
    waiting = true;
    $("#chat-window").append("<div id='loader-div' class='row right'><div class='loader'></div></div>");
  }

  //Hides the loading icon.
  function hideLoading() {
    waiting = false;
    $("#loader-div").remove();
  }

/*----------------------------------------------------------------------------*/
/*Favourites*/

  var companyLog = new FavouriteLog(); //Creates an object to store company data.
  var sectorLog = new FavouriteLog(); //Creates an object to store sector data.

  //Company object to store company details
  function FavouriteLog() {
    this.list = []; //Original list of companies. list => [{id: String, ticker: String, name: String, fav: Bool, poll: Int, lastRec: Bool}]
    this.changeLog = []; //List of favourite changes. changeLog => [{id: String, fav: Bool}]
    this.addChange = function(newChange) { //newChange => {id: String, fav: Bool}
      this.changeLog.push(newChange); //Adds the new change to the list.
    };
    this.clearChanges = function() { //Removes all changes in the changelog.
      this.changeLog = [];
    };
  }

  //Adds a company to the data structure and favourite modal.
  //data :: {id: String, ticker: String, name: String, pollRate: Int, fav: Bool}
  companyLog.add = function(data) {
    this.list.push(data);
    addCompany(data.id, data.ticker, data.name, data.pollRate, data.fav);
  };

  companyLog.compareChanges = function() {
    var finalChangeLog = []; //List of all changes that differ from the stored list.
    for (var i = 0; i < this.changeLog.length; i++) {
      var index = this.list.findIndex(e => ((e.id === this.changeLog[i].id) && ((e.fav != this.changeLog[i].fav) || (e.pollRate != this.changeLog[i].pollRate)))); //Finds index where company occurs and favourite is different.
      if (index !== -1) { //If the favourite is different add it to the finalised list.
        finalChangeLog.push(this.changeLog[i]);
      }
    }
    return finalChangeLog;
  };

  companyLog.commitChanges = function() {
    for (var i = 0; i < this.changeLog.length; i++) {
      var index = this.list.findIndex(e => e.id === this.changeLog[i].id);  //Finds the index where the ID matches.
      if (index !== -1) { //If a matching ID is found, then update the favourite value.
        this.list[index].fav = this.changeLog[i].fav;
        this.list[index].pollRate = this.changeLog[i].pollRate;
      }
    }
    this.clearChanges();
  };

  //Gets the poll rate for a specific company.
  companyLog.getPollRate = function(companyID) {
    console.log("Getting poll rate for: " + companyID);
    var index = this.list.findIndex(function(e) {
      return companyID === e.id;
    });
    if (index !== -1) {
      console.log("Rate: " + this.list[index].pollRate);
      return this.list[index].pollRate;
    }
    else { //No ID match.
      console.log("ERR");
      return -1;
    }
  };

  //Sets the poll rate for a specific company.
  companyLog.setPollRate = function(companyID, pollRate) {
    console.log("Setting poll rate for: " + companyID + " to " + pollRate);
    var index = this.list.findIndex(function(e) {
      return companyID === e.id;
    });
    if (index !== -1) {
      this.list[index].pollRate = pollRate;
    }
  };

  //Adds a sector to the data structure and favourite modal.
  //data :: {id: String, name: String, fav: Bool}
  sectorLog.add = function(data) {
    this.list.push(data);
    addSector(data.id, data.name, data.fav);
  };

  sectorLog.compareChanges = function() {
    var finalChangeLog = []; //List of all changes that differ from the stored list.
    for (var i = 0; i < this.changeLog.length; i++) {
      var index = this.list.findIndex(e => (e.id === this.changeLog[i].id) && (e.fav != this.changeLog[i].fav)); //Finds index where sector occurs and favourite is different.
      if (index !== -1) { //If the favourite is different add it to the finalised list.
        finalChangeLog.push(this.changeLog[i]);
      }
    }
    return finalChangeLog;
  };

  sectorLog.commitChanges = function() {
    for (var i = 0; i < this.changeLog.length; i++) {
      var index = this.list.findIndex(e => e.id === this.changeLog[i].id);  //Finds the index where the ID matches.
      if (index !== -1) { //If a matching ID is found, then update the favourite value.
        this.list[index].fav = this.changeLog[i].fav;
      }
    }
    this.clearChanges();
  };

  //Gets a JSON object of all companies and sector and corresponding information.
  function getFavourites() {
    $.ajax({
      url: "../Database/scripts/get_favourites.php",
      dataType: "json",
      method: "POST",
      timeout: timeout,
      error: function(xhr, ajaxOptions, thrownError) {
        Materialize.Toast.removeAll(); //Remove all current toast notifications.
        Materialize.toast("Failed to retrieve Favourites.", 2000, "rounded"); //Notify that synthesis is not supported.
      },
      success: function(data) {
        console.log(data);
        for (var i = 0; i < data.companyList.length; i++) {
          companyLog.add(data.companyList[i]);
        }
        for (var i = 0; i < data.sectorList.length; i++) {
          sectorLog.add(data.sectorList[i]);
        }
      }
    });
  }

  //TODO
  //Sends a JSON object to the server of all companies and sectors which favourite value has been changed.
  function saveFavourites() {
    companyLog.clearChanges();
    sectorLog.clearChanges();
    changePollRates(); //Validates all poll rates, resets to original if invalid.

    $(".fav-table-body-company tr:not(#company-no-result)").each(function() { //For each company row in the modal.
      var id = $(this).find(".poll-rate-selector").attr("data-id");
      var pollRate = $(this).find(".poll-rate-selector").val();
      var fav = $(this).find(".fav-company-switch").prop("checked");
      console.log("MODAL // COMPANY ID: " + id + " : " + fav + " : " + pollRate);
      companyLog.addChange({id: id, fav: fav, pollRate: pollRate});
    });
    $(".fav-table-body-sector tr:not(#sector-no-result)").each(function() { //For each sector row in the modal.
      var id = $(this).find(".fav-sector-switch").attr("data-id");
      var fav = $(this).find(".fav-sector-switch").prop("checked");
      console.log("MODAL //SECTOR ID: " + id + " : " + fav);
      sectorLog.addChange({id: id, fav: fav});
    });

    var companyChanges = companyLog.compareChanges(); //List of company changes that are different from the original.
    var sectorChanges = sectorLog.compareChanges(); //List of sector changes that are different from the original.
    var sendData = {companyList: companyChanges, sectorList: sectorChanges};

    //Debugging
    console.log("COMPANY LOG\n" + companyLog.toString());
    console.log("SECTOR LOG\n" + sectorLog.toString());
    console.log(companyChanges);
    console.log(sectorChanges);
    console.log(sendData);

    $.ajax({
      url: "../Database/scripts/save_favourites.php",
      data: sendData,
      method: "POST",
      timeout: timeout,
      error: function(xhr, ajaxOptions, thrownError) {
        Materialize.Toast.removeAll(); //Remove all current toast notifications.
        Materialize.toast("Failed to save favourites.", 2000, "rounded");
        console.log("(ERROR) Save Favourites");
      },
      success: function(data) {
        Materialize.Toast.removeAll(); //Remove all current toast notifications.
        Materialize.toast("Saved favourites.", 2000, "rounded");
        console.log("(SUCCESS) Save Favourites");
        companyLog.commitChanges();
        sectorLog.commitChanges();
      }
    });
  }

  //Adds a company row to the favourites modal.
  function addCompany(id, ticker, name, pollRate, fav) {
    var tickerRow = "<td>" + ticker + "</td>";
    var nameRow = "<td>" + name + "</td>";
    var pollRow = "<td><input class='poll-rate-selector' data-id='" + id + "' ";
    pollRow += "type='number' min='0' max='1000' maxlength='4'";

    if (pollRate > 0) {
      pollRow += "value='" + pollRate + "'";
    }
    else {
      pollRow += "value='-'";
    }

    pollRow += "></td>";
    var favRow = "<td><div class='switch'><label><input data-id='";
    favRow += id +  "' class='fav-company-switch' type='checkbox'";
    if (fav) { favRow += " checked"; } //Marks the company as favourited.
    favRow += "><span class='lever'></span></label></div></td>";
    var companyRow = "<tr>" + tickerRow + nameRow + pollRow + favRow + "</tr>";
    $("#fav-company table tbody tr:last").after(companyRow); //Appends the company to the table.
  }

  //Adds a sector row to the favourites modal.
  function addSector(id, name, fav) {
    var nameRow = "<td>" + name + "</td>";
    var favRow = "<td><div class='switch'><label><input data-id='";
    favRow += id + "' class='fav-sector-switch' type='checkbox'";
    if (fav) { favRow += " checked"; } //Marks the sector as favourited.
    favRow += "><span class='lever'></span></label></div></td>";
    var sectorRow = "<tr>" + nameRow + favRow + "</tr>";
    $("#fav-sector table tbody tr:last").after(sectorRow); //Appends the sector to the table.
  }

  //Filters the company and sector list when a search query has been entered.
  $("#fav-search").keyup(function(event) {
    var search = $("#fav-search").val().toUpperCase(); //Search term.
    var company_noresult = true; //Flag for if search term matches no companies.
    var sector_noresult = true; //Flag for if search term matches no sectors.

    //Selects all rows in the company table which is not the 'no-result' row.
    $("#fav-company .fav-table tbody tr:not(#company-no-result)").each(function() {
      if ($(this).html().toUpperCase().indexOf(search) > -1) {
        $(this).fadeIn(); //Show row.
        company_noresult = false; //At least one company has matched.
      }
      else {
        $(this).fadeOut(); //Hide row.
      }
    });

    //Selects all rows in the sector table which is not the 'no-result' row.
    $("#fav-sector .fav-table tbody tr:not(#sector-no-result)").each(function() {
      if ($(this).html().toUpperCase().indexOf(search) > -1) {
        $(this).fadeIn(); //Show row.
        sector_noresult = false; //At least one sector has matched.
      }
      else {
        $(this).fadeOut(); //Hide row.
      }
    });

    if (company_noresult === true) {
      $("#company-no-result").show();
    }
    else {
      $("#company-no-result").hide();
    }

    if (sector_noresult === true) {
      $("#sector-no-result").show();
    }
    else {
      $("#sector-no-result").hide();
    }
  });

/*----------------------------------------------------------------------------*/
/*Notifications*/

  var poll = window.setInterval(pollNotifications, pollLoop); //Set pollNotifications to execute every minute.
  var pollCount = 0; //Number of notification polls checked.

  //TODO
  //Identifies which favourites need to be polled to the server then sends the AJAX request.
  function pollNotifications() {
    pollCount++;
    console.log("Poll Notifications (" + pollCount + ")");
    var notificationObj = []; //List of all companies to send notification polls for.
    for (var i = 0; i < companyLog.list.length; i++) {
      var company = companyLog.list[i];
      if ((company.fav === true) && (company.pollRate > 0)) {
        if (pollCount % company.pollRate === 0) { //If current time indicates favourite should be polled.
          notificationObj.push({id: company.id, lastRec: company.lastRec}); //TODO
        }
      }
    }

    //Don't send AJAX request if nothing needs polling.
    if (notificationObj.length === 0) { return; }

    console.log("NOTIFICATIONS");
    console.log(notificationObj);

    //Sends the notification requests to the server.
    $.ajax({
      url: "../ParsingAndProcessing/getNotifications.php", //TODO
      data: {notifications: notificationObj},
      method: "POST",
      timeout: timeout,
      error: function(xhr, ajaxOptions, thrownError) {
        console.log("No response from server for notifications.");
      },
      success: function(data) {
        //TODO
      }
    });
  }

  //Saves changes to company poll rates.
  function changePollRates() {
    console.log("Change Poll Rates");
    $(".poll-rate-selector").each(function(index, element) {
      var companyID = $(this).attr("data-id");
      var pollRate = $(this).val();
      var valid = validatePollRate(pollRate);
      if (!valid) { //Replace existing invalid poll rate with valid stored poll rate.
        pollRate = companyLog.getPollRate(companyID);
        $(this).val(pollRate);
      }
      console.log(companyID + " at rate " + pollRate + " is " + valid);
    });
  }

  //Validates a poll rate to ensure it is an integer between 0 and 1000 inclusive.
  function validatePollRate(pollRate) {
    if ($.isNumeric(pollRate) && Math.floor(pollRate) == (+pollRate)) {
      return (pollRate >= 0 && pollRate <= 1000);
    }
    else {
      return false;
    }
  }

/*----------------------------------------------------------------------------*/
/*News*/

  //TODO
  //Generates a jQuery object to display news stories.
  //newsArray :: [headline: String, url: String, description: String]
  function getNewsDisplay(newsArray) {
    var maxShownHeadlines = 1; //Number of headlines initially shown.
    var headlineCount = newsArray.length;
    var newsDisplay = $("<div class='news-table'></div>");


    for (var i = 0; i < newsArray.length; i++) {
      var article = newsArray[i];
      var headline = article.title;
      var url = article.link;
      var description = article.desc;
      var articleRow = $("<div class='news-row'><a class='headline tooltipped' data-position='top' ata-delay='50'></a><p class='headline-desc'></p></div>");
      console.log("HEADLINE: " + headline + " :: " + "URL: " + url);
      articleRow.find(".headline").text(headline);
      articleRow.find(".headline").attr("href", url);
      articleRow.find(".headline").attr("data-tooltip", url);
      articleRow.find(".headline-desc").text(description);
      articleRow.find(".tooltipped").tooltip({delay: 50});
      newsDisplay.append(articleRow);
    }

    //If number of news headlines more than maximum allowed to show then show more button.
    if (headlineCount > maxShownHeadlines) {
      var showMoreBtn = $("<button class='showMore' data-showmore='more'></button>");
      var moreCount = headlineCount - maxShownHeadlines;
      showMoreBtn.text("Show " + moreCount + " more...");
      newsDisplay.append(showMoreBtn); //Adds the show more button to the news display.
    }

    //Hide overflow headlines.
    $(newsDisplay).find("div").each(function(index) {
      if (index + 1 > maxShownHeadlines) {
        $(this).hide();
      }
    });

    newsDisplay.find(".showMore").click(function() {
      var op = $(this).attr("data-showmore");

      if (op === "more") { //Show All
        $(this).attr("data-showmore", "less");
        $(this).text("Show less...");
        $(this).parent().find("div").fadeIn();
      }
      else if (op === "less") { //Hide Overflow
        $(this).attr("data-showmore", "more");
        $(this).text("Show " + moreCount + " more...");
        $(this).parent().find("div").each(function(index) {
          if (index + 1 > maxShownHeadlines) {
            $(this).fadeOut();
          }
        });
      }
    });

    return newsDisplay;
  }

/*----------------------------------------------------------------------------*/
/*Query*/

  //Performs query length validation and submits query on ENTER press.
  $("#query").keyup(function(event) {
    if ($("#query").val().length > 250) { //Shows error text if exceeds 250 characters.
      $("#query-input").addClass("query-error");
      $("#query-error-message").html("Query exceeds 250 characters.").show();
    }
    else { //Hides the error text if less than 250 characters.
      $("#query-input").removeClass("query-error");
      $("#query-error-message").hide();
    }
    if (event.keyCode == 13) { //ENTER key.
      submitQuery();
    }
  });

  //Length validation for the query text field.
  function checkQuery() {
    if ($("#query").val().length > 250) { //Shows error text if exceeds 250 characters.
      $("#query-input").addClass("query-error");
      $("#query-error-message").html("Query exceeds 250 characters.").show();
    }
    else { //Hides the error text if less than 250 characters.
      $("#query-input").removeClass("query-error");
      $("#query-error-message").hide();
    }
  }

  //Submits a query to the web server. Handles potential errors.
  function submitQuery() {
    var query = $("#query").val(); //String value of the query.
    var length = query.length; //Characters in the query.

    if (length <= 250) {
      if (waiting === true) { //Query cannot send, as a response is expected from the previous query.
        $("#query-input").addClass("query-error");
        $("#query-error-message").text("Cannot send query, waiting for response.").show();
      }
      else if (length < 1) { //Query is empty.
        $("#query-input").addClass("query-error");
        $("#query-error-message").text("Cannot send query, please type something.").show();
      }
      else { //Valid query.
        var currentTime = new Date();
        displayQuery(currentTime.toUTCString(), query);
        $("#query").val("");
        showLoading();
        scrollToChatBottom();
        sendQuery(query);
      }
    }
  }

  //Animates a scroll to the bottom of the chat window.
  function scrollToChatBottom() {
    var height = 0;
    height = height < $("#chat-window")[0].scrollHeight ? $("#chat-window")[0].scrollHeight : 0;
    $("#chat-window").stop().animate({scrollTop: height}, 500);
  }

  //Sends a query to the web server, and waits for a response.
  function sendQuery(query) {
    $.ajax({
      url: "../Client/dialogflow.php",
      data: {"user_query": query},
      method: "POST",
      timeout: timeout,
      error: function(xhr, ajaxOptions, thrownError) {
        var currentTime = new Date();
        hideLoading(); //Hides the rotating loading animation.
        displayErrorResponse(currentTime.toUTCString(), "No response from server.");
      },
      success: function(data) {
        hideLoading(); //Hides the rotating loading animation.
        parseResponse(data);
      }
    });
  }

/*----------------------------------------------------------------------------*/
/*Graph*/

  //TODO
  //Creates the graph object to add to the chat window.
  function createLineGraph() {
    var ctx = $(".response-graph").get(-1).getContext("2d"); //Get context of the last canvas object.
    var lineGraph = new Chart(ctx, {
      type: 'line',
      data: {
          labels: ["2013", "2014", "2015", "2016", "2017", "2018"], //x-axis labels.
          datasets: [{
              label: "# of Votes", //Dataset label.
              data: [12, 19, 3, 5, 2, 14], //Data.
              borderColor: ["rgba(255, 0, 0, 0.8)"], //Line colour.
              borderWidth: 2, //Line width.
              fill: false, //Doesn't fill under the line.
              pointBorderWidth: 2
          }]
      },
      options: {
        scales: { yAxes: [{
          ticks: { beginAtZero: true },
          scaleLabel : { display: true, labelString: "Y-Axis Label" }
        }]},
        title: { display: true, text: "Hello World!"},
        legend: { display: false }
      }
    });
  }

/*----------------------------------------------------------------------------*/
/*Reponse Types*/

  //TODO
  function parseResponse(data) {
    console.log("Parsing Response");
    console.log(data);

    var timestamp = new Date().toUTCString();
    var json, speechRow, stockTable, infoRow, newsRow, graphRow;

    //Attempt to parse JSON response.
    try {
      json = JSON.parse(data);
    }
    catch(e) {
      fallBackError(timestamp);
      return;
    }

    //Response Properties
    var resolvedQuery = json.resolvedQuery;
    var intent = json.intentName;
    var speech = json.speech + " ";
    var stock = json.stocks;
    var dataset = json.dataset;

    switch (intent) {
      case "get_share_price":
        speech += dataset.SharePrice;
        speechRow = getSpeechDisplay(speech);
        stockTable = getStockDisplay(stock, dataset.SharePrice, dataset.PointChange, dataset.PercentChange);
        displayResponseList(timestamp, [speechRow, stockTable]);
        break;
      case "get_point_change":
        speech += dataset.PointChange;
        speechRow = getSpeechDisplay(speech);
        stockTable = getStockDisplay(stock, dataset.SharePrice, dataset.PointChange, dataset.PercentChange);
        displayResponseList(timestamp, [speechRow, stockTable]);
        break;
      case "percent_change":
        speech += dataset.PercentChange;
        speechRow = getSpeechDisplay(speech);
        stockTable = getStockDisplay(stock, dataset.SharePrice, dataset.PointChange, dataset.PercentChange);
        displayResponseList(timestamp, [speechRow, stockTable]);
        break;
      case "get_bid":
        speech += dataset.Bid;
        speechRow = getSpeechDisplay(speech);
        stockTable = getStockDisplay(stock, dataset.SharePrice, dataset.PointChange, dataset.PercentChange);
        displayResponseList(timestamp, [speechRow, stockTable]);
        break;
      case "get_offer":
        speech += dataset.Offer;
        speechRow = getSpeechDisplay(speech);
        stockTable = getStockDisplay(stock, dataset.SharePrice, dataset.PointChange, dataset.PercentChange);
        displayResponseList(timestamp, [speechRow, stockTable]);
        break;
      case "get_open":
        speech += dataset.Open;
        speechRow = getSpeechDisplay(speech);
        stockTable = getStockDisplay(stock, dataset.SharePrice, dataset.PointChange, dataset.PercentChange);
        displayResponseList(timestamp, [speechRow, stockTable]);
        break;
      case "get_close":
        speech += dataset.Close;
        speechRow = getSpeechDisplay(speech);
        stockTable = getStockDisplay(stock, dataset.SharePrice, dataset.PointChange, dataset.PercentChange);
        displayResponseList(timestamp, [speechRow, stockTable]);
        break;
      case "get_high":
        speech += dataset.High;
        speechRow = getSpeechDisplay(speech);
        stockTable = getStockDisplay(stock, dataset.SharePrice, dataset.PointChange, dataset.PercentChange);
        displayResponseList(timestamp, [speechRow, stockTable]);
        break;
      case "get_low":
        speech += dataset.Low;
        speechRow = getSpeechDisplay(speech);
        stockTable = getStockDisplay(stock, dataset.SharePrice, dataset.PointChange, dataset.PercentChange);
        displayResponseList(timestamp, [speechRow, stockTable]);
        break;
      case "get_revenue": //TODO TEST
        speech += dataset.Revenue;
        speechRow = getSpeechDisplay(speech);
        infoRow = getInfoListDisplay([
          {info: "Market Cap", value: dataset.MarketCap}
        ]);
        stockTable = getStockDisplay(stock, dataset.SharePrice, dataset.PointChange, dataset.PercentChange);
        displayResponseList(timestamp, [speechRow, stockTable, infoRow]);
        break;
      case "get_eps": //EPS, DivYield, PERatio
        speech += dataset["EPS"];
        speechRow = getSpeechDisplay(speech);
        infoRow = getInfoListDisplay([
          {info: "Dividend Yield", value: dataset.DivYield},
          {info: "Price-Earnings Ratio", value: dataset.PERatio}
        ]);
        displayResponseList(timestamp, [speechRow, infoRow]);
        break;
      case "get_volume": //TODO TEST
        speech += dataset.Volume;
        speechRow = getSpeechDisplay(speech);
        infoRow = getInfoListDisplay([
          {info: "Average Volume", value: dataset.AverageVol}
        ]);
        stockTable = getStockDisplay(stock, dataset.SharePrice, dataset.PointChange, dataset.PercentChange);
        displayResponseList(timestamp, [speechRow, stockTable, infoRow]);
        break;
      case "get_market_cap": //TODO DOESNT HAVE MARKET CAP IN JSON
        //speech += dataset[""] //TODO
        speechRow = getSpeechDisplay(speech);
        infoRow = getInfoListDisplay([
          {info: "Share Price", value: dataset.SharePrice},
          {info: "Shares in Issue", value: dataset.SharesInIssue},
          {info: "Volume", value: dataset.Volume}
        ]);
        displayResponseList(timestamp, [speechRow, infoRow]);
        break;
      case "get_div_yield": //TODO DOESNT HAVE DIV YIELD IN JSON
        //speech += dataset["divyield###"];
        speechRow = getSpeechDisplay(speech);
        infoRow = getInfoListDisplay([
          {info: "Earnings per Share" , value: dataset["EPS"]},
          {info: "Price-Earnings Ratio", value: dataset.PERatio},
          {info: "Volume", value: dataset.Volume}
        ]);
        displayResponseList(timestamp, [speechRow, infoRow]);
        break;
      case "get_average_vol": //TODO TEST
        speech += dataset.AverageVol;
        speechRow = getSpeechDisplay(speech);
        infoRow = getInfoListDisplay([
          {info: "Volume" , value: dataset.Volume}
        ]);
        displayResponseList(timestamp, [speechRow, infoRow]);
        break;
      case "get_pe_ratio": //TODO TEST NO PE RATIO
        //speech += dataset[""];
        speechRow = getSpeechDisplay(speech);
        infoRow = getInfoListDisplay([
          {info: "Dividend Yield", value: dataset.DivYield},
          {info: "Earnings per Share", value: dataset["EPS"]},
          {info: "Volume", value: dataset.Volume}
        ]);
        displayResponseList(timestamp, [speechRow, infoRow]);
        break;
      case "get_shares_in_issue": //TODO DOESNT INCLUDE SHARES IN ISSUE
        //speech += dataset[""];
        speechRow = getSpeechDisplay(speech);
        infoRow = getInfoListDisplay([
          {info: "Market Cap", value: dataset.MarketCap},
          {info: "Volume", value: dataset.Volume},
          {info: "Share Price", value: dataset.SharePrice}
        ]);
        displayResponseList(timestamp, [speechRow, infoRow]);
        break;
      case "get_news": //TODO
        speechRow = getSpeechDisplay(speech);
        //GET NEWS ROW
        displayResponseList(timestamp, [speechRow]);
        newsRow = getNewsDisplay(dataset);
        displayResponseList(timestamp, [speechRow, newsRow]);
        break;
      case "get_stock_performance": //TODO
        speechRow = getSpeechDisplay(speech);
        stockTable = getStockDisplay(stock, json.auxillary.SharePrice, json.auxillary.PointChange, json.auxillary.PercentChange);
        //GRAPH TODO
        displayResponseList(timestamp, [speechRow, stockTable]);
        break;
      case "get_sector_performance": //TODO
        break;
      case "get_buy_or_sell": //TODO
        var movingAverages = dataset.movingAverages;
        var technicalIndicators = dataset.technicalIndicators;
        var summary = dataset.Summary;
        speech = "Recommended: " + summary;
        speechRow = getSpeechDisplay(speech);
        displayResponseList(timestamp, [speechRow]);
        break;
      case "Input Error": //TODO
        displayErrorResponse(speech);
        break
      default:
        fallBackError(timestamp);
        return;
    }

    console.log(speech);
    say(speech); //Outputs the response using voice synthesis.
    scrollToChatBottom(); //Scrolls to bottom of the chat window.

  }

  //Error called if JSON is malformed or cannot identify intent.
  function fallBackError(timestamp) {
    var error = "Could not understand response.";
    displayErrorResponse(timestamp, error);
    console.log(error);
    say(error); //Outputs the response using voice synthesis.
    scrollToChatBottom(); //Scrolls to bottom of the chat window.
  }

/*----------------------------------------------------------------------------*/

  initialisation(); //Start up functions.

});
