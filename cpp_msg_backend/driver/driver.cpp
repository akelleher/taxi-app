#include "driver.h"

driver::driver() {

    latitude = 0;
    longitude = 0;
}

driver::driver(const std::string& e, const std::string& n, double la, double lo) {

    email = e;
    name = n;
    latitude = la;
    longitude = lo;

    // get current time
    time(&last_access_time);
}

bool driver::timeout() {

    time_t current_time;
    time(&current_time);

    return current_time - last_access_time > 1800; // time out is 1800 seconds

}
