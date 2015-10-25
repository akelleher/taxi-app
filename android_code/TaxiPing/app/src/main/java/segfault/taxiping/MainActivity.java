package segfault.taxiping;

import android.app.Activity;
import android.content.Context;
import android.location.GpsStatus;
import android.location.Location;
import android.location.LocationListener;
import android.location.LocationManager;
import android.location.LocationProvider;
import android.support.v7.app.ActionBarActivity;
import android.os.Bundle;
import android.view.KeyEvent;
import android.view.Menu;
import android.view.MenuItem;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.EditText;
import android.widget.Toast;

import java.text.SimpleDateFormat;


public class MainActivity extends Activity {

    WebView myWebView;

    LocationManager locMgr;
    String GPS_Provider;
    GpsStatus.Listener GPS_Listener;
    LocationListener locListener;
    Location locativus;

    @Override
    public void onBackPressed(){
        if(myWebView.canGoBack())
            myWebView.goBack();
        else super.onBackPressed();
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {

        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        // initialize listener - GPS Listener
        GPS_Listener = new GpsStatus.Listener() {
            @Override
            public void onGpsStatusChanged(int event) {
                switch (event) {
                    case GpsStatus.GPS_EVENT_STARTED:
                        //Log.d(TAG, "GPS_EVENT_STARTED");
                        Toast.makeText(MainActivity.this, "GPS_EVENT_STARTED", Toast.LENGTH_SHORT).show();
                        break;
                    case GpsStatus.GPS_EVENT_STOPPED:
                        //Log.d(TAG, "GPS_EVENT_STOPPED");
                        Toast.makeText(MainActivity.this, "GPS_EVENT_STOPPED", Toast.LENGTH_SHORT).show();
                        break;
                    case GpsStatus.GPS_EVENT_FIRST_FIX:
                        //Log.d(TAG, "GPS_EVENT_FIRST_FIX");
                        Toast.makeText(MainActivity.this, "GPS_EVENT_FIRST_FIX", Toast.LENGTH_SHORT).show();
                        break;
                    case GpsStatus.GPS_EVENT_SATELLITE_STATUS:
                        //Log.d(TAG, "GPS_EVENT_SATELLITE_STATUS");
                        break;
                }
            }
        };

        // initialize listener - location listener
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
                        Toast.makeText(MainActivity.this, "Status Changed: Out of Service", Toast.LENGTH_SHORT).show();
                        break;
                    case LocationProvider.TEMPORARILY_UNAVAILABLE:
                        //Log.v(TAG, "Status Changed: Temporarily Unavailable");
                        Toast.makeText(MainActivity.this, "Status Changed: Temporarily Unavailable", Toast.LENGTH_SHORT).show();
                        break;
                    case LocationProvider.AVAILABLE:
                        //Log.v(TAG, "Status Changed: Available");
                        Toast.makeText(MainActivity.this, "Status Changed: Available", Toast.LENGTH_SHORT).show();
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
            Toast toast = Toast.makeText(getApplicationContext(),
                    "Please Turn on Your GPS Location Service",
                    Toast.LENGTH_LONG);
        }
        System.out.println("yayayayaya");
/*
        myWebView = (WebView) findViewById(R.id.webview);
        myWebView.loadUrl("http://www.bloomberg.com");
        myWebView.getSettings().setJavaScriptEnabled(true);
        // prevent calling chrome when click on links on the webpage
        myWebView.setWebViewClient(new WebViewClient());
*/
    }


    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.menu_main, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
        int id = item.getItemId();

        //noinspection SimplifiableIfStatement
        if (id == R.id.action_settings) {
            return true;
        }

        return super.onOptionsItemSelected(item);
    }

    boolean initLocationProvider() {

        locMgr = (LocationManager)getSystemService(Context.LOCATION_SERVICE);

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

    void updateWithNewLocation(Location location) {

        if (location != null) {

            double logt = location.getLongitude();
            EditText first = (EditText) findViewById(R.id.longitude);
            first.setText( Double.toString(logt) );

            double latt = location.getLatitude();
            EditText second = (EditText) findViewById(R.id.latitude);
            second.setText( Double.toString(latt) );

            //float speed = location.getSpeed();
            long time = location.getTime();
            SimpleDateFormat format = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
            EditText third = (EditText) findViewById(R.id.date_time);
            third.setText( format.format(time));

        }
    }
}
