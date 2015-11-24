#include "driver.h"

driver::driver() {

    latitude = 0;
    longitude = 0;
    fd = -1;
}

driver::driver(int f, const std::string& e, const std::string& n,
               double la, double lo, const std::string& no) {

    fd = f;
    email = e;
    name = n;
    latitude = la;
    longitude = lo;
    note = no;

    // get current time
    time(&last_access_time);
}

bool driver::timeout() {

    time_t current_time;
    time(&current_time);

    return current_time - last_access_time > 1800; // time out is 1800 seconds

}
