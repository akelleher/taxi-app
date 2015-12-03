#include <cmath>
#include <sys/errno.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <cstdio>
#include <unistd.h>
#include <cstring>
#include <cstdlib>
#include <arpa/inet.h>
#include <sys/select.h>
#include <stdint.h>

/* from http://www.packetizer.com/security/sha1/ */
#include "sha1-c/sha1.h"

#include <boost/regex.hpp> /* boost regular expression */
#include <string>          /* c++ string */
#include <unordered_map>   /* c++11 hash table */
#include "coord.h"         /* for coordination */


#define GUID "258EAFA5-E914-47DA-95CA-C5AB0DC85B11"

#define WS_STATUS_CONNECTING 1
#define WS_STATUS_OPEN 2
#define WS_STATUS_CLOSING 3
#define WS_STATUS_CLOSED 4

// global variables
/* change this to a '|' character for testing the SEND command */
char newline = '\n';

boost::regex *verify_coord;
boost::regex *parse_name;
boost::regex *parse_latitude;
boost::regex *parse_longitude;

std::unordered_map<std::string, coord> driver_locations;

struct user {
  int fd;
  int status;
};

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

int process_cmd( int chatuser_index,
                 char * payload,
                 int payload_length,
                 char * response,
                 int * response_length );

int main() {

  //<coord><name>Juan Poma</name><latitude>-73.68</latitude><longitude>42.72</longitude></coord>
  verify_coord = new boost::regex("^<coord><name>([a-zA-Z' ]+)</name><latitude>([-0-9]++[.]+[0-9]+)</latitude><longitude>([-0-9]++[.]+[0-9]+)</longitude></coord>$");

  parse_name = new boost::regex("<name>([a-zA-Z' ]+)</name>");
  parse_latitude = new boost::regex("<latitude>([-0-9]++[.]+[0-9]+)</latitude>");
  parse_longitude = new boost::regex("<longitude>([-0-9]++[.]+[0-9]+)</longitude>");


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

    FD_ZERO( &readfds );
    FD_SET( sock, &readfds );  /* <== incoming new client connections */
    printf( "Set FD_SET to include listener fd %d\n", sock );

    for ( unsigned int i = 0 ; i < online_users.size(); i++ ) {
      FD_SET( online_users[i].fd, &readfds );
      printf( "Set FD_SET to include client socket fd %d\n",
              online_users[i].fd );
    }

    /* BLOCK (but blocked on ALL fds that are set via FD_SET */
    int q = select( FD_SETSIZE, &readfds, NULL, NULL, NULL );

    if ( FD_ISSET( sock, &readfds ) ) {

      /* We know that this accept() call will NOT block */
      newsock = accept( sock, (struct sockaddr *)&client, &fromlen );
      user a_user;
      a_user.fd = newsock; a_user.status = WS_STATUS_CONNECTING;
      online_users.push_back(a_user);
      printf( "Accepted client connection\n" );
    }


    for ( unsigned int i = 0; i < online_users.size(); i++ )
    {
      int fd = online_users[i].fd;

      if ( FD_ISSET( fd, &readfds ) )
      {
        int close_it = 0;
        int broadcast = 0;
        n = recv( fd, buffer, BUFFER_SIZE - 1, 0 );

        if ( n == 0 ) close_it = 1;
        else if ( n < 0 ) {
          perror( "recv()" );
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

            if ( length == 126 || length == 127 )
            {
              printf( "Frame has length %d. Ignoring for now....\n", length );
              close_it = 1;
            }
            else
            {
              char mask_bytes[4];

              if ( mask )
              {
                memcpy( mask_bytes, buffer + payload_start, 4 );
                payload_start += 4;
              }

              if ( n < payload_start + length )
              {
                printf( "Frame is incomplete. Ignoring.\n" );
                close_it = 1;
              }
              else
              {
                char * payload = (char *)calloc( length + 1, sizeof( char ) );
                memcpy( payload, buffer + payload_start, length );

                if ( mask )
                {
                  int i;
                  for ( i = 0 ; i < length ; i++ )
                  {
                    payload[i] ^= mask_bytes[ i % 4 ];
                  }
                }

                if ( opcode == 0x08 )
                {
                  printf( "RCVD CLOSE FRAME\n" );
                  response[1] = 0x02;
                  response[2] = 0x00;  /* echo close back */
                  response[3] = 0x03;
                  response_length += 3;
                }
                else if ( opcode == 0x02 || opcode == 0x01 )
                {
                  printf( "RCVD %s DATA FRAME\n",
                          ( opcode == 0x01 ? "TEXT" : "BINARY" ) );

                  if ( opcode == 0x01 )
                  {
                    printf( "PAYLOAD: [%s]\n", payload );
                  }

                  int whattodo = process_cmd( online_users[i].fd, payload, length, response, &response_length );

                  if ( whattodo == 1 )
                  {
                    close_it = 1;
                  }
                  else if ( whattodo == 2 )
                  {
                    broadcast = 1;
                  }
                  else if ( whattodo > 2 )
                  {
                    fd = whattodo;
                  }
                }
              }
            }
          }
        }

        if ( close_it )
        {
          int k;
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
        }
      }
    }
  }


  return 0; /* we never get here */
}



/* payload is "ME IS <username>\n" etc. */
/* response has first byte set (FIN and OPCODE) */
/* returns whether to do nothing (0), close connection (1),
   broadcast (2), or send a private message (>2) */
int process_cmd( int index,
                 char * payload,
                 int payload_length,
                 char * response,
                 int * response_length ) {

  std::string input = payload;
  printf("length of string: %lu\n", input.length());

  // a driver is updating his location
  if ( regex_match(input, *verify_coord) ) {

    printf("im uploading coordination\n");
    boost::sregex_iterator itr_end;

    // parse user
    boost::sregex_iterator name_itr(input.begin(), input.end(), *parse_name);
    std::string name = name_itr->str();
    name.erase(name.begin(), name.begin()+6);	// remove the beginning tag
    name.erase(name.end()-7, name.end());	// remove the ending tag

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

    printf("name: %s latitude: %f longitude: %f\n", name.c_str(), latitude_num, longitude_num);

    // insert or update the entry hash table
    auto search_result = driver_locations.find(name);
    if (search_result == driver_locations.end())
      driver_locations.insert( {name, coord(latitude_num, longitude_num)} );
    else
      search_result->second = coord(latitude_num, longitude_num);

    strcpy( response + 2, "ACK" );
    response[1] = strlen(response + 2);
    *response_length = strlen(response + 2) + 2;

    send(index, response, *response_length, 0);

  // a dispatcher is gonna get all the locations
  } else if ( !(strcmp(payload, "RETRIEVE_ALL")) ) {

      auto itr = driver_locations.begin();
      for(; itr != driver_locations.end(); itr++) {

        std::string name = itr->first;
        std::string latitude = std::to_string(itr->second.latitude);
        std::string longitude = std::to_string(itr->second.longitude);

        std::string msg;
        msg.append("{\"n\":\"");
        msg.append(name);
        msg.append("\",\"la\":\"");
        msg.append(latitude);
        msg.append("\",\"lo\":\"");
        msg.append(longitude);
        msg.append("\"}");

	printf("sending: %s length: %d\n", msg.c_str(), msg.size());

	strcpy( response+2, msg.c_str());
        response[1] = strlen(response + 2);
        *response_length = strlen(response+2)+2;

        //printf()
        send(index, response, *response_length, 0);

      }

  } else {

    std::string e = "Command Not Found";

    strcpy( response+2, e.c_str());
    response[1] = strlen(response + 2);
    *response_length = strlen(response+2)+2;

    send(index, response, *response_length, 0);

  }

  return 0;
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
