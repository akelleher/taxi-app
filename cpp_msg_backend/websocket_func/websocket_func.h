#ifndef _WEBSOCKET_FUNC_H_
#define  _WEBSOCKET_FUNC_H_


#include <string>

bool ws_send(int socket, std::string content, int flag);
int parse_ws_recv(char* buffer, int buffer_size, std::string& payload);


#endif
