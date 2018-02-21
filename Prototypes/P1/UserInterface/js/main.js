$(document).ready(function() {
  $(".button-collapse").sideNav();
  $('.modal').modal();
  $("#fav-search, #query").val("");
  $("#company-no-result, #sector-no-result, #query-error-message").hide();
  $('ul.tabs').tabs();

  var timeout = 10000; //10 second timeout to AJAX responses.
  var waiting = false; //Flag for if the chatbot is waiting for a response.
  var speechEnabled = false; //Flag for if speech synthesis is enabled.
  var companyChanges = []; //List of all changes to company favourites.
  var sectorChanges = []; //List of all changes to sector favourites.
  var companyOriginal = []; //List of all company favourites at the start of the session.
  var sectorOriginal = []; //List of all sector favourites at the start of the session.


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

  //Adds a new text query to the chat window.
  function displayQuery(timestamp, query) {
    $("#chat-window").append(
      "<div class='left-border'>" +
      "<div class='row timestamp-row'>" +
      "<p class='timestamp--left'>Sent: " + timestamp + "</p>" +
      "</div>" +
      "<div class='row'>" +
      "<div class='chat chat-query'>" +
      "<p></p></div></div></div>"
    );
    $(".chat-query:last p").text(query);
  }

  //Adds a new text reponse to the chat window.
  function displayResponse(timestamp, response) {
    $("#chat-window").append(
      "<div class='right-border'>" +
      "<div class='row timestamp-row'>" +
      "<p class='timestamp--right'>Received: " + timestamp + "</p>" +
      "</div>" +
      "<div class='row'>" +
      "<div class='chat chat-response'>" +
      "<p></p></div></div></div>" +
      "<div class='response-divider'></div>"
     );
     $(".chat-response:last p").text(response);
     say(response);
  }

  function displayErrorResponse(timestamp, response) {
    $("#chat-window").append(
      "<div class='right-border-error'>" +
      "<div class='row timestamp-row'>" +
      "<p class='timestamp--right'>Received: " + timestamp + "</p>" +
      "</div>" +
      "<div class='row'>" +
      "<div class='chat chat-response-error'>" +
      "<p></p></div></div></div>" +
      "<div class='response-divider'></div>"
     );
     $(".chat-response-error:last p").text("Error: " + response);
     say("Error. " + response);
  }

  //Shows the loading icon.
  function showLoading() {
    waiting = true;
    $("#chat-window").append(
      "<div id='loader-div' class='row right'><div class='loader'></div></div>"
    );
  }

  //Hides the loading icon.
  function hideLoading() {
    waiting = false;
    $("#loader-div").remove();
  }

/*----------------------------------------------------------------------------*/
/*Favourites Modal*/

  //Adds a company to the favourites modal.
  function addCompany(ticker, name, fav) {
    var companyRow = "<tr><td>" + ticker + "</td><td>";
    companyRow += name + "</td><td><div class='switch'><label><input id='ticker-" + ticker + "' class='fav-company-switch' type='checkbox'";
    if (fav) {
      companyRow += " checked"; //Marks the company as favourited.
    }
    companyRow += "><span class='lever'></span></label></div></td></tr>";
    $("#fav-company table tbody tr:last").after(companyRow); //Appends the company to the table.
  }

  //Adds a sector to the favourites modal.
  function addSector(id, name, fav) {
    var sectorRow= "<tr id='sector-" + id + "'><td>" + name + "</td>";
    sectorRow += "<td><div class='switch'><label><input id='" + id + "' class='fav-company-sector' type='checkbox'";
    if (fav) {
      sectorRow += " checked"; //Marks the sector as favourited.
    }
    sectorRow += "><span class='lever'></span></label></div></td></tr>";
    $("#fav-sector table tbody tr:last").after(sectorRow); //Appends the sector to the table.
  }

  //Filters the company and sector list.
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

  //Adds events to modal objects after page load and other conditions.
  function addModalEvents() {
    //Add company favourite changes to the corresponding array when detected.
    $(".fav-company-switch").click(function() {
      var companyID = $(this).attr("id"); //Gets the ID attribute of the company.
      var fav = $(this).prop("checked");
      var change = {id: companyID, fav: fav}; //Creates a new object for the change.
      var index = companyChanges.findIndex(function(e) {
        return e.id === companyID; //Finds if the company has already been changed in the session.
      });
      if (index != -1) { //Removes a previous occurence from the array.
        companyChanges.splice(index,  1);
      }
      companyChanges.push(change); //Adds new change to the end of the array.
    });

    //Add sector favourite changes to the corresponding array when detected.
    $(".fav-sector-switch").click(function() {
      var sectorID = $(this).attr("id"); //Gets the ID attribute of the sector.
      var fav = $(this).prop("checked");
      var change = {id: sectorID, fav: fav}; //Creates a new object for the change.
      var index = sectorChanges.findIndex(function(e) {
        return e.id === sectorID; //Finds if the sector has already been changed in the session.
      });
      if (index != 1) { //Removes a previous occurence from the array.
        sectorChanges.splice(index, 1);
      }
      sectorChanges.push(change); //Adds new change to the end of the array.
    });
  }

  //TODO
  function saveCompanyList() {
    //TODO
    /*for (var i = 0; i < companyChanges.length; i++) {


      var index = companyOriginal.findIndex(function(e) {
        return (e.id === companyChanges[i].id) && (e.fav == companyChanges[i].fav)
      }
    }*/
    //remove non-Changes

    var companyChangesFormat = [];


    if (companyChanges.length > 0) {
      $.ajax({
        url: "https://www.google.com/fakepage.php", //###TODO change to php file later.
        data: {},
        method: "POST",
        timeout: timeout,
        error: function(xhr, ajaxOptions, thrownError) {
          return false;
        },
        success: function(data) {
          return true;
        }
      });
    }
    else {
      return true;
    }


  }

  //TODO
  function formatSectorList() {

  }

/*----------------------------------------------------------------------------*/
/*Company and Sector List*/

  //TODO
  function getFTSEListData() {
    $.ajax({
      url: "https://www.google.com/fakepage.php", //###change to php file later.
      data: {},
      method: "POST",
      timeout: timeout,
      error: function(xhr, ajaxOptions, thrownError) {
        Materialize.Toast.removeAll(); //Remove all current toast notifications.
        Materialize.toast("Failed to retrieve FTSE Company and Sector data.", 2000, "rounded"); //Notify that synthesis is not supported.
      },
      success: function(data) {
        //TODO
        parseCompanies(data.companyList); //TODO ###
        parseSectors(data.sectorList); //TODO ###
      }
    });
  }

  //TODO
  function parseCompanies(data) {
    data.forEach(function(d) {
      addCompanies(d[0], d[1], d[2]); //Adds the company to the modal.
      companyOriginal.push({id: d[0], fav: d[2]}); //Adds the ticker and if favourited to the array.
    });
  }

  //TODO
  function parseSectors(data) {
    data.forEach(function(d) {
      addSector(d[0], d[1], d[2]); //Adds the company to the modal.
      sectorOriginal.push({id: d[0], fav: d[2]}); //Adds the id and if favourited to the array.
    });
  }

/*----------------------------------------------------------------------------*/

  //Redirect button click and ENTER to submitQuery function.
  $("#btn-send").click(submitQuery);
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
    $("#chat-window").stop().animate({
      scrollTop: height
    }, 500);
  }

  //Sends a query to the web server, and waits for a response.
  function sendQuery(query) {
    $.ajax({
      //Edited to call the python script in the client folder, will update to the parsing folder
      //When it works, just proof of concept for now
      url: "../Client/dialogflow.php", 
      data: {user_query:query},
      method: "POST",
      timeout: timeout,
      error: function(xhr, ajaxOptions, thrownError) {
        var currentTime = new Date();
        hideLoading(); //Hides the rotating loading animation.
        displayErrorResponse(currentTime.toUTCString(), "No response from server."); //###
      },
      success: function(data) {
        hideLoading(); //Hides the rotating loading animation.
        alert(data);//debug mode for seeing raw json output 
        parseResponse(data);
      }
    });
  }

/*----------------------------------------------------------------------------*/
/*Responses*/

  //TODO
  function parseResponse(data) {
    console.log("parse response layer"); //###
    var currentTime = new Date(); // oh boy love me some time
    displayResponse(currentTime.toUTCString(), data); // Added such that Dialogflow can respond
  }

  //TODO
  //Creates the graph object to add to the chat window.
  function createLineGraph() {
    var ctx = $(".response-graph").get(0).getContext("2d"); //Get context of the last canvas object.
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

  //Adds a new text reponse to the chat window.
  function displayGraphResponse(timestamp, response) {
    $("#chat-window").append(
      "<div class='right-border'>" +
      "<div class='row timestamp-row'>" +
      "<p class='timestamp--right'>Received: " + timestamp + "</p>" +
      "</div>" +
      "<div class='row'>" +
      "<div class='chat chat-response'>" +
      "<p></p><canvas class='response-graph'></canvas>" +
      "</div></div></div>" +
      "<div class='response-divider'></div>"
     );
     $(".chat-response:last p").text(response);
     createLineGraph(); //Displays a graph in the response.
     say(response); //Says the response using speech synthesis.
  }

  //TODO
  function displayHighlightedResponse(timestamp, response) {

  }

/*----------------------------------------------------------------------------*/

  //Testing chat queries and commands.
  displayQuery("02/12/18 13:10:14", "Hello");
  displayResponse("02/12/18 13:10:20", "Goodbye");

  displayQuery("02/12/18 14:45:59", "A very extremely long query to test how the CSS responds to the long length of a query. It should not exceed 75% of the chatbot width and wrap into multiple lines.");
  displayResponse("02/12/18 14:46:08", "A very extremely long query to test how the CSS responds to the long length of a query. It should not exceed 75% of the chatbot width and wrap into multiple lines.");

  displayQuery("12/02/18 13:13:09", "What is the spot price of Apple?");
  displayResponse("12/02/18 13:13:24", "The spot price of Apple is £2.30");

  addCompany("CHEF", "My name is chef", true);
  addCompany("SPAG", "sOmEbOdY ToUcHa mY sPaGeTT", false);

  addSector("1", "Banks", true);
  addSector("2", "Financial Services", false);
  addSector("3", "General Retailers", false);
  addSector("4", "Media", false);
  addSector("5", "Mining", true);

  //TODO
  displayGraphResponse("12/02/18 13:13:09", "GRAPH TEST 1");

  getFTSEListData(); //TODO ###company and sector lists for modal form.

  addModalEvents(); //###

});
