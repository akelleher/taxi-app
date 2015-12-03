#include <unordered_map>
#include <string>
#include <boost/regex.hpp>
#include <cstdio>

#include "../driver/driver.h"
#include "../websocket_func/websocket_func.h"

extern boost::regex *verify_driver;
extern boost::regex *verify_notification;
extern boost::regex *verify_response;
extern boost::regex *verify_break;

extern boost::regex *parse_name;
extern boost::regex *parse_latitude;
extern boost::regex *parse_longitude;
extern boost::regex *parse_email;
extern boost::regex *parse_note;
extern boost::regex *parse_addr;
extern boost::regex *parse_reply;

extern std::unordered_map<std::string, driver> drivers_location;
extern int dispatcher_fd;

int process_cmd( int index, char *payload );
bool update_driver_info( int index, std::string input );
bool retrieve_all_drivers( int index, std::string input );
bool send_notification( int index, std::string input );
bool response_notification(int index, std::string input);
bool take_a_break(int index, std::string input);



