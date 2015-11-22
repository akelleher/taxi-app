#include "websocket_func.h"
#include <cstdlib>
#include <cstdio>
#include <cstring>
#include <sys/socket.h>

/*

N.B:

Does not support frame w/ size over 65,535 bytes so far

MASK bit is always ZERO
OPCODE is always 0x1 --- text frame

TODO List:

<HIGH> support binary data sending

<MEDIUM - HIGH> don't call send() here, push the request into a queue,
                leave it to a pthread function, thus avoiding Racing 
                Condition in concurrent situation.

<MEDIUM - HIGH> Optimize: declare every large object (e.g. char[4096]... )
                in heap, NOT in stack. pass them by reference. Don't forget
                to free the memory after use. 

<MEDIUM> add the support for MASK

<MEDIUM> implement large data sending i.e over 65,535 bytes

*/
bool ws_send(int socket, std::string content, int flag) {

    char* send_buff;
    int send_len;

    if ( content.length() < 126 ) {

        send_buff = (char*)calloc(content.length()+3, sizeof(char));
        send_len = content.length() + 2;

    } else if ( content.size() < 65536 ) {

        send_buff = (char*)calloc(content.length()+5, sizeof(char));
        send_len = content.length() + 4;

    } else {
        // TO BE IMPLEMENTED
        fprintf(stderr, "ws_send() doesn't support sending frames with size over 65,535 bytes so far.\n");
        return false;
    }

    if (!send_buff) {
        fprintf(stderr, "calloc(): insufficient memory.\n");
        return false;
    }

    send_buff[0] = 0x81; // FIN=1 OPCODE=1
    char* ptr = &send_buff[1];

    // set MASK bit to ZERO
    // write the length of content(payload) to corresponding bit field
    if ( content.length() < 126 ) {
        *ptr++ = 0x7f & content.size();    // set the MASK = 0
    } else if ( content.length() < 65536 ) {
        *ptr++ = 0x7e;                     // MASK = 0, length flag = 126
        *ptr++ = content.length() >> 8;    // higher 8-bit to send_buff[2]
        *ptr++ = content.length() & 0xff;  // lower 8-bit to send_buff[3]
    } else {
        // TO BE IMPLEMENTED
    }

    printf("send_len: %d [2]: %x [3]: %x\n", send_len, send_buff[2], send_buff[3]);// debug

    bool ret = false;

    strcpy(ptr, content.c_str());
    int n = send( socket, send_buff, send_len, flag );

    if ( n < 0 )
        perror("send()");

    if ( n != send_len)
        fprintf(stderr, "send(): length mismatch.\n");
    else
        ret = true;

    free(send_buff);
    return ret;
    
}

//TODO
int parse_ws_recv(char* buffer, int buffer_size, std::string& payload) {
    return 0;
}
