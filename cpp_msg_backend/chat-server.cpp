#include <cmath>
#include <sys/errno.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <sys/wait.h>
#include <cstdio>
#include <unistd.h>
#include <cstring>
#include <cstdlib>
#include <arpa/inet.h>
#include <sys/select.h>
#include <stdint.h>

/* from http://www.packetizer.com/security/sha1/ */
#include "sha1-c/sha1.h"

#include <boost/regex.hpp>                   /* boost regular expression */
#include <string>                            /* c++ string */
#include <unordered_map>                     /* c++11 hash table */
#include "./driver/driver.h"                 /* class for saving driver information */
#include "./cmd_process/cmd_process.h"       /* all non-member functions for processing incoming msg */
#include "./websocket_func/websocket_func.h" /* implementation of websocket protocol */


#define GUID "258EAFA5-E914-47DA-95CA-C5AB0DC85B11"

#define WS_STATUS_CONNECTING 1
#define WS_STATUS_OPEN 2
#define WS_STATUS_CLOSING 3
#define WS_STATUS_CLOSED 4

// global variables
/* change this to a '|' character for testing the SEND command */
char newline = '\n';

boost::regex *verify_driver;
boost::regex *verify_notification;
boost::regex *verify_response;
boost::regex *verify_break;

boost::regex *parse_name;
boost::regex *parse_latitude;
boost::regex *parse_longitude;
boost::regex *parse_email;
boost::regex *parse_note;
boost::regex *parse_addr;
boost::regex *parse_reply;


struct user {
  int fd;
  int status;
};

std::unordered_map<std::string, driver> drivers_location;
int dispatcher_fd;

extern int errno;

#define BUFFER_SIZE 4096
#define MAX_CLIENTS 100

int hex_to_digit( char h ) { return ( h > '9' ? h - 'A' + 10 : h - '0' ); }

void trim_right( char * s ) {
  while ( strlen( s ) > 0 && ( s[ strlen( s ) - 1 ] == newline || s[ strlen( s ) - 1 ] == ' ' ) ) {
    s[ strlen( s ) - 1 ] = '\0';
  }
}

char * do_hash( char * sec_ws_key );
char * encode_base64( int size, unsigned char * src );
void erase_timeout_driver();

int main() {

  //<driver><email>pomaj@rpi.edu</email><name>Juan Poma</name><latitude>-73.68</latitude><longitude>42.72</longitude></driver>
  //<notify><email>pomaj@rpi.edu</email><addr>earth</addr><note>wheel chair</note></notify>
  //<response><reply>Y</reply><email>pomaj@rpi.edu</email></response>
  verify_driver = new boost::regex("^<driver><email>([a-zA-z0-9._]++[@]+[a-zA-Z0-9_]++[.]+[a-zA-Z0-9._]+)</email><name>(.+)</name><latitude>([-0-9]++[.]+[0-9]+)</latitude><longitude>([-0-9]++[.]+[0-9]+)</longitude><note>(.+)</note></driver>$");
  verify_notification = new boost::regex("^<notify><email>(.+)</email><addr>(.+)</addr><note>(.+)</note></notify>$");
  verify_response = new boost::regex("^<response><reply>(.+)</reply><email>(.+)</email></response>$");
  verify_break = new boost::regex("^<break>(.+)</break>$");

  parse_email = new boost::regex("<email>([a-zA-z0-9._]++[@]+[a-zA-Z0-9_]++[.]+[a-zA-Z0-9._]+)</email>");
  parse_name = new boost::regex("<name>(.+)</name>");
  parse_latitude = new boost::regex("<latitude>([-0-9]++[.]+[0-9]+)</latitude>");
  parse_longitude = new boost::regex("<longitude>([-0-9]++[.]+[0-9]+)</longitude>");
  parse_note = new boost::regex("<note>(.+)</note>");
  parse_addr = new boost::regex("<addr>(.+)</addr>");
  parse_reply = new boost::regex("<reply>(.+)</reply>");

  char buffer[ BUFFER_SIZE ];

  int sock, newsock, len, n;
  unsigned int fromlen;

  fd_set readfds;  

  /* socket structures from /usr/include/sys/socket.h */
  struct sockaddr_in server;
  struct sockaddr_in client;

  unsigned short port = 8787;
  
  /* Create the listener socket as TCP socket */
  /*   (use SOCK_DGRAM for UDP)               */
  sock = socket( PF_INET, SOCK_STREAM, 0 );

  if ( sock < 0 )
  {
    perror( "socket()" );
    exit( 1 );
  }

  server.sin_family = PF_INET;
  server.sin_addr.s_addr = INADDR_ANY;

  /* htons() is host-to-network-short for marshalling */
  /* Internet is "big endian"; Intel is "little endian" */
  server.sin_port = htons( port );
  len = sizeof( server );

  /* Enable using the port that is in TIME_WAIT state */
  int yes = 1;
  setsockopt( sock, SOL_SOCKET, SO_REUSEADDR, (const char *)&yes, sizeof( yes ) );

  if ( bind( sock, (struct sockaddr *)&server, len ) < 0 )
  {
    perror( "bind()" );
    exit( 1 );
  }

  fromlen = sizeof( client );
  listen( sock, 5 );  /* 5 is number of backlogged waiting clients */
  printf( "Listener socket created and bound to port %d\n", port );

  std::vector<user> online_users;

  while ( 1 ) {

    erase_timeout_driver();

    FD_ZERO( &readfds );
    FD_SET( sock, &readfds );  /* <== incoming new client connections */
    printf( "Set FD_SET to include listener fd %d\n", sock );

    for ( unsigned int i = 0 ; i < online_users.size(); i++ ) {
      FD_SET( online_users[i].fd, &readfds );
      printf( "Set FD_SET to include client socket fd %d\n",
              online_users[i].fd );
    }

    /* BLOCK (but blocked on ALL fds that are set via FD_SET */
    select( FD_SETSIZE, &readfds, NULL, NULL, NULL );

    if ( FD_ISSET( sock, &readfds ) ) {

      /* We know that this accept() call will NOT block */
      newsock = accept( sock, (struct sockaddr *)&client, &fromlen );
      user a_user;
      a_user.fd = newsock; a_user.status = WS_STATUS_CONNECTING;
      online_users.push_back(a_user);
      printf( "Accepted client connection\n" );
    }

    int i = 0;
    for ( ;; i++ ) {

      if (i >= online_users.size() ) break;

      int fd = online_users[i].fd;

      if ( FD_ISSET( fd, &readfds ) )
      {
        int close_it = 0;
        n = recv( fd, buffer, BUFFER_SIZE - 1, 0 );

        if ( n == 0 ) close_it = 1;
        else if ( n < 0 ) {
          perror( "recv()" ); // connection reset by peer
          close_it = 1;
        }

        else if ( online_users[i].status == WS_STATUS_CONNECTING )
        {
          buffer[n] = '\0';
          printf( "Received message from fd %d: [%s]\n", fd, buffer );

          char *tok = strtok( buffer, "\n" );
          char *path = NULL, *host = NULL, *location = NULL;
          char *sec_ws_key = NULL, *sec_ws_protocol = NULL;

          if ( tok != NULL )
          {
            printf( "REQUEST LINE: %s\n", tok );

            if ( strncmp( tok, "GET /", 5 ) != 0 )
            {
              printf( "Unknown request line. Ignoring request.\n" );
              close_it = 1;
              tok = NULL;
            }
            else
            {
              path = (char *)calloc( strlen( tok ), sizeof( char ) );
              int i = 0;
              char *p = tok + 4;
              while ( p < tok + strlen( tok ) && *p != ' ' )
              {
                path[i++] = *p++;
              }
              printf( "path is: [%s]\n", path );
            }

            if ( tok != NULL )
            {
              tok = strtok( NULL, "\n" );
            }
          }

          while ( tok != NULL )
          {
            printf( "HEADER LINE: %s\n", tok );

            if ( strncmp( tok, "Host: ", 6 ) == 0 )
            {
              host = (char *)calloc( strlen( tok ), sizeof( char ) );
              strcpy( host, tok + 6 );
              while ( host[ strlen( host ) - 1 ] == '\r' ||
                      host[ strlen( host ) - 1 ] == '\n' )
              {
                host[ strlen( host ) - 1 ] = '\0';
              }
              printf( "Host is: [%s]\n", host );

              location = (char *)calloc( strlen( host ) +
                                         strlen( path ) + 6,
                                         sizeof( char ) );
              sprintf( location, "ws://%s%s", host, path );
              printf( "Location is: [%s]\n", location );
            }
            else if ( strncmp( tok, "Sec-WebSocket-Key: ", 19 ) == 0 )
            {
              sec_ws_key = (char *)calloc( strlen( tok ), sizeof( char ) );
              strcpy( sec_ws_key, tok + 19 );
              while ( sec_ws_key[ strlen( sec_ws_key ) - 1 ] == '\r' ||
                      sec_ws_key[ strlen( sec_ws_key ) - 1 ] == '\n' )
              {
                sec_ws_key[ strlen( sec_ws_key ) - 1 ] = '\0';
              }
              printf( "Sec-WebSocket-Key is: [%s]\n", sec_ws_key );
            }
            else if ( strncmp( tok, "Sec-WebSocket-Protocol: ", 24 ) == 0 )
            {
              sec_ws_protocol = (char *)calloc( strlen( tok ), sizeof( char ) );
              strcpy( sec_ws_protocol, tok + 24 );
              while ( sec_ws_protocol[ strlen( sec_ws_protocol ) - 1 ] == '\r' ||
                      sec_ws_protocol[ strlen( sec_ws_protocol ) - 1 ] == '\n' )
              {
                sec_ws_protocol[ strlen( sec_ws_protocol ) - 1 ] = '\0';
              }
              printf( "Sec-WebSocket-Protocol is: [%s]\n", sec_ws_protocol );
            }

            tok = strtok( NULL, "\n" );
          }

          if ( host == NULL )
          {
            printf( "Missing Host header. Ignoring.\n" );
            close_it = 1;
          }
          else if ( sec_ws_key == NULL )
          {
            printf( "Missing Sec-WebSocket-Key header. Ignoring.\n" );
            close_it = 1;
          }

          if ( close_it == 0 )
          {
            char response[ BUFFER_SIZE ];
            char *hashed = NULL;
            char *r2 = "";

            hashed = do_hash( sec_ws_key );

            if ( sec_ws_protocol )
            {
              r2 = (char *)calloc( strlen( sec_ws_protocol ) + 32, sizeof( char ) );
              sprintf( r2, "Sec-WebSocket-Protocol: %s\r\n", sec_ws_protocol );
            }

            sprintf( response, "HTTP/1.1 101 Switching Protocols\r\n"
                               "Upgrade: WebSocket\r\n"
                               "Connection: Upgrade\r\n"
                               "Sec-WebSocket-Accept: %s\r\n%s\r\n",
                               hashed, r2 );

            printf( "RESPONSE: [%s]\n", response );

            n = send( fd, response, strlen( response ), 0 );
            if ( n < strlen( response ) )
            {
              perror( "send()" );
              close_it = 1;
            }
            else
            {
              online_users[i].status = WS_STATUS_OPEN;
            }
          }
        }
        else if ( online_users[i].status == WS_STATUS_OPEN )
        {
          char response[ BUFFER_SIZE ];
          int response_length = 0;

          printf( "Received ws message from fd %d of length %d\n", fd, n );

          if ( n < 6 )
          {
            printf( "Frame too short. Ignoring.\n" );
            close_it = 1;
          }
          else
          {
            int payload_start = 2;

            char b1 = buffer[0];
            int fin = b1 & 0x80;    /* 1st bit is FIN */
            int opcode = b1 & 0x0f; /* low-order 4 bits */

            response[0] = 0x80 | opcode;
            response_length++;

            char b2 = buffer[1];
            int mask = b2 & 0x80;   /* 1st bit is MASK */
            int length = b2 & 0x7f; /* low-order 7 bits */

            printf( "FIN: %d; OPCODE: %d; MASK: %d; LENGTH: %d\n",
                    fin, opcode, mask, length );

            if ( length == 126 ) {

              payload_start += 2;
              length = (unsigned char)buffer[2];
              length <<= 8;
              length += (unsigned char)buffer[3];

              printf( "**Payload length code: 126 actual length: %d\n", length);
            }

            else if ( length == 127 ) {
              printf( "**Frame size over 65,535 bytes. Ignoring for now....\n");
              close_it = 1;
            }

            char mask_bytes[4];

            if ( mask ) {
                memcpy( mask_bytes, buffer + payload_start, 4 );
                payload_start += 4;
            }

            if ( n < payload_start + length ) {

                printf( "Frame is incomplete. Ignoring.\n" );
                close_it = 1;

            } else {

                char * payload = (char *)calloc( length + 1, sizeof( char ) );
                memcpy( payload, buffer + payload_start, length );

                // decode data by mask value
                if ( mask ) {
                    for ( int i = 0 ; i < length ; i++ )
                        payload[i] ^= mask_bytes[ i % 4 ];
                }

                if ( opcode == 0x08 ) {

                    printf( "Sending RCVD Close Frame\n" );
                    response[1] = 0x02;
                    response[2] = 0x00;  /* echo close back */
                    response[3] = 0x03;
                    response_length += 3;

                } else if ( opcode == 0x02 || opcode == 0x01 ) {

                  printf( "RCVD %s DATA FRAME\n", ( opcode == 0x01 ? "TEXT" : "BINARY" ) );

                  if ( opcode == 0x01 ) printf( "PAYLOAD: [%s]\n", payload );

                  process_cmd( online_users[i].fd, payload);

                }
              }
          }
        }

        if ( close_it ) {

          printf( "Client on fd %d closed connection\n", fd );
          close( fd );

          /* remove fd from online_users: */
          std::vector<user>::iterator vec_itr = online_users.begin();
          for (; vec_itr != online_users.end(); vec_itr++) {

            if (vec_itr->fd == fd) {
              online_users.erase(vec_itr);
              break;
            }
          }
        } // end of if(close_it)

      } // end of valid receive
    }   // end of for loop online user
  }     // end of while(1)

  return 0; /* we never get here */
}


void erase_timeout_driver() {

    auto itr = drivers_location.begin();
    for(; itr != drivers_location.end(); itr++ ) {
        if (itr->second.timeout()) 
            //printf("removing %s\n", itr->second.email.c_str());
            //itr = drivers_location.erase(itr);
            itr->second.note = std::string("timeout");
    }
}



char * do_hash( char * sec_ws_key ) {


  SHA1Context sha;
  SHA1Reset( &sha );

  char * combined = (char *)calloc( 1 + strlen( sec_ws_key ) + strlen( GUID ), sizeof( char ) );
  sprintf( combined, "%s%s", sec_ws_key, GUID );
  printf( "HASHING: [%s]\n", combined );

  SHA1Input( &sha, (const unsigned char *)combined, strlen( combined ) );

  if ( SHA1Result( &sha ) ) {

    char * result = (char *)calloc( 128, sizeof( char ) );
    result[0] = '\0';

    int i, j;
    for ( i = 0, j = 0 ; i < 5 ; i++ ) {

      int k;
      char mini[16];
      sprintf( mini, "%08X", sha.Message_Digest[i] );  /* e.g. 99AABBCC */

      for ( k = 0 ; k < 8 ; k += 2 ) {
        result[j++] = hex_to_digit( mini[k] ) * 16 + hex_to_digit( mini[k+1] );
      }
    }

    int output_length = 28;
    char * x = encode_base64( 20, (unsigned char *)result );
    strncpy( result, x, output_length );
    result[ output_length ] = '\0';
    return result;

  } else {

    printf( "Error performing do_hash() function\n" );
    return (char *)NULL;
  }
}
