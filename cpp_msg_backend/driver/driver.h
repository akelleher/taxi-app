#ifndef _DRIVER_H_
#define _DRIVER_H_

#include <string>
#include <ctime>

class driver {

    public:

        driver();
        driver( int f, const std::string& e, const std::string& n, double la, double lo, const std::string& no);

        bool timeout();

        int fd;
        std::string email;
        std::string name;
        double latitude;
        double longitude;
        std::string note;
        std::string addr;

        time_t last_access_time;

};





#endif
