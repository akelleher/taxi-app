#include "cmd_process.h"

int process_cmd( int index, char *payload ) {

    std::string input = payload;
    printf("length of string: %lu\n", input.length());

    // a driver is updating his location
    if ( regex_match(input, *verify_driver) ) {

        update_driver_info(index, input);

    // a dispatcher is gonna get all the locations
    } else if ( !(strcmp(payload, "RETRIEVE_ALL")) ) {

        dispatcher_fd = index;
        retrieve_all_drivers(index, input);

    // send notification from dispatcher to driver
    } else if ( regex_match(input, *verify_notification) ) {

        dispatcher_fd = index;
        send_notification(index, input);

    // forward response from driver to dispatcher
    } else if ( regex_match(input, *verify_response) ) {

        response_notification(index, input);

    // when driver hit "take a break"
    } else if ( regex_match(input, *verify_break) ) {

        take_a_break(index, input);

    // error: undefined command
    } else {

        ws_send(index, std::string("{\"type\":\"server_response\",\"content\":\"COMMAND_NOT_FOUND\"}"), 0);
    }

  return 0;
}

bool update_driver_info( int index, std::string input ) {

    boost::sregex_iterator itr_end;

    // parse email
    boost::sregex_iterator email_itr(input.begin(), input.end(), *parse_email);
    std::string email = email_itr->str();
    email.erase(email.begin(), email.begin()+7);		// remove the beginning tag
    email.erase(email.end()-8, email.end());			// remove the ending tag

    // parse user
    boost::sregex_iterator name_itr(input.begin(), input.end(), *parse_name);
    std::string name = name_itr->str();
    name.erase(name.begin(), name.begin()+6);			// remove the beginning tag
    name.erase(name.end()-7, name.end());			// remove the ending tag

    // parse latitude
    boost::sregex_iterator latitude_itr(input.begin(), input.end(), *parse_latitude);
    std::string latitude = latitude_itr->str();
    latitude.erase(latitude.begin(), latitude.begin()+10);	// remove the beginning tag
    latitude.erase(latitude.end()-11, latitude.end());		// remove the ending tag
    double latitude_num = stod(latitude);

    // parse longitude
    boost::sregex_iterator longitude_itr(input.begin(), input.end(), *parse_longitude);
    std::string longitude = longitude_itr->str();
    longitude.erase(longitude.begin(), longitude.begin()+11);	// remove the beginning tag
    longitude.erase(longitude.end()-12, longitude.end());	// remove the ending tag
    double longitude_num = stod(longitude);

    // parse note
    boost::sregex_iterator note_itr(input.begin(), input.end(), *parse_note);
    std::string note = note_itr->str();
    note.erase(note.begin(), note.begin()+6);	// remove the beginning tag
    note.erase(note.end()-7, note.end());	// remove the ending tag

    printf("email: %s name: %s latitude: %f longitude: %f note:%s\n", 
           email.c_str(), name.c_str(), latitude_num, longitude_num, note.c_str());

    // insert or update the entry hash table
    auto search_result = drivers_location.find(email);
    if (search_result == drivers_location.end())
      drivers_location.insert( {email, driver(index, email, name, latitude_num, longitude_num, note)} );

    else {
      std::string old_addr = search_result->second.addr;
      search_result->second = driver(index, email, name, latitude_num, longitude_num, note);
      search_result->second.addr = old_addr;
    }

    ws_send(index, std::string("{\"type\":\"server_response\",\"content\":\"ACK\"}"), 0);

    return true;
}

bool retrieve_all_drivers( int index, std::string input ) {

    auto itr = drivers_location.begin();
    for(; itr != drivers_location.end(); itr++) {

	// compose JSON script
        std::string msg;
        msg.append("{\"type\":\"driver_coordination");
        msg.append("\",\"email\":\"");
        msg.append(itr->first);
        msg.append("\",\"name\":\"");
        msg.append(itr->second.name);
        msg.append("\",\"la\":\"");
        msg.append( std::to_string(itr->second.latitude) );
        msg.append("\",\"lo\":\"");
        msg.append( std::to_string(itr->second.longitude) );
        msg.append("\",\"note\":\"");
        msg.append(itr->second.note);
        msg.append("\"}");

        printf("sending: %s length: %zd\n", msg.c_str(), msg.size());
        ws_send(index, msg, 0);
    }

    return true;
}

bool send_notification( int index, std::string input ) {

    boost::sregex_iterator itr_end;

    // parse email
    boost::sregex_iterator email_itr(input.begin(), input.end(), *parse_email);
    std::string email = email_itr->str();
    email = email.substr(7, email.size() - 15);		// remove XML tag

    // parse address
    boost::sregex_iterator addr_itr(input.begin(), input.end(), *parse_addr);
    std::string addr = addr_itr->str();
    addr = addr.substr(6, addr.size() - 13);		// remove XML tag

    // parse note
    boost::sregex_iterator note_itr(input.begin(), input.end(), *parse_note);
    std::string note = note_itr->str();
    note = note.substr(6, note.size() -13);		// remove XML tag

    printf("email: %s, addr: %s, note: %s\n", email.c_str(), addr.c_str(), note.c_str());

    auto search_result = drivers_location.find(email);
    if (search_result == drivers_location.end())
        ws_send(index, std::string("{\"type\":\"server_response\",\"content\":\"DRIVER_NOT_FOUND\"}"), 0);

    // send notification to driver 
    else {

        // update addr data to driver
        search_result->second.addr = addr;

        // compose JSON script
        std::string msg;
        msg.append("{\"type\":\"notification");
        msg.append("\",\"email\":\"");
        msg.append(email);
        msg.append("\",\"addr\":\"");
        msg.append(addr);
        msg.append("\",\"note\":\"");
        msg.append(note);
        msg.append("\"}");

        // send via websocket
        ws_send(search_result->second.fd, msg, 0);
        ws_send(index, std::string("{\"type\":\"server_response\",\"content\":\"NOTIFICATION_SEND_SUCCESSFULLY\"}"), 0);

    }

    return true;
}

bool response_notification(int index, std::string input) {

    boost::sregex_iterator itr_end;

    // parse email
    boost::sregex_iterator email_itr(input.begin(), input.end(), *parse_email);
    std::string email = email_itr->str();
    email = email.substr(7, email.size() - 15);		// remove XML tag

    // parse address
    boost::sregex_iterator reply_itr(input.begin(), input.end(), *parse_reply);
    std::string reply = reply_itr->str();
    reply = reply.substr(7, reply.size() - 15);		// remove XML tag

    printf("reply： %s, comes from: %s\n", reply.c_str(), email.c_str());

    // find driver from hash table
    auto search_result = drivers_location.find(email);

    // this should never happen
    if (search_result == drivers_location.end()) {
        ws_send(index, std::string("{\"type\":\"server_response\",\"content\":\"DRIVER_NOT_FOUND\"}"), 0);
        ws_send(dispatcher_fd, std::string("{\"type\":\"server_response\",\"content\":\"INTERNAL_DATA_CORRUPT\"}"), 0);
        fprintf(stderr, "hash table data corrupt. Cannot find driver %s\n", email.c_str());
        return false;
    }

    // for valid reply - forward it to dispatcher
    if (!reply.compare("Y") || !reply.compare("N")) {

        std::string msg;
        msg.append("{\"type\":\"reply_notification");
        msg.append("\",\"reply\":\"");
        msg.append(reply);
        msg.append("\",\"email\":\"");
        msg.append(email);
        msg.append("\",\"name\":\"");
        msg.append(search_result->second.name);
        msg.append("\",\"addr\":\"");
        msg.append(search_result->second.addr);
        msg.append("\"}");

        ws_send(index, std::string("{\"type\":\"server_response\",\"content\":\"ACK\"}"), 0);
        ws_send(dispatcher_fd, msg, 0);

    } else {
        ws_send(index, std::string("{\"type\":\"server_response\",\"content\":\"INVALID_RESPONSE\"}"), 0);
    }

    return true;
}

bool take_a_break(int index, std::string input) {

    // get email address out from XML tag
    std::string email = input.substr(7, input.size() - 15);

    // change driver status
    auto search_result = drivers_location.find(email);
    if (search_result == drivers_location.end())
        ws_send(index, std::string("{\"type\":\"server_response\",\"content\":\"DRIVER_NOT_FOUND\"}"), 0);
    else
      search_result->second.note = std::string("taking_break");

    ws_send(index, std::string("{\"type\":\"server_response\",\"content\":\"ACK\"}"), 0);

    return true;

}
