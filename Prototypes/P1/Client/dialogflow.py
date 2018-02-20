import os.path
import sys
import json

try:
    import apiai
except ImportError:
    sys.path.append(
        os.path.join(os.path.dirname(os.path.realpath(__file__)), os.pardir)
    )
    import apiai

CLIENT_ACCESS_TOKEN = "f4bc3c425f1c4e6b9c52f21493decb19"


def main():
    user_input = sys.argv[1]

    ai = apiai.ApiAI(CLIENT_ACCESS_TOKEN)

    request = ai.text_request()

    #request.session_id = "<SESSION ID, UNIQUE FOR EACH USER>"
    
    request.query = user_input

    response = request.getresponse()

    # print(response.read()) #- Full JSON, might be useful for debugging, can only read once

    json_response = json.loads(response.read()) # Convert response to JSON for parsing 

    result = json_response["result"] # Strip to result header

    fulfillment = result["fulfillment"] # Strip to fulfillment

    print(fulfillment["speech"]) # Strip to speech and read out

if __name__ == '__main__':
    main()