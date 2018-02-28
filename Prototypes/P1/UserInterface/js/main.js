"use strict"; //Strict Mode.

$(document).ready(function() {
  $(".button-collapse").sideNav();
  $('.modal').modal();
  $("#fav-search, #query").val("");
  $("#company-no-result, #sector-no-result, #query-error-message").hide();
  $('ul.tabs').tabs();

  var timeout = 10000; //10 second timeout to AJAX responses.
  var waiting = false; //Flag for if the chatbot is waiting for a response.
  var speechEnabled = false; //Flag for if speech synthesis is enabled.

/*----------------------------------------------------------------------------*/
/* Initialisation*/

  function initialisation() {
    //Testing chat queries and commands.
    displayQuery("12/02/18 13:13:09", "What is the spot price of Apple?");
    displayResponse("12/02/18 13:13:24", "The spot price of Apple is £2.30");
    displayQuery("02/12/18 14:45:59", "A very extremely long query to test how the CSS responds to the long length of a query. It should not exceed 75% of the chatbot width and wrap into multiple lines.");
    displayResponse("02/12/18 14:46:08", "A very extremely long query to test how the CSS responds to the long length of a query. It should not exceed 75% of the chatbot width and wrap into multiple lines.");


    /*-//TODO-REMOVE--*/
    companyLog.add({id: "CHEF", ticker: "CHEF", name: "My name is chef", pollRate: 2, fav: false});
    companyLog.add({id: "SPAG", ticker: "SPAG", name: "Somebody toucha my spaghet", pollRate: 15, fav: true});
    sectorLog.add({id: "1", name: "Banks", fav: false});
    sectorLog.add({id: "2", name: "Financial Services", fav: true});
    /*-//TODO-REMOVE--*/

    getFavourites();

    //TODO REMOVE AFTER IMPLEMENTATION #########################################
    //Add company favourite changes to the corresponding array when detected.
    $(".fav-company-switch").click(function() {
      var companyID = $(this).attr("data-id"); //Gets the ID attribute of the company.
      var fav = $(this).prop("checked");
      companyLog.addChange({id: companyID, fav: fav}); //Creates a new object for the change.
      console.log("COMPANY CHANGE: " + companyID + " : " + fav);
      setPollChangeAvailability(companyID, fav);
    });
    //Add sector favourite changes to the corresponding array when detected.
    $(".fav-sector-switch").click(function() {
      var sectorID = $(this).attr("data-id"); //Gets the ID attribute of the sector.
      var fav = $(this).prop("checked");
      sectorLog.addChange({id: sectorID, fav: fav}); //Creates a new object for the change.
      console.log("SECTOR CHANGE: " + sectorID + " : " + fav);
    });
    //TODO REMOVE ##############################################################

    $("#fav-save").click(saveFavourites);
    $("#btn-send").click(submitQuery); //Redirect button click and ENTER to submitQuery function.

    displayErrorResponse("2018", "ERROR");
    displayGraphResponse("2019", "GRAPH");

    $(".chat-response:last").append(getStockDisplay("Apple Industries", 291.87, 32.45, 1.98));


    //TODO
    var response1 = "hello";
    var response2 = getStockDisplay("Apple Industries", 291.87, 32.45, 1.98);
    var responseFinal = [response1, response2];
    displayResponseList("TIMESTAMP", responseFinal);

  }

/*----------------------------------------------------------------------------*/
/*Speech API*/

  var artyom = new Artyom();
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
    var template = $("<div class='chat-border'><div class='row timestamp-row'><p></p></div>"
      + "<div class='row'><div class='chat'></div></div></div>"
      + "<div class='response-divider'></div>");
    template.find(".chat-border").addClass(borderType);
    template.find("p").addClass(timestampType).text("Received: " + timestamp);
    template.find(".chat").addClass(responseType).append(body);
    $("#chat-window").append(template);
  }

  //Adds a new text query to the chat window.
  function displayQuery(timestamp, query) {
    displayChatTemplate(timestamp, "left-border", "timestamp--left", "chat-query", "<p></p>");
    $(".chat-query:last p").text(query);
  }

  //Adds a new text reponse to the chat window.
  function displayResponse(timestamp, response) {
    displayChatTemplate(timestamp, "right-border", "timestamp--right", "chat-response", "<p></p>");
    $(".chat-response:last p").text(response);
    say(response);
  }

  //Displays an error in a red-themed chat response.
  function displayErrorResponse(timestamp, response) {
    displayChatTemplate(timestamp, "right-border-error", "timestamp--right", "chat-response-error", "<p></p>");
    $(".chat-response-error:last p").text(response);
    say("Error. " + response);
  }

  //Adds a new text reponse to the chat window.
  function displayGraphResponse(timestamp, response) {
    displayChatTemplate(timestamp, "right-border", "timestamp--right", "chat-response", "<p></p><canvas class='response-graph'></canvas>");
    $(".chat-response:last p").text(response);
    createLineGraph(); //Displays a graph in the response.
    say(response); //Says the response using speech synthesis.
  }

  //TODO
  //Gets a jQuery object for displaying information on a company stock.
  function getStockDisplay(stockName, stock1, stock2, stock3) {
    var stockTable = $("<table class='centered table-no-format'><tbody><tr>"
      + "<td><p class='stock-name'></p></td><td>"
      + "<p class='stock-performance'><i class='stock-icon material-icons'></i>"
      + "<span class='stock-info-1'></span><span class='stock-currency'>GBP</span>"
      + "<span class='stock-info-2'></span><span class='stock-info-3'></span></p></td></tr>");

    var valToDetermineRiseFallFlat = -3; //TODO
    if (valToDetermineRiseFallFlat > 0) { //Stock is rising.
      stockTable.find(".stock-performance").addClass("stock-rise").find(".stock-icon").text("keyboard_arrow_up");
    }
    else if (valToDetermineRiseFallFlat < 0) { //Stock is falling.
      stockTable.find(".stock-performance").addClass("stock-fall").find(".stock-icon").text("keyboard_arrow_down");
    }
    else { //Stock is neutral.
      stockTable.find(".stock-performance").addClass("stock-flat").find(".stock-icon").text("remove");
    }

    //Includes stock information.
    stockTable.find(".stock-name").text(stockName); //Include stock name.
    stockTable.find(".stock-info-1").text(stock1);
    stockTable.find(".stock-info-2").text(stock2);
    stockTable.find(".stock-info-3").text(" (" + stock3 + "%)");

    return stockTable; //Return the jQuery object to be included in the chat window.
  }

  //TODO
  function displayResponseList(timestamp, response) {
    displayChatTemplate(timestamp, "right-border", "timestamp--right", "chat-response", "<p></p>");
    for (var i = 0; i < response.length; i++) {
      var responseRow = $("<div></div>").addClass("row chat-response-row").append(response[i]);
      $(".chat-response:last p").append(responseRow);
    }
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
      var index = this.changeLog.findIndex(function(e) { //Find a previous occurence of a change for the company.
        return e.id === newChange.id;
      });
      if (index !== -1) { //If a previous occurence is found remove the previous occurence from the changelog.
        this.changeLog.splice(index, 1);
      }
      this.changeLog.push(newChange); //Adds the new change to the list.
    };
    this.clearChanges = function() { //Removes all changes in the changelog.
      this.changeLog = [];
    };
    this.compareChanges = function() {
      var finalChangeLog = []; //List of all changes that differ from the stored list.
      for (var i = 0; i < this.changeLog.length; i++) {
        var index = this.list.findIndex(e => (e.id === this.changeLog[i].id) && (e.fav !== this.changeLog[i].fav)); //Finds index where company occurs and favourite is different.
        if (index !== -1) { //If the favourite is different add it to the finalised list.
          finalChangeLog.push(this.changeLog[i]);
        }
      }
      return finalChangeLog;
    };
    this.commitChanges = function() {
      for (var i = 0; i < this.changeLog.length; i++) {
        var index = this.list.findIndex(e => e.id === this.changeLog[i].id);  //Finds the index where the ID matches.
        if (index !== -1) { //If a matching ID is found, then update the favourite value.
          this.list[index].fav = this.changeLog[i].fav;
        }
      }
      this.changeLog = []; //Reset changes.
    };
    this.toString = function() { //For debugging.
      var output = "";
      for (var i = 0; i < this.changeLog.length; i++) {
        output += "ID: " + this.changeLog[i].id + " // Fav: " + this.changeLog[i].fav + "\n";
      }
      return output;
    };
  }

  //TODO
  //data :: {id: String, ticker: String, name: String, pollRate: Int, fav: Bool}
  companyLog.add = function(data) {
    this.list.push(data); //TODO
    addCompany(data.id, data.ticker, data.name, data.pollRate, data.fav);
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
  }

  //Sets the poll rate for a specific company.
  companyLog.setPollRate = function(companyID, pollRate) {
    console.log("Setting poll rate for: " + companyID + " to " + pollRate);
    var index = this.list.findIndex(function(e) {
      return companyID === e.id;
    });
    if (index !== -1) {
      this.list[index].pollRate = pollRate;
    }
  }

  //TODO
  //data :: {id: String, name: String, fav: Bool}
  sectorLog.add = function(data) {
    this.list.push(data); //TODO
    addSector(data.id, data.name, data.fav);
  };

  //TODO
  //Gets a JSON object of all companies and sector and corresponding information.
  function getFavourites() {
    $.ajax({
      url: "https://www.google.com/fakepage.php", //###change to php file later.
      data: null,
      dataType: "json",
      method: "POST",
      timeout: timeout,
      error: function(xhr, ajaxOptions, thrownError) {
        Materialize.Toast.removeAll(); //Remove all current toast notifications.
        Materialize.toast("Failed to retrieve Favourites.", 2000, "rounded"); //Notify that synthesis is not supported.
      },
      success: function(data) {
        data.companyList.forEach(function(d) { //Adds the list of companies to the log.
          companyLog.add(data);
        });
        data.sectorList.forEach(function(d) { //Adds the list of sectors to the log.
          companyLog.add(data);
        });
        //Add company favourite changes to the corresponding array when detected.
        $(".fav-company-switch").click(function() {
          var companyID = $(this).attr("data-id"); //Gets the ID attribute of the company.
          var fav = $(this).prop("checked");
          companyLog.addChange({id: companyID, fav: fav}); //Creates a new object for the change.
          console.log("COMPANY CHANGE: " + companyID + " : " + fav);
          setPollChangeAvailability(companyID, fav);
        });
        //Add sector favourite changes to the corresponding array when detected.
        $(".fav-sector-switch").click(function() {
          var sectorID = $(this).attr("data-id"); //Gets the ID attribute of the sector.
          var fav = $(this).prop("checked");
          sectorLog.addChange({id: sectorID, fav: fav}); //Creates a new object for the change.
          console.log("SECTOR CHANGE: " + sectorID + " : " + fav);
        });
      }
    });
  }

  //TODO
  //Sends a JSON object to the server of all companies and sectors which favourite value has been changed.
  function saveFavourites() {
    var companyChanges = companyLog.compareChanges(); //List of company changes that are different from the original.
    var sectorChanges = sectorLog.compareChanges(); //List of sector changes that are different from the original.
    var sendData = {companyList: companyChanges, sectorList: sectorChanges};
    changePollRates(); //TODO

    //Debugging
    console.log("COMPANY LOG\n" + companyLog.toString());
    console.log("SECTOR LOG\n" + sectorLog.toString());
    console.log(companyChanges);
    console.log(sectorChanges);
    console.log(sendData);

    $.ajax({
      url: "https://www.google.com/fakepage.php", //###TODO change to php file later.
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
    if (!fav) {
      pollRow += " disabled";
    }

    pollRow += " type='text'></td>"
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

  var poll = window.setInterval(pollNotifications, 1000 * 60); //Set pollNotifications to execute every minute.
  var pollCount = 0; //Number of notification polls checked.

  //TODO
  //Identifies which favourites need to be polled to the server then sends the AJAX request.
  function pollNotifications() {
    console.log("Poll Notifications (" + ++pollCount + ")");
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

  //Allows only favourited companies' poll rate to be changed.
  function setPollChangeAvailability(companyID, fav) {
    $("*[data-id=" + companyID + "].poll-rate-selector").prop("disabled", !fav);
  }

  //Saves changes to company poll rates.
  function changePollRates() {
    console.log("Change Poll Rates");
    $(".poll-rate-selector").each(function(index, element) {
      var companyID = $(this).attr("data-id");
      var pollRate = $(this).val();
      var valid = validatePollRate(pollRate);
      if (valid) { //Sets the valid poll rate in the company log.
        companyLog.setPollRate(companyID, pollRate);
      }
      else { //Replace existing invalid poll rate with valid stored poll rate.
        pollRate = companyLog.getPollRate(companyID);
        $(this).val(pollRate);
      }
      console.log(companyID + " at rate " + pollRate + " is " + valid);
    });
  }

  //Validates a poll rate to ensure it is an integer between 0 and 1000 inclusive.
  function validatePollRate(pollRate) {
    if ($.isNumeric(pollRate) && Math.floor(pollRate) == +pollRate) {
      return (pollRate >= 0 && pollRate <= 1000)
    }
    else {
      return false;
    }
  }

/*----------------------------------------------------------------------------*/
/*News*/

//URL
//HEADER

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
      data: {user_query: query},
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
    console.log("Parsing Response"); //###
    var currentTime = new Date();
    displayResponse(currentTime.toUTCString(), data);
  }

  /*[SharePrice] => PointChange, PercentChange, Bid, Offer, Open, Close, High, Low
  [PointChange] => SharePrice, PercentChange, Bid, Offer, Open, Close, High, Low
  [PercentChange] => SharePrice, PointChange, Bid, Offer, Open, Close, High, Low
  [Bid] => SharePrice, PointChange, PercentChange, Offer, Open, Close, High, Low
  [Offer] => SharePrice, PointChange, PercentChange, Bid, Open, Close, High, Low
  [High] => SharePrice, PointChange, PercentChange, Bid, Offer, Open, Close, Low
  [Low] => SharePrice, PointChange, PercentChange, Bid, Offer, Open, Close, High
  [Open] => SharePrice, PointChange, PercentChange, Bid, Offer, Close, High, Low
  [Close] => SharePrice, PointChange, PercentChange, Bid, Offer, Open, High, Low
  [VolTotal] => TradePrice, TradeVol, SharesInIssue, SharePrice
  [TradePrice] => PointChange, PercentChange, Bid, Offer, Open, Close, High, Low
  [TradeVol] => PointChange, PercentChange, Bid, Offer, Open, Close, High, Low, TradePrice
  [PreviousSharePrice ] =>  PointChange, PercentChange, TradePrice
  [SharesInIssue] =>  MarketCap, VolTotal, SharePrice
  [MarketCap] => SharePrice, SharesInIssue, VolTotal, SharePrice
  [PERatio] =>  DivPerShare, DivYield, DivCover, EPS, TradePrice
  [DivPerShare] => DivYield, DivCover, EPS, PERatio, TradePrice
  [DivYield] => DivPerShare, DivCover, EPS, PERatio, TradePrice
  [DivCover] => DivPerShare, DivYield, EPS, PERatio, TradePrice
  [EPS] => DivPerShare, DivYield, DivCover, PERatio, TradePrice*/

/*----------------------------------------------------------------------------*/

  initialisation(); //Start up functions.

});
