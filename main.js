$(document).ready(function(){

    const CLIENT_ACCESS_TOKEN = "53c11b7a186742c1ae9422aa5938c0e1"
    
    $("#in").submit(function (event) {
        
        var input = $("input").val();
        
        if (input != "") {
            
            const client = new ApiAi.ApiAiClient({accessToken: CLIENT_ACCESS_TOKEN});
            
            const response = client.textRequest(input);

            response
                .then(handleResponse)
                .catch(handleError);

            function handleResponse(serverResponse) {
                    console.log(serverResponse.result.fulfillment.speech);
                    $("p").html(serverResponse.result.fulfillment.speech);
            }
            
            function handleError(serverError) {
                    console.log(serverError);
            }

        } else {
            $("p").html("");
        }

        event.preventDefault();
            
    });
});