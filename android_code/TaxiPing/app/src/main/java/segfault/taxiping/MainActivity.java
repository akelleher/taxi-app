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

    double latestLongitude;
    double latestLatitude;
    long time;

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

        // start internal web browser
        myWebView = (WebView) findViewById(R.id.webview);
        myWebView.loadUrl("file:///sdcard/Download/android_sample.html");
        myWebView.getSettings().setJavaScriptEnabled(true);
        // prevent calling chrome when click on links on the webpage
        myWebView.setWebViewClient(new WebViewClient());
        myWebView.addJavascriptInterface(new WebAppInterface(this),"Android");
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


}
