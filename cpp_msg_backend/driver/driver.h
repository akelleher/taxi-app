#ifndef _DRIVER_H_
#define _DRIVER_H_

#include <string>
#include <ctime>

class driver {

    public:

        driver();
        driver( const std::string& e, const std::string& n, double la, double lo);

        bool timeout();

        std::string email;
        std::string name;
        double latitude;
        double longitude;
        time_t last_access_time;

};





#endif
