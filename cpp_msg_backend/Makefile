all:
	g++ -Wall chat-server.cpp base64.c sha1-c/sha1.c websocket_func/websocket_func.cpp driver/driver.cpp -o chat-server -lboost_regex -std=c++11

test_regex:
	g++ -Wall test_regex.cpp -o regex -lboost_regex
