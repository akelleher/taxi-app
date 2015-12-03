package segfault.taxiping;

import android.content.Context;
import android.location.GpsStatus;
import android.location.Location;
import android.location.LocationListener;
import android.location.LocationManager;
import android.location.LocationProvider;
import android.os.Bundle;
import android.webkit.JavascriptInterface;
import android.widget.EditText;
import android.widget.Toast;

import java.text.SimpleDateFormat;

/**
 * Created by Vrsus on 2015/11/1.
 */
public class WebAppInterface {

    Context mContext;
    LocationManager locMgr;
    Location locativus;
    String GPS_Provider;
    GpsStatus.Listener GPS_Listener;
    LocationListener locListener;
    double latestLatitude;
    double latestLongitude;
    long tempus;


    WebAppInterface(Context c) {
        mContext = c;

        // initialize listener - GPS Listener
        // specify its behavior in different situations
        GPS_Listener = new GpsStatus.Listener() {
            @Override
            public void onGpsStatusChanged(int event) {
                switch (event) {
                    case GpsStatus.GPS_EVENT_STARTED:
                        //Log.d(TAG, "GPS_EVENT_STARTED");
                        Toast.makeText(mContext, "GPS_EVENT_STARTED", Toast.LENGTH_SHORT).show();
                        break;
                    case GpsStatus.GPS_EVENT_STOPPED:
                        //Log.d(TAG, "GPS_EVENT_STOPPED");
                        Toast.makeText(mContext, "GPS_EVENT_STOPPED", Toast.LENGTH_SHORT).show();
                        break;
                    case GpsStatus.GPS_EVENT_FIRST_FIX:
                        //Log.d(TAG, "GPS_EVENT_FIRST_FIX");
                        Toast.makeText(mContext, "GPS_EVENT_FIRST_FIX", Toast.LENGTH_SHORT).show();
                        break;
                    case GpsStatus.GPS_EVENT_SATELLITE_STATUS:
                        //Log.d(TAG, "GPS_EVENT_SATELLITE_STATUS");
                        break;
                }
            }
        };

        // initialize listener - location listener
        // notify user when the state of GPS module is changed
        locListener = new LocationListener() {
            @Override
            public void onLocationChanged(Location location) {
                updateWithNewLocation(location);
            }

            @Override
            public void onStatusChanged(String provider, int status, Bundle extras) {
                switch (status) {
                    case LocationProvider.OUT_OF_SERVICE:
                        //Log.v(TAG, "Status Changed: Out of Service");
                        Toast.makeText(mContext, "Status Changed: Out of Service", Toast.LENGTH_SHORT).show();
                        break;
                    case LocationProvider.TEMPORARILY_UNAVAILABLE:
                        //Log.v(TAG, "Status Changed: Temporarily Unavailable");
                        Toast.makeText(mContext, "Status Changed: Temporarily Unavailable", Toast.LENGTH_SHORT).show();
                        break;
                    case LocationProvider.AVAILABLE:
                        //Log.v(TAG, "Status Changed: Available");
                        Toast.makeText(mContext, "Status Changed: Available", Toast.LENGTH_SHORT).show();
                        break;
                }
            }

            @Override
            public void onProviderEnabled(String provider) {
                updateWithNewLocation(locativus);
            }

            @Override
            public void onProviderDisabled(String provider) {
                updateWithNewLocation(locativus);
            }
        };

        // initialize GPS module
        if (initLocationProvider()) {
            whereAmI();
        } else {
            Toast.makeText(mContext,
                    "Please Turn on Your GPS Location Service",
                    Toast.LENGTH_LONG).show();
        }
    }

    // initialize location manager. select between network gps or native gps
    boolean initLocationProvider() {

        locMgr = (LocationManager)mContext.getSystemService(Context.LOCATION_SERVICE);

        // this one is slower
        if (locMgr.isProviderEnabled(LocationManager.GPS_PROVIDER)) {
            GPS_Provider = LocationManager.GPS_PROVIDER;
            return true;
        }

        // this one faster but not always available
        if (locMgr.isProviderEnabled(LocationManager.NETWORK_PROVIDER)) {
            GPS_Provider = LocationManager.NETWORK_PROVIDER;
            return true;
        }

        return false;
    }

    void whereAmI() {

        // Get Last Known Location
        locativus = locMgr.getLastKnownLocation(GPS_Provider);
        updateWithNewLocation(locativus);

        //GPS Listener
        locMgr.addGpsStatusListener(GPS_Listener);

        //Location Listener
        int minTime = 3000;//ms
        int minDist = 1;//meter
        locMgr.requestLocationUpdates(GPS_Provider, minTime, minDist, locListener);
    }

    // store the location data into variables
    void updateWithNewLocation(Location location) {

        if (location != null) {

            latestLongitude = location.getLongitude();

            latestLatitude = location.getLatitude();

            //float speed = location.getSpeed();
            tempus = location.getTime();
        }
    }

    // java warpper functions for using android gps module
    @JavascriptInterface
    public double getLatitude() { return latestLatitude; }

    @JavascriptInterface
    public double getLongitude() {
        return latestLongitude;
    }

    @JavascriptInterface
    public String getTime() {
        SimpleDateFormat format = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        return format.format(tempus);
    }
}
