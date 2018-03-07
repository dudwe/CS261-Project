"use strict"; //Strict Mode.

  //TODO LINK FAVOURITE WITH DB LAYER
  //TODO LINK POLL WITH PARSING LAYER
  //TODO TEST VOICE INPUT
  //###TODO### INDICATES USING DROPDOWNS

$(document).ready(function() {
  $(".button-collapse").sideNav();
  $('.modal').modal();
  $("#fav-search, #query").val("");
  $("#company-no-result, #sector-no-result, #query-error-message").hide();
  $('ul.tabs').tabs();

  var timeout = 10000; //10 second timeout to AJAX responses.
  var waiting = false; //Flag for if the chatbot is waiting for a response.
  var speechEnabled = false; //Flag for if speech synthesis is enabled.
  var maxFavourites = 10; //Maximum number of favourites.

  var pollMin = 1000 * 60; //1 minute in Milliseconds.
  var poll5Min = window.setInterval(pollNotifications.bind(null, "5 Minutes"), pollMin * 5); //5 Mins => 5 Mins
  var poll15Min = window.setInterval(pollNotifications.bind(null, "15 Minutes"), pollMin * 7.5); //15 Mins => 7.5 Mins
  var pollHour = window.setInterval(pollNotifications.bind(null, "1 Hour"), pollMin * 20); //1 Hour => 15 Mins
  var pollDay = window.setInterval(pollNotifications.bind(null, "1 Day"), pollMin * 60 * 3); //1 Day => 3 Hour

/*----------------------------------------------------------------------------*/
/* Initialisation*/

  //Initialisation Operations.
  function initialisation() {
    getFavourites(); //Gets list of all companies and sectors.
    $("#fav-save").click(saveFavourites);
    $("#btn-send").click(submitQuery); //Redirect button click and ENTER to submitQuery function.

    var timestamp = new Date().toUTCString();
    displayQuery(getFormattedDate(timestamp), "Trader ChatBot Prototype P1");
    displayResponseList(getFormattedDate(timestamp), ["Response JSON is output in console btw. FOR TESTING"]);

    //Dropdown Choices
    var dropdownArr = ["What is the share price for ",
      "What is the point change of ",
      "What is the percentage change of ",
      "What is the bid for ",
      "What is the offer for ",
      "What is the open for ",
      "What is the close for ",
      "What is the low for ",
      "What is the high for ",
      "What is the revenue of ",
      "What is the EPS of ",
      "What is the volume of ",
      "What is the average volume ",
      "What is the market cap of ",
      "What is the dividend yield of ",
      "What is the PE Ratio of ",
      "How many shares in issue for ",
      "Any news on ",
      "Performance for ",
      "Conversion rate of USD to ",
      "Conversion rate of GBP to ",
      "Conversion rate of Euro to "];

    $("#query").materialize_autocomplete({
      limit: 5,
      multiple: { enable: false },
      dropdown: {
        el: '#singleDropdown',
        itemTemplate: '<li class="ac-item" data-id="<%= item.id %>" data-text=\'<%= item.text %>\'><a href="javascript:void(0)"><%= item.highlight %></a></li>'
      },
      getData: function(value, callback) {
        var data  = [];
        for (var i = 0; i < dropdownArr.length; i++) {
          var id = "drop-" + i; //Loops through all dropdown choices.
          if (dropdownArr[i].toUpperCase().includes(value)) {
            var highlight = "<strong>" + dropdownArr[i] + "</strong>";
            data.push({id: id, text: dropdownArr[i], highlight: highlight});
          }
        }
        callback(value, data);
      },
      onSelect: function(item) {
        $("#query").focus(); //Puts focus on the query input.
      }
    });

    $("#query").focus(); //Puts focus on the query input.
  }

/*----------------------------------------------------------------------------*/
/*Speech API*/

  const artyom = new Artyom();
  var support_speech = artyom.speechSupported();
  var support_recogn = artyom.recognizingSupported();
  artyom.initialize({
    lang: "en-GB",
    debug: true
  });
  console.log("Speech Synthesis Supported: " + support_speech);
  console.log("Speech Recognition Supported: " + support_recogn);

  //TODO
  var settings = {
    continuous: true,
    onStart: function() {
      console.log("Dictation started by the user");
    },
    onEnd: function() {
      console.log("Dictation Ended.");
    },
    onResult: function(text) {
      console.log("Dictation Result: " + text);
      $("#query").val(text);
      checkQuery();
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
    var buttonText = $("#mic-text");
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
  function displayChatTemplate(timestamp, borderType, timestampType, responseType, body, timestampPrefix) {
    var template = $("<div class='chat-border'><div class='row timestamp-row'><p></p></div><div class='row'><div class='chat'></div></div></div>");
    var divider = $("<div class='response-divider'></div>");
    template.addClass(borderType);
    template.after(divider);
    template.find("p").addClass(timestampType).text(timestampPrefix + timestamp);
    template.find(".chat").addClass(responseType).append(body);
    $("#chat-window").append(template);
  }

  //Adds a new text query to the chat window.
  function displayQuery(timestamp, query) {
    displayChatTemplate(timestamp, "left-border", "timestamp--left", "chat-query", "<p></p>", "Sent: ");
    $(".chat-query:last > p").text(query);
  }

  //Displays an error in a red-themed chat response.
  function displayErrorResponse(timestamp, response) {
    displayChatTemplate(timestamp, "right-border-error", "timestamp--right", "chat-response-error", "<p></p>", "Received: ");
    $(".chat-response-error:last > p").text(response);
  }

  //Adds a new text reponse to the chat window.
  function displayGraphResponse(timestamp, response) {
    displayChatTemplate(timestamp, "right-border", "timestamp--right", "chat-response", "<p></p><canvas class='response-graph'></canvas>", "Received: ");
    $(".chat-response:last > p").text(response);
    createLineGraph(); //Displays a graph in the response.
  }

  //Gets a jQuery object for displaying information on a company stock.
  function getStockDisplay(stockName, sharePrice, pointChange, percentageChange) {
    var stockTable = $("<table class='centered table-no-format'><tbody><tr>" +
      "<td><p class='stock-name'></p></td><td>" +
      "<p class='stock-performance'><i class='stock-icon material-icons'></i>" +
      "<span class='stock-info-shareprice tooltipped' data-position='bottom' data-delay='50'></span><span class='stock-currency'>GBP</span>" +
      "<span class='stock-info-pointchange tooltipped' data-position='bottom' data-delay='50'></span>" +
      "<span class='stock-info-percentagechange tooltipped' data-position='bottom' data-delay='50'></span></p></td></tr></tbody></table>");

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
    stockTable.find(".stock-info-shareprice").text(sharePrice).attr("data-tooltip", "Share Price");
    stockTable.find(".stock-info-pointchange").text(pointChange).attr("data-tooltip", "Point Change");
    stockTable.find(".stock-info-percentagechange").text(" (" + percentageChange + ")").attr("data-tooltip", "Percentage Change");
    stockTable.find(".tooltipped").tooltip({delay: 50});
    return stockTable; //Return the jQuery object to be included in the chat window.
  }

  //Gets a jQuery object for a highlighted speech response row.
  function getSpeechDisplay(speech) {
    //var speechRow = $("<div class='m-0 p-0'><span class='quote'></span><span class='speech'></span><span class='quote'></span><div>");
    var speechRow = $("<div class='m-0 p-0'><span class='speech'></span><div>");
    //speechRow.find(".quote").text('"');
    speechRow.find(".speech").text(speech);
    return speechRow;
  }

  //Displays a list of response rows into the chat template.
  function displayResponseList(timestamp, response) {
    displayChatTemplate(timestamp, "right-border", "timestamp--right", "chat-response", "<p class='chat-pad'></p>", "Received: ");
    for (var i = 0; i < response.length; i++) {
      var responseRow = $("<div></div>").addClass("row chat-response-row").append(response[i]);
      $(".chat-response:last > .chat-pad").append(responseRow);
    }
  }

  //Displays additional information in key-value pairs.
  //infoList :: [{info: String, value: String}]
  function getInfoListDisplay(infoList) {
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
  //data :: {id: String, ticker: String, name: String, poll_rate: Int, fav: Bool}
  companyLog.add = function(data) {
    this.list.push(data);
    addCompany(data.id, data.ticker, data.name, data.poll_rate, data.fav);
  };

  companyLog.compareChanges = function() {
    var finalChangeLog = []; //List of all changes that differ from the stored list.
    for (var i = 0; i < this.changeLog.length; i++) {
      var index = this.list.findIndex(e => ((e.id === this.changeLog[i].id) && ((e.fav != this.changeLog[i].fav) || (e.poll_rate != this.changeLog[i].poll_rate)))); //Finds index where company occurs and favourite is different.
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
        this.list[index].poll_rate = this.changeLog[i].poll_rate;
      }
    }
    this.clearChanges();
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
        console.log("GET FAVOURITES");
        console.log(data);
        for (var i = 0; i < data.companyList.length; i++) {
          companyLog.add(data.companyList[i]);
        }
        for (var j = 0; j < data.sectorList.length; j++) {
          sectorLog.add(data.sectorList[j]);
        }
        $("select").material_select(); //###TODO###
      }
    });
  }

  //Sends a JSON object to the server of all companies and sectors which favourite value has been changed.
  function saveFavourites() {
    //Check Favourite Limit has not been reached.
    var companyFavCount = $(".fav-company-switch:checked").length;
    var sectorFavCount = $(".fav-sector-switch:checked").length;

    if (companyFavCount + sectorFavCount > maxFavourites) {
      Materialize.Toast.removeAll(); //Remove all current toast notifications.
      Materialize.toast("Failed to save favourites, cannot have more than " + maxFavourites + " favourites selected.", 4000, "rounded");
      console.log("(ERROR) Favourite limit reached.");
      return;
    }

    companyLog.clearChanges();
    sectorLog.clearChanges();

    $(".fav-table-body-company tr:not(#company-no-result)").each(function() { //For each company row in the modal.
      var id = $(this).find(".fav-company-switch").attr("data-id");
      var poll_rate = $(this).find(".poll-rate-selector").val(); //###TODO###
      var poll_rate2 = $(this).find(".select-dropdown").val(); //###TODO###
      var fav = $(this).find(".fav-company-switch").prop("checked");
      fav = fav ? "1" : "0";
      companyLog.addChange({id: id, fav: fav, poll_rate: poll_rate2}); //##TODO###
    });
    $(".fav-table-body-sector tr:not(#sector-no-result)").each(function() { //For each sector row in the modal.
      var id = $(this).find(".fav-sector-switch").attr("data-id");
      var fav = $(this).find(".fav-sector-switch").prop("checked");
      fav = fav ? "1" : "0";
      sectorLog.addChange({id: id, fav: fav});
    });

    var companyChanges = companyLog.compareChanges(); //List of company changes that are different from the original.
    var sectorChanges = sectorLog.compareChanges(); //List of sector changes that are different from the original.

    if (companyChanges.length === 0 && sectorChanges.length === 0) { return; } //Don't send AJAX request if nothing has changed.

    var sendData = {companyList: companyChanges, sectorList: sectorChanges};

    //Debugging
    console.log("SAVE FAVOURITES");
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

  //TODO
  //Adds a company row to the favourites modal.
  function addCompany(id, ticker, name, poll_rate, fav) {
    var tickerRow = $("<td></td>").text(ticker);
    var nameRow = $("<td></td>").text(name);
    var oldPollRow = $("<td></td>").text("placeholder"); //###TODO### REMOVE PLACEHOLDER LATER

    var pollRow = $("<td><div class='input-field col s12'><select class='pollSelect'>" +
      "<option value='0'>Not Selected</option>" +
      "<option value='1'>5 Minutes</option>" +
      "<option value='2'>15 Minutes</option>" +
      "<option value='3'>1 Hour</option>" +
      "<option value='4'>1 Day</option>" +
      "</select></div></td>");

    switch(poll_rate) {
      case "5 Minutes":
        pollRow.find("option[value='1']").attr("selected", "selected");
        break;
      case "15 Minutes":
        pollRow.find("option[value='2']").attr("selected", "selected");
        break;
      case "1 Hour":
        pollRow.find("option[value='3']").attr("selected", "selected");
        break;
      case "1 Day":
        pollRow.find("option[value='4']").attr("selected", "selected");
        break;
      default: //Not Selected
        pollRow.find("option[value='0']").attr("selected", "selected");
        break;
    }

    var favRow = $("<td><div class='switch'><label><input class='fav-company-switch' type='checkbox'><span class='lever'></span></label></div></td>");
    favRow.find("input").attr("data-id", id);
    if (fav == "1") {
      favRow.find("input").attr("checked", "checked");
    }

    /*var favRow = "<td><div class='switch'><label><input data-id='";
    favRow += id +  "' class='fav-company-switch' type='checkbox'";
    if (fav == "1") { favRow += " checked"; } //Marks the company as favourited.
    favRow += "><span class='lever'></span></label></div></td>";*/

    var companyRow = $("<tr></tr>").append(tickerRow).append(nameRow).append(pollRow).append(favRow);

    /*var companyRow = "<tr>" + tickerRow + nameRow + pollRow + testRow + favRow + "</tr>"; //TODO*/
    $("#fav-company table tbody tr:last").after(companyRow); //Appends the company to the table.
  }

  //Adds a sector row to the favourites modal.
  function addSector(id, name, fav) {
    var nameRow = "<td>" + name + "</td>";
    var favRow = "<td><div class='switch'><label><input data-id='";
    favRow += id + "' class='fav-sector-switch' type='checkbox'";
    if (fav == "1") { favRow += " checked"; } //Marks the sector as favourited.
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

  function pollNotifications(pollTimeText) {
    var companyList = [];
    for (var i = 0; i < companyLog.list.length; i++) {
      if (companyLog.list[i].fav == "1" && companyLog.list[i].poll_rate == pollTimeText) {
        companyList.push(companyLog.list[i].id);
      }
    }

    //Doesn't send AJAX request if no companies need polling.
    if (companyList.length === 0) { return; }

    console.log("SEND NOTIFICATIONS (TIME: " + pollTimeText + ")");
    console.log(companyList);

    //Sends the notification requests to the server.
    $.ajax({
      url: "../ParsingAndProcessing/getNotifications.php", //TODO
      data: {companyList: companyList},
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

/*----------------------------------------------------------------------------*/
/*News*/

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
      var sentiment = article.sentiment;
      /*var accuracy = 0;
      if (sentiment.sentiment === "positive") {
        accuracy = parseFloat(sentiment.accuracy.positivity).toFixed(2) * 100;
      }
      else {
        accuracy = parseFloat(sentiment.accuracy.negativity).toFixed(2) * 100;
      }*/
      var articleRow = $("<a class='tooltipped' data-position='top' data-delay='50'><div class='news-row'><p class='headline'></p><p><small class='headline-sentiment'></small></p><p class='headline-desc'></p></div></a>");
      articleRow.find(".headline").text(headline);
      articleRow.attr("href", url);
      articleRow.attr("data-tooltip", url);
      articleRow.find(".headline-sentiment").text(sentiment.sentiment.charAt(0).toUpperCase() + sentiment.sentiment.slice(1));
      articleRow.find(".headline-desc").text(description);
      articleRow.tooltip({delay: 50});
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
        displayQuery(getFormattedDate(currentTime.toUTCString()), query);
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
        displayErrorResponse(getFormattedDate(currentTime.toUTCString()), "No response from server.");
      },
      success: function(data) {
        hideLoading(); //Hides the rotating loading animation.
        parseResponse(data);
      }
    });
  }

/*----------------------------------------------------------------------------*/
/*Graph*/

  //Creates the canvas object to add to the chat window.
  function getGraphDisplay() {
    return $("<canvas class='response-graph'></canvas>");
  }

  //Formats a date in the format DD/MM/YYYY HH:MM:SS
  function getFormattedDate(original) {
    var date;

    if (original.includes("/")) {
      var splitDate = original.split("\/");
      date = new Date(parseInt(splitDate[2], 10), parseInt(splitDate[1] - 1, 10), parseInt(splitDate[0], 10));
    }
    else {
      var unixTimestamp = Date.parse(original);
      date = new Date(unixTimestamp);
    }

    var newDate = ('0' + date.getDate()).slice(-2) + '/' + ('0' + (date.getMonth() + 1)).slice(-2) + '/' + date.getFullYear();
    var newTime = ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + ':' + ('0' + date.getSeconds()).slice(-2);
    return newDate + ' ' + newTime;
  }

  //Creates a line graph plotting points for High, Low, Bid and Offer for a stock.
  function createLineGraph(dataset) {
    var ctx = $(".response-graph").get(-1).getContext("2d"); //Get context of the last canvas object.

    var dateList = [];
    var closeList = [];
    var highList = [];
    var lowList = [];
    var openList = [];

    for (var i = 0; i < dataset.length; i++) {
      dateList.push(getFormattedDate(dataset[i][0]));
      closeList.push(dataset[i][1]);
      highList.push(dataset[i][2]);
      lowList.push(dataset[i][3]);
      openList.push(dataset[i][4]);
    }

    var graphTitle = dateList[0] + " to " + dateList[dataset.length - 1];

    var lineGraph = new Chart(ctx, {
      type: 'line',
      data: {
          labels: dateList, //x-axis labels.
          datasets: [{
              label: "Close",
              data: closeList,
              borderColor: ["rgba(255, 0, 0, 0.8)"], //Line colour.
              borderWidth: 1, //Line width.
              fill: false, //Doesn't fill under the line.
              pointBorderWidth: 1
            },
            {
              label: "High",
              data: highList,
              borderColor: ["rgba(0, 255, 0, 0.8)"], //Line colour.
              borderWidth: 1, //Line width.
              fill: false, //Doesn't fill under the line.
              pointBorderWidth: 1
            },
            {
              label: "Low",
              data: lowList,
              borderColor: ["rgba(0, 0, 255, 0.8)"], //Line colour.
              borderWidth: 1, //Line width.
              fill: false, //Doesn't fill under the line.
              pointBorderWidth: 1
            },
            {
              label: "Open",
              data: openList,
              borderColor: ["rgba(0, 255, 255, 0.8)"], //Line colour.
              borderWidth: 1, //Line width.
              fill: false, //Doesn't fill under the line.
              pointBorderWidth: 1
            }]
      },
      options: {
        scales: { yAxes: [{
          ticks: { beginAtZero: false },
          scaleLabel : { display: true, labelString: "Price" }
        }]},
        title: { display: true, text: graphTitle},
        elements: { point: { radius: 0 } }
      }
    });
  }

/*----------------------------------------------------------------------------*/
/*Reponse Types*/

  //Parse a response and execute display the appropriate data based off of the response intent.
  function parseResponse(data) {
    console.log("RESPONSE RAW DATA");
    console.log(data);

    var date = new Date();
    var timestamp = getFormattedDate(date.toUTCString());
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
      case "get_intent_conversion":
        var conversionSpeech = getCurrencyConvertSpeech(dataset.intent, stock);
        if (conversionSpeech === undefined || conversionSpeech === null) {
          fallBackError(timestamp);
          return;
        }
        else {
          speech = conversionSpeech + parseFloat(dataset.convertedValue).toFixed(2) + ' ' + dataset.toCurrency;
          speechRow = getSpeechDisplay(speech);
          displayResponseList(timestamp, [speechRow]);
        }
        break;
      case "get_currency_conversion":
        speech = "The conversion rate from " + json.from + " to " + json.to + " is " + parseFloat(dataset).toFixed(2) + ".";
        speechRow = getSpeechDisplay(speech);
        displayResponseList(timestamp, [speechRow]);
        break; //TODO
      case "get_share_price":
        speech += dataset.SharePrice;
        speechRow = getSpeechDisplay(speech);
        infoRow = getInfoListDisplay([
          {info: "Bid", value: dataset.Bid},
          {info: "Offer", value: dataset.Offer}
          //{info: "Open", value: dataset.Open},
          //{info: "Close", value: dataset.Close},
          //{info: "Low", value: dataset.Low},
          //{info: "High", value: dataset.High}
        ]);
        stockTable = getStockDisplay(stock, dataset.SharePrice, dataset.PointChange, dataset.PercentChange);
        displayResponseList(timestamp, [speechRow, stockTable, infoRow]);
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
        infoRow = getInfoListDisplay([
          {info: "Bid", value: dataset.Bid},
          {info: "Offer", value: dataset.Offer}
        ]);
        displayResponseList(timestamp, [speechRow, stockTable, infoRow]);
        break;
      case "get_offer":
        speech += dataset.Offer;
        speechRow = getSpeechDisplay(speech);
        stockTable = getStockDisplay(stock, dataset.SharePrice, dataset.PointChange, dataset.PercentChange);
        infoRow = getInfoListDisplay([
          {info: "Bid", value: dataset.Bid},
          {info: "Offer", value: dataset.Offer}
        ]);
        displayResponseList(timestamp, [speechRow, stockTable, infoRow]);
        break;
      case "get_open":
        speech += dataset.Open;
        speechRow = getSpeechDisplay(speech);
        stockTable = getStockDisplay(stock, dataset.SharePrice, dataset.PointChange, dataset.PercentChange);
        infoRow = getInfoListDisplay([
          {info: "Open", value: dataset.Open},
          {info: "Close", value: dataset.Close}
        ]);
        displayResponseList(timestamp, [speechRow, stockTable, infoRow]);
        break;
      case "get_close":
        speech += dataset.Close;
        speechRow = getSpeechDisplay(speech);
        stockTable = getStockDisplay(stock, dataset.SharePrice, dataset.PointChange, dataset.PercentChange);
        infoRow = getInfoListDisplay([
          {info: "Open", value: dataset.Open},
          {info: "Close", value: dataset.Close}
        ]);
        displayResponseList(timestamp, [speechRow, stockTable, infoRow]);
        break;
      case "get_high":
        speech += dataset.High;
        speechRow = getSpeechDisplay(speech);
        stockTable = getStockDisplay(stock, dataset.SharePrice, dataset.PointChange, dataset.PercentChange);
        infoRow = getInfoListDisplay([
          {info: "Low", value: dataset.Low},
          {info: "High", value: dataset.High}
        ]);
        displayResponseList(timestamp, [speechRow, stockTable, infoRow]);
        break;
      case "get_low":
        speech += dataset.Low;
        speechRow = getSpeechDisplay(speech);
        stockTable = getStockDisplay(stock, dataset.SharePrice, dataset.PointChange, dataset.PercentChange);
        infoRow = getInfoListDisplay([
          {info: "Low", value: dataset.Low},
          {info: "High", value: dataset.High}
        ]);
        displayResponseList(timestamp, [speechRow, stockTable, infoRow]);
        break;
      case "get_revenue":
        speech += dataset.Revenue;
        speechRow = getSpeechDisplay(speech);
        infoRow = getInfoListDisplay([
          {info: "Market Cap", value: dataset.MarketCap}
        ]);
        stockTable = getStockDisplay(stock, dataset.SharePrice, dataset.PointChange, dataset.PercentChange);
        displayResponseList(timestamp, [speechRow, stockTable, infoRow]);
        break;
      case "get_eps":
        speech += dataset.EPS;
        speechRow = getSpeechDisplay(speech);
        infoRow = getInfoListDisplay([
          {info: "Dividend Yield", value: dataset.DivYield},
          {info: "Price-Earnings Ratio", value: dataset.PERatio}
        ]);
        displayResponseList(timestamp, [speechRow, infoRow]);
        break;
      case "get_volume":
        speech += dataset.Volume;
        speechRow = getSpeechDisplay(speech);
        infoRow = getInfoListDisplay([
          {info: "Average Volume", value: dataset.AverageVol}
        ]);
        stockTable = getStockDisplay(stock, dataset.SharePrice, dataset.PointChange, dataset.PercentChange);
        displayResponseList(timestamp, [speechRow, stockTable, infoRow]);
        break;
      case "get_market_cap":
        speech += dataset.MarketCap;
        speechRow = getSpeechDisplay(speech);
        infoRow = getInfoListDisplay([
          {info: "Share Price", value: dataset.SharePrice},
          {info: "Shares in Issue", value: dataset.SharesInIssue},
          {info: "Volume", value: dataset.Volume}
        ]);
        displayResponseList(timestamp, [speechRow, infoRow]);
        break;
      case "get_div_yield":
        speech += dataset.DivYield;
        speechRow = getSpeechDisplay(speech);
        infoRow = getInfoListDisplay([
          {info: "Earnings per Share" , value: dataset.EPS},
          {info: "Price-Earnings Ratio", value: dataset.PERatio},
          {info: "Volume", value: dataset.Volume}
        ]);
        displayResponseList(timestamp, [speechRow, infoRow]);
        break;
      case "get_average_vol":
        speech += dataset.AverageVol;
        speechRow = getSpeechDisplay(speech);
        infoRow = getInfoListDisplay([
          {info: "Volume" , value: dataset.Volume}
        ]);
        displayResponseList(timestamp, [speechRow, infoRow]);
        break;
      case "get_pe_ratio":
        speech += dataset.PERatio;
        speechRow = getSpeechDisplay(speech);
        infoRow = getInfoListDisplay([
          {info: "Dividend Yield", value: dataset.DivYield},
          {info: "Earnings per Share", value: dataset.EPS},
          {info: "Volume", value: dataset.Volume}
        ]);
        displayResponseList(timestamp, [speechRow, infoRow]);
        break;
      case "get_shares_in_issue":
        speech += dataset.SharesInIssue;
        speechRow = getSpeechDisplay(speech);
        infoRow = getInfoListDisplay([
          {info: "Market Cap", value: dataset.MarketCap},
          {info: "Volume", value: dataset.Volume},
          {info: "Share Price", value: dataset.SharePrice}
        ]);
        displayResponseList(timestamp, [speechRow, infoRow]);
        break;
      case "get_news":
        speechRow = getSpeechDisplay(speech);
        newsRow = getNewsDisplay(dataset);
        displayResponseList(timestamp, [speechRow, newsRow]);
        break;
      case "get_stock_performance":
        speechRow = getSpeechDisplay(speech);
        stockTable = getStockDisplay(stock, json.auxillary.SharePrice, json.auxillary.PointChange, json.auxillary.PercentChange);
        graphRow = getGraphDisplay();
        displayResponseList(timestamp, [speechRow, stockTable, graphRow]);
        createLineGraph(dataset);
        break;
      case "get_sector_performance":
        speechRow = getSpeechDisplay(speech);
        stockTable = getStockDisplay(stock, json.dataset.SharePrice, json.dataset.PointChange, json.dataset.PercentChange);
        displayResponseList(timestamp, [speechRow, stockTable]);
        break;
      case "get_buy_or_sell":
        var movingAverages = dataset.movingAverages;
        var technicalIndicators = dataset.technicalIndicators;
        var summary = dataset.Summary;
        speech = "Recommended: " + summary;
        speechRow = getSpeechDisplay(speech);
        infoRow = getInfoListDisplay([
          {info: "Moving Averages", value: dataset.MovingAverages},
          {info: "Technical Indicators", value: dataset.TechnicalIndicators},
          {info: "Summary", value: dataset.Summary}
        ]);
        displayResponseList(timestamp, [speechRow, infoRow]);
        break;
      case "get_sector_rising_or_falling":
        speech = "Here is a summary of the " + stock + " sector performance.";
        speechRow = getSpeechDisplay(speech);
        var responseList = [speechRow];
        for (var i = 0; i < dataset.length; i++) {
          responseList.push(getStockDisplay(dataset[i].TickerSymbol, dataset[i].SharePrice, dataset[i].PointChange, dataset[i].PercentChange));
        }
        displayResponseList(timestamp, responseList);
        break; //TODO TEST
      case "Input Error":
        displayErrorResponse(timestamp, speech);
        break;
      default:
        fallBackError(timestamp);
        return;
    }

    say(speech); //Outputs the response using voice synthesis.
    scrollToChatBottom(); //Scrolls to bottom of the chat window.
  }

  //Returns the speech text when currency conversion is applies to a complex intent.
  function getCurrencyConvertSpeech(intent, stock) {
    switch (intent) {
      case "get_share_price":
        return "The share price of " + stock + " is ";
      default:
        return null;
    }
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
