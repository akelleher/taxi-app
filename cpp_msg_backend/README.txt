This is the c++ chatserver code for Taxiping. 

Compiling:

  One needs to install C++ boost library before compiling this code. C++11 is also required. Using compiler g++ with version newer than 4.8.0 is recommended. Compile command is in Makefile. One can simple run command "make all" in terminal to compile and "./chatserver" to start the server. The server is listening on TCP port 8787 on default.

chatserver.cpp:
	contains the main function of the whole program. The main function defines several global variables and start the TCP server.

driver/
	define object driver use to store all the data related to a single driver.

cmd_process/
	define functions that handle incoming request and send response.

websocket_func/
	define functions that decode data from websocket protocol to plain text and vice versa.

base64.c and sha1-c/
	third party open source library for encoding/decoding base64 data and sha1 encryption.

test_regex.cpp
	a tiny program can test regular expression.

