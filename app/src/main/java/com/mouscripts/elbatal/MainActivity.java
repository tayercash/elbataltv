package com.mouscripts.elbatal;

import static android.webkit.WebView.RENDERER_PRIORITY_BOUND;
import static org.apache.http.conn.ssl.SSLSocketFactory.SSL;

import android.Manifest;
import android.annotation.SuppressLint;
import android.app.Dialog;
import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.content.ActivityNotFoundException;
import android.content.ComponentName;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.pm.ActivityInfo;
import android.content.pm.PackageInfo;
import android.content.pm.PackageManager;
import android.content.pm.Signature;
import android.content.res.Configuration;
import android.content.res.Resources;
import android.database.ContentObserver;
import android.database.Cursor;
import android.media.AudioManager;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.net.Uri;
import android.net.http.SslError;
import android.os.Build;
import android.os.Bundle;
import android.os.Environment;
import android.os.Handler;
import android.os.Looper;
import android.provider.Settings;
import android.text.ClipboardManager;
import android.text.InputType;
import android.util.DisplayMetrics;
import android.util.Log;
import android.view.KeyEvent;
import android.view.View;
import android.view.Window;
import android.view.WindowManager;
import android.webkit.CookieManager;
import android.webkit.JavascriptInterface;
import android.webkit.JsPromptResult;
import android.webkit.JsResult;
import android.webkit.SslErrorHandler;
import android.webkit.ValueCallback;
import android.webkit.WebChromeClient;
import android.webkit.WebResourceRequest;
import android.webkit.WebResourceResponse;
import android.webkit.WebSettings;
import android.webkit.WebStorage;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.EditText;
import android.widget.FrameLayout;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.Toast;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.annotation.NonNull;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.app.ActivityCompat;
import androidx.core.content.ContextCompat;
import androidx.core.content.FileProvider;
import androidx.webkit.WebViewCompat;

import com.google.android.gms.auth.api.signin.GoogleSignIn;
import com.google.android.gms.auth.api.signin.GoogleSignInAccount;
import com.google.android.gms.auth.api.signin.GoogleSignInClient;
import com.google.android.gms.auth.api.signin.GoogleSignInOptions;
import com.google.android.gms.common.api.ApiException;
import com.google.android.gms.tasks.OnCompleteListener;
import com.google.android.gms.tasks.Task;

import com.google.firebase.analytics.FirebaseAnalytics;
import com.google.firebase.messaging.FirebaseMessaging;
import com.unity3d.ads.IUnityAdsInitializationListener;
import com.unity3d.ads.IUnityAdsLoadListener;
import com.unity3d.ads.IUnityAdsShowListener;
import com.unity3d.ads.UnityAds;
import com.unity3d.ads.UnityAdsShowOptions;

import org.apache.commons.io.FileUtils;
import org.jetbrains.annotations.NotNull;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedInputStream;
import java.io.BufferedOutputStream;
import java.io.BufferedReader;
import java.io.ByteArrayInputStream;
import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.io.OutputStreamWriter;
import java.io.UnsupportedEncodingException;
import java.net.HttpURLConnection;
import java.net.URI;
import java.net.URL;
import java.net.URLDecoder;
import java.nio.charset.StandardCharsets;
import java.security.KeyManagementException;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.security.cert.CertificateEncodingException;
import java.security.cert.CertificateException;
import java.security.cert.CertificateFactory;
import java.security.cert.X509Certificate;
import java.time.LocalDateTime;
import java.time.ZoneOffset;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Base64;
import java.util.Date;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.Map;
import java.util.Objects;
import java.util.Set;
import java.util.concurrent.TimeUnit;
import java.util.zip.GZIPOutputStream;
import java.util.zip.ZipEntry;
import java.util.zip.ZipInputStream;

import javax.net.ssl.HostnameVerifier;
import javax.net.ssl.HttpsURLConnection;
import javax.net.ssl.SSLContext;
import javax.net.ssl.SSLSession;
import javax.net.ssl.SSLSocketFactory;
import javax.net.ssl.TrustManager;
import javax.net.ssl.X509TrustManager;

import okhttp3.CacheControl;
import okhttp3.Call;
import okhttp3.Callback;
import okhttp3.FormBody;
import okhttp3.Headers;
import okhttp3.OkHttpClient;
import okhttp3.Request;
import okhttp3.RequestBody;
import okhttp3.Response;
import okhttp3.ResponseBody;
import android.content.SharedPreferences;

public class MainActivity extends AppCompatActivity implements IUnityAdsInitializationListener {

    private final HashMap<String, String> Custom_vars = new HashMap<String, String>();
    private static final String TAG = "MainActivity";

    private WebView webView;

    private FrameLayout fullScreenContainer;
    private View customView;
    private WebChromeClient.CustomViewCallback customViewCallback;

    private WebView HelperWebView;
    GoogleSignInOptions gso;
    GoogleSignInClient gsc;
    public Dialog helper_dialog = null;
    public boolean HelperWebView_loded = false;
    private String IntentExtra_string = "";
    private String Share_data = "";
    private String Share_or_notify = "0";
    private String elplayer_dl_link = "https://www.elbatal-app.com/p/elplayer.html";

    private ActivityResultLauncher<Intent> activityResultLauncher;

    private final String unityGameID = "5002537";
    private final Boolean testMode = false;
    private String mou_Aauth0 = "IFV3IGR3MVR3JGN3";
    private String mou_Aauth1 = "JaF3Nap3MWJ3NWR3";
    private String mou_Aauth2 = "JGp3MGZ3IWJ3NGt3";
    private String mou_Aauth3 = "";
    private static final String Mou_Key = "c!xZj+N9&G@Ev@vw";
    private Map<String, String> requestHeaders = new HashMap<>();
    private Map<String, String> ResponseHeaders = new HashMap<>();

    @Override
    public void onConfigurationChanged(@NonNull Configuration newConfig) {
        super.onConfigurationChanged(newConfig);
    }

    @Override
    protected void onSaveInstanceState(@NonNull Bundle outState) {
        super.onSaveInstanceState(outState);
        webView.saveState(outState);
    }

    @Override
    protected void onRestoreInstanceState(@NonNull Bundle savedInstanceState) {
        super.onRestoreInstanceState(savedInstanceState);
        webView.restoreState(savedInstanceState);
    }
    private FirebaseAnalytics mFirebaseAnalytics;


    @SuppressLint({"SetJavaScriptEnabled", "AddJavascriptInterface"})
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);



        // Main layout with FrameLayout to hold fullscreen view
        FrameLayout root = new FrameLayout(this);
        fullScreenContainer = new FrameLayout(this);
        fullScreenContainer.setVisibility(View.GONE);

        disableSSLCertificateChecking();
        logRegToken();
        mFirebaseAnalytics = FirebaseAnalytics.getInstance(this);

        Custom_vars.put("u_i_a", "video");
        Custom_vars.put("u_r_a", "Rewarded_Android");
        Custom_vars.put("e_m", "true");
        Custom_vars.put("e_f_v_m", "true");
        Custom_vars.put("e_v_m", "false");
        Custom_vars.put("f_v_m", "false");
        Custom_vars.put("AD_TAG_URI", "");

        try {

            Uri vid_position_uri = Uri.parse("content://com.mouscripts.bplayer.provider/vid_position");
            ContentObserver observer = new ContentObserver(new Handler(Looper.getMainLooper())) {
                @Override
                public void onChange(boolean selfChange) {
                    super.onChange(selfChange);
                    // Re-query to get the new value
                    Cursor cursor = getContentResolver().query(vid_position_uri, null, null, null, null);
                    if (cursor != null && cursor.moveToFirst()) {
                        String value = cursor.getString(cursor.getColumnIndexOrThrow("vid_position"));
                        cursor.close();
                        Log.d("AppB", "Updated vid_positions: " + value);
                    }
                }
            };

// Register the observer
            getContentResolver().registerContentObserver(vid_position_uri, true, observer);

            Cursor cursor = getContentResolver().query(
                    vid_position_uri,
                    null, null, null, null
            );

            if (cursor != null && cursor.moveToFirst()) {
                String value = cursor.getString(cursor.getColumnIndexOrThrow("vid_position"));
                cursor.close();

                Log.d("AppB", "Received vid_positions: " + value);
            }
        } catch (IllegalArgumentException | SecurityException e) {
            // Handle provider not found or permission denied
            Log.e("ProviderAccess", "Failed to access provider: " + e.getMessage());
        }
        gso = new GoogleSignInOptions.Builder(GoogleSignInOptions.DEFAULT_SIGN_IN).requestEmail().build();
        gsc = GoogleSignIn.getClient(this, gso);

        activityResultLauncher = registerForActivityResult(
                new ActivityResultContracts.StartActivityForResult(),
                result -> {
                    if (result.getResultCode() == RESULT_OK) {
                        Intent data = result.getData();
                        if (data != null && data.hasExtra("Active_RESUlt")) {
                            boolean received = data.getBooleanExtra("Active_RESUlt",false);
                            runJsCode("ActiveElbatalTv_RESUlt("+received+")");
//                            Toast.makeText(this, "Received: " + received, Toast.LENGTH_LONG).show();
                        }
                    }
                }
        );

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            // Create channel to show notifications.
            String channelId = getString(R.string.default_notification_channel_id);
            String channelName = getString(R.string.default_notification_channel_name);
            NotificationManager notificationManager =
                    getSystemService(NotificationManager.class);
            notificationManager.createNotificationChannel(new NotificationChannel(channelId,
                    channelName, NotificationManager.IMPORTANCE_HIGH));
        }
        mou_Aauth3 = "NFJ3IWJ3Nat3JGt3";

        supportRequestWindowFeature(Window.FEATURE_NO_TITLE); //will hide the title
//        Objects.requireNonNull(getSupportActionBar()).hide(); // hide the title bar
        setFullscreen(false);
        getWindow().setFlags(
                WindowManager.LayoutParams.FLAG_HARDWARE_ACCELERATED,
                WindowManager.LayoutParams.FLAG_HARDWARE_ACCELERATED);
//        setContentView(R.layout.activity_main);

        MainActivity.this.setRequestedOrientation(ActivityInfo.SCREEN_ORIENTATION_UNSPECIFIED);

//        if(isEmulator){
//            MainActivity.this.setRequestedOrientation(ActivityInfo.SCREEN_ORIENTATION_UNSPECIFIED);
//        } else {
//            MainActivity.this.setRequestedOrientation(ActivityInfo.SCREEN_ORIENTATION_PORTRAIT);
//        }


//        if (getPackageManager().hasSystemFeature(PackageManager.FEATURE_TELEVISION)
//                || getPackageManager().hasSystemFeature(PackageManager.FEATURE_LEANBACK)) {
//            this.setRequestedOrientation(ActivityInfo.SCREEN_ORIENTATION_LANDSCAPE);
//        } else {
//            this.setRequestedOrientation(ActivityInfo.SCREEN_ORIENTATION_PORTRAIT);
//        }
        final String databasePath = MainActivity.this.getApplicationContext().getDir("database", Context.MODE_PRIVATE).getPath();
//        webView = findViewById(R.id.WebView);

        webView = new WebView(this);
        root.addView(webView, new FrameLayout.LayoutParams(
                FrameLayout.LayoutParams.MATCH_PARENT, FrameLayout.LayoutParams.MATCH_PARENT));
        root.addView(fullScreenContainer, new FrameLayout.LayoutParams(
                FrameLayout.LayoutParams.MATCH_PARENT, FrameLayout.LayoutParams.MATCH_PARENT));

        setContentView(root);

        WebSettings webSettings = webView.getSettings();
        webSettings.setJavaScriptEnabled(true);
        webView.setHapticFeedbackEnabled(false);
        webView.setHorizontalScrollBarEnabled(false);
        webView.setVerticalScrollBarEnabled(false);
        webView.getSettings().setTextZoom(100);
        webView.getSettings().setRenderPriority(WebSettings.RenderPriority.HIGH);
        CookieManager.getInstance().setAcceptThirdPartyCookies(webView, true);
        webView.getSettings().setUserAgentString("Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36");
//        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
//            @SuppressLint("WebViewApiAvailability")
//            PackageInfo info = WebView.getCurrentWebViewPackage();
//            Log.d(TAG, "onCreate: info.versionName => " + info.versionName);
//            Log.d(TAG, "version code: " + info.versionCode);
//        } else {
//            PackageManager pm = getPackageManager();
//            try {
//                PackageInfo pi = pm.getPackageInfo("com.google.android.webview", 0);
//                Log.d(TAG, "version name: " + pi.versionName);
//                Log.d(TAG, "version code: " + pi.versionCode);
//            } catch (PackageManager.NameNotFoundException e) {
//                Log.e(TAG, "Android System WebView is not found");
//            }
//        }

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            webView.setRendererPriorityPolicy(RENDERER_PRIORITY_BOUND, true);
        }
        webSettings.setDatabasePath(databasePath);
        webSettings.setAllowFileAccessFromFileURLs(true);
        webSettings.setSavePassword(false);
        webSettings.setSaveFormData(false);
        webSettings.setAllowUniversalAccessFromFileURLs(true);
        webSettings.setAllowFileAccess(true);
        webView.addJavascriptInterface(new WebAppInterface(this), "mouscripts");
//        webView.setWebViewClient(new WebViewClient());
        webSettings.setJavaScriptEnabled(true);

        webSettings.setMediaPlaybackRequiresUserGesture(false);
        webSettings.setJavaScriptCanOpenWindowsAutomatically(true);
        webSettings.setDomStorageEnabled(true);
        webSettings.setDatabaseEnabled(true);
        webSettings.setLoadsImagesAutomatically(true);
        webSettings.setUseWideViewPort(true);
        webSettings.setCacheMode(WebSettings.LOAD_NO_CACHE);
        webSettings.setSupportZoom(false);
        webSettings.setSupportMultipleWindows(true);
        webSettings.setSaveFormData(true);

        webView.setLayerType(View.LAYER_TYPE_HARDWARE, null);
        webView.setOverScrollMode(View.OVER_SCROLL_NEVER);
        webView.setFocusable(true);


        webView.requestFocus();
        webView.clearCache(true);

        PackageInfo mWebViewInfo = WebViewCompat.getCurrentWebViewPackage(getApplicationContext());
        
        webView.setWebChromeClient(new MyWebChromeClient());

        // [END handle_data_extras]
        if (savedInstanceState != null) {
            webView.restoreState(savedInstanceState);
        } else {

            if (getIntent().getExtras() != null) {
                JSONObject json = new JSONObject();

                for (String key : getIntent().getExtras().keySet()) {
                    Object value = getIntent().getExtras().get(key);
                    try {
                        json.put(key, JSONObject.wrap(value));
                    } catch (JSONException e) {
                        throw new RuntimeException(e);
                    }
                }
                byte[] res = String.valueOf(json).getBytes();
                IntentExtra_string = Arrays.toString(res);
                Share_or_notify = "2";
//            Log.d(TAG, "onNewIntent: json => " + IntentExtra_string);


            }


            Uri uri = getIntent().getData();
            //        Log.d(TAG, "onCreate: " + uri);
            // checking if the uri is null or not.
            if (uri != null) {
                String args = uri.getQuery();
                Share_data = args;
                Share_or_notify = "1";
                if (args != null) {
                    webView.loadUrl("http://192.168.1.16:8080/newbatal/index.html?" + args);
                }
            } else {
                webView.loadUrl("http://192.168.1.16:8080/newbatal/index.html");
            }
        }

        webView.setWebViewClient(new WebViewClient() {
            @Override
            public WebResourceResponse shouldInterceptRequest(WebView view, WebResourceRequest request) {
                Map<String, String> requestHeadersss = request.getRequestHeaders();

                if (!(requestHeadersss.containsKey("MOuCustomREQUEST") && "NICE".equals(requestHeadersss.get("MOuCustomREQUEST")))) {
                    return null; // Let the WebView handle the request normally
                }
                requestHeadersss.remove("MOuCustomREQUEST");

                String url = request.getUrl().toString();
                String method = request.getMethod();
                InputStream inputStream = null;
                Response response = null;

                try {
                    RequestBody formBody = null;
                    if (method.equalsIgnoreCase("POST")) {
                        // Use a placeholder body if needed (cannot access POST data here directly)
                        Map<String, String> params = getQueryParams(url);
                        // Build the form body with the extracted parameters
                        FormBody.Builder formBuilder = new FormBody.Builder();
                        for (Map.Entry<String, String> entry : params.entrySet()) {
                            formBuilder.add(entry.getKey(), entry.getValue());
                        }
                        formBody = formBuilder.build();

                        URI uri = new URI(url);
                        URI newUri = new URI(uri.getScheme(), uri.getAuthority(), uri.getPath(), null, uri.getFragment());
                        url = newUri.toString();
                    }
                    // Build the OkHttp request
                    Request.Builder requestBuilder = new Request.Builder().url(url)
                            .header("Cache-Control", "no-cache"); // Disable caching

                    Log.d(TAG, "shouldInterceptRequest: requestHeaders => " + requestHeaders);
                    // Add custom headers if provided
                    requestHeadersss.putAll(requestHeaders);
                    for (Map.Entry<String, String> header : requestHeadersss.entrySet()) {
                        requestBuilder.addHeader(header.getKey(), header.getValue());
                    }
//                    requestBuilder.removeHeader("sec-ch-ua-platform"); // Remove the header
//                    requestBuilder.removeHeader("sec-ch-ua-mobile"); // Remove the header
//                    requestBuilder.removeHeader("sec-ch-ua"); // Remove the header

                    OkHttpClient okHttpClient = getUnsafeOkHttpClient();

                    if (method.equalsIgnoreCase("POST")) {
                        assert formBody != null;
                        requestBuilder.post(formBody);
                    } else {
                        requestBuilder.get();
                    }

                    // Execute the request
                    response = okHttpClient.newCall(requestBuilder.build()).execute();

                    // Get the InputStream from the response
                    ResponseBody body = response.body();
                    inputStream = body.byteStream();

                    // Get content type and encoding from the response headers
                    String contentType = response.header("Content-Type", "application/octet-stream");
                    String encoding = response.header("Content-Encoding", "UTF-8");

                    StringBuilder HeadersString = new StringBuilder();
                    Set<String> headerNamesSet = response.headers().names(); // Assuming this returns a Set<String>
                    List<String> headerNames = new ArrayList<>(headerNamesSet); // Convert Set to List

                    for (int i = 0; i < headerNames.size(); i++) {
                        String headerName = headerNames.get(i);
                        HeadersString.append(headerName).append(": ").append(response.header(headerName));
                        HeadersString.append("\n");
                    }
                    ResponseHeaders.put(get_MouPerfect(url, true), get_MouPerfect(String.valueOf(HeadersString), true));

                    return new WebResourceResponse(
                            contentType,
                            encoding,
                            inputStream
                    );

                } catch (Exception e) {
                    e.printStackTrace();
                }
                return null; // Return null if something goes wrong
            }

            @Override
            public void onReceivedError(WebView view, int errorCode,
                                        String description, String failingUrl) {
                if (errorCode == -1) {
                    Log.d(TAG, "onReceivedError: Oh no! " + errorCode);
                    webView.loadUrl("http://192.168.1.16:8080/newbatal/404.html");
                }
            }

            @Override
            public void onPageFinished(WebView view, String url) {
                super.onPageFinished(view, url);

                // Mark WebView as loaded
                SharedPreferences prefs = getSharedPreferences("AppPrefs", MODE_PRIVATE);
                prefs.edit().putBoolean("webViewLoaded", true).apply();

                if (url.equals("http://192.168.1.16:8080/newbatal/index.html")) {
//                    try {
//                        get_google_login_data();
//                    } catch (UnsupportedEncodingException e) {
//                        throw new RuntimeException(e);
//                    }
                }
//                if(getPackageManager().hasSystemFeature(PackageManager.FEATURE_TELEVISION)
//                        || getPackageManager().hasSystemFeature(PackageManager.FEATURE_LEANBACK)){
//
//                } else {
//            }
                if (Custom_vars.get("e_m").compareTo("true") == 0) {
                    UnityAds.initialize(getApplicationContext(), unityGameID, testMode, MainActivity.this);
                }

            }

            @SuppressLint("WebViewClientOnReceivedSslError")
            @Override
            public void onReceivedSslError(WebView view, SslErrorHandler handler, SslError error) {
                handler.proceed();
            }

        });



    }

    @Override
    protected void onResume() {
        super.onResume();
        // Show the ad when returning to the activity if it's loaded
//        showAdmobInteritial();
//        IntentFilter filter = new IntentFilter("com.example.sockets.NOTIFICATION");
//        registerReceiver(notificationReceiver, filter);
    }


    @Override
    public boolean onKeyDown(int keyCode, KeyEvent event) {
//        if (event.getAction() == KeyEvent.ACTION_DOWN) {
//            switch (keyCode) {
//                case KeyEvent.KEYCODE_BACK:
//                    if (webView.canGoBack()) {
//                        webView.goBack();
//                    } else {
//                        finish();
//                    }
//                    return true;
//            }
//        }
        if (event.getAction() == KeyEvent.ACTION_DOWN) {
            if (keyCode == KeyEvent.KEYCODE_BACK) {
                webView.loadUrl("javascript:back_button_clicked();");
                return true;
            }

        }
        return super.onKeyDown(keyCode, event);
    }

    void G_signIn() {
        Intent signInIntent = gsc.getSignInIntent();
        startActivityForResult(signInIntent, 1000);
    }

    void G_signOut() {
        gsc.signOut().addOnCompleteListener(new OnCompleteListener<Void>() {
            @Override
            public void onComplete(Task<Void> task) {

            }
        });
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        Log.d(TAG, "onActivityResult: " + resultCode);
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == 1000) {
            Task<GoogleSignInAccount> task = GoogleSignIn.getSignedInAccountFromIntent(data);
            try {
                task.getResult(ApiException.class);
                get_google_login_data();
            } catch (ApiException e) {
                runJsCode("on_google_signed_error();");
                Log.e(TAG, "onActivityResult: " + e);
//                Toast.makeText(getApplicationContext(), "Something went wrong", Toast.LENGTH_SHORT).show();
            } catch (UnsupportedEncodingException e) {
                throw new RuntimeException(e);
            }
        }


    }

    void get_google_login_data() throws UnsupportedEncodingException {

        GoogleSignInAccount acct = GoogleSignIn.getLastSignedInAccount(MainActivity.this);
        if (acct != null) {
            String personID = get_MouPerfect(acct.getId(), true);
            String personName = acct.getDisplayName();
            String personEmail = get_MouPerfect(acct.getEmail(), true);
            Uri personImg = acct.getPhotoUrl();

            runJsCode("on_google_signed_success('" + personID + "','" + personName + "','" + personEmail + "','" + personImg + "');");
        }
    }

    public class WebAppInterface {
        Context mContext;

        WebAppInterface(Context c) {
            mContext = c;
        }

        @JavascriptInterface
        public void saveFile(String content, String fileName) {
            try {
                FileOutputStream fos = openFileOutput(fileName, MODE_PRIVATE);
                fos.write(content.getBytes());
                fos.close();
                // Optional: Log the file path
                String filePath = getFilesDir() + "/" + fileName;
                Log.d("FilePath", "Saved to: " + filePath);
            } catch (IOException e) {
                e.printStackTrace();
            }
        }

        @JavascriptInterface
        public String getResponseHeaders(String url) throws UnsupportedEncodingException {
//            Log.d(TAG, "getResponseHeaders: URL WANT TO GET ITS HEADERS => " + url);

            url = get_MouPerfect(url, true);
            if (ResponseHeaders.containsKey(url)) {
                return ResponseHeaders.get(url);
            }
            return null;
        }

        @JavascriptInterface
        public void removeResponseHeaders(String url) {
            if (ResponseHeaders.containsKey(url)) {
                ResponseHeaders.remove(url);
                Log.d(TAG, "removeResponseHeaders: ResponseHeaders => " + ResponseHeaders);
            }
        }

        @JavascriptInterface
        public void updateHeaders(String StringHeaders) throws JSONException {
            JSONObject jsonObject = new JSONObject(StringHeaders);
            // Iterate over the JSONObject and put each key-value pair into the map
            requestHeaders.clear();
            Iterator<String> keys = jsonObject.keys();
            while (keys.hasNext()) {
                String key = keys.next();
                requestHeaders.put(key, jsonObject.getString(key));
            }
//            requestHeaders.put("Content-Type", "application/json");

        }

        @JavascriptInterface
        public void showHTML(String html) {
            byte[] res = html.getBytes();
            String html_bytes = Arrays.toString(res);
            runJsCode("html_on_helper_webview('" + html_bytes + "')");
        }

        @JavascriptInterface
        public void ajax(String data) throws JSONException {
            String Type;
            String Url;
            JSONObject json_custom_headers;
            Iterator<String> iter_headers = null;
            Map<String, String> headers = null;

            JSONObject json_data;
            Iterator<String> iter_json_data = null;
            Map<String, String> data_parameter = null;

            String OnSuccess = null;

            String res = null;
            JSONObject data_json = new JSONObject(data);
            if (data_json.has("url")) {
                Url = data_json.getString("url");
                Type = data_json.has("type") ? data_json.getString("type").toUpperCase() : "GET";

                headers = new HashMap<String, String>();
                if (data_json.has("headers")) {
                    json_custom_headers = new JSONObject(data_json.getString("headers"));
                    iter_headers = json_custom_headers.keys();
                    while (iter_headers.hasNext()) {
                        String key = iter_headers.next();
                        try {
                            String value = json_custom_headers.get(key).toString();
                            headers.put(key, value);
                        } catch (JSONException e) {
                        }
                    }
                }

                if (data_json.has("data")) {
                    data_parameter = new HashMap<String, String>();
                    json_data = new JSONObject(data_json.getString("data"));
                    iter_json_data = json_data.keys();
                    while (iter_json_data.hasNext()) {
                        String key = iter_json_data.next();
                        try {
                            String value = json_data.get(key).toString();
                            data_parameter.put(key, value);
                        } catch (JSONException e) {
                        }
                    }
                }

                OkHttpClient client = null;
                Request request = null;
                if (Type.equals("GET")) {
                    Headers headerbuild = Headers.of(headers);

                    if (data_json.has("data")) {
                        String parameters_build = null;
                        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.N) {
                            parameters_build = data_parameter.entrySet().stream()
                                    .map(p -> p.getKey() + "=" + p.getValue())
                                    .reduce((p1, p2) -> p1 + "&" + p2)
                                    .orElse("");
                        }
                        Url = Url + "?" + parameters_build;
                    }
                    client = getUnsafeOkHttpClient();

                    request = new Request.Builder().cacheControl(CacheControl.FORCE_NETWORK)
                            .url(Url)
                            .headers(headerbuild)
                            .build();


                } else if (Type.equals("POST")) {

                }
                if (isNetworkAvailable(mContext)) {
                    assert client != null;
                    client.newCall(request).enqueue(new Callback() {
                        @Override
                        public void onFailure(@NotNull Call call, @NotNull IOException e) {
                            e.printStackTrace();
                            String Onfailer = null;
                            if (data_json.has("OnFailer")) {
                                try {
                                    Onfailer = data_json.getString("OnFailer");
                                } catch (JSONException o) {
                                    e.printStackTrace();
                                }
                                runJsCode(Onfailer + "( 404 , `" + Onfailer + "` , ``)");
                            }

                        }

                        @Override
                        public void onResponse(@NotNull Call call, @NotNull Response response) throws IOException {
                            if (!response.isSuccessful()) {
                                // Handle the error
                                throw new IOException("Unexpected code " + response);
                            }

                            try (ResponseBody responseBody = response.body()) {
                                // Read the response body as a string
//                                String responseString = responseBody.string();

                                if (data_json.has("OnSuccess")) {
                                    String OnSuccess = null;
                                    try {
                                        OnSuccess = data_json.getString("OnSuccess");
                                    } catch (JSONException e) {
                                        e.printStackTrace();
                                    }
                                    String finalResults = null;

                                    if (data_json.has("save_file_to_dir")) {
                                        try {
                                            boolean save_file_to_dir = data_json.getBoolean("save_file_to_dir");
                                            if (save_file_to_dir) {
                                                if (data_json.has("dir") && data_json.has("file_name")) {
                                                    String dirName = data_json.getString("dir");
                                                    String fileName = data_json.getString("file_name");
                                                    String Return = null;

                                                    try {
                                                        File dir = new File(getFilesDir(), dirName);
                                                        dir.mkdirs();
                                                        File file = new File(dir, fileName);
                                                        OutputStream os = new FileOutputStream(file);
                                                        int bytesRead = 0;
                                                        byte[] buffer = new byte[8192];
                                                        while ((bytesRead = response.body().byteStream().read(buffer, 0, 8192)) != -1) {
                                                            os.write(buffer, 0, bytesRead);
                                                        }
                                                        String file_path = fileName;
                                                        Return = "{\"status\":true,\"code\":200,\"msg\":\"" + fileName + " Saved Successfully\",\"file_path\":\"" + file_path + "\"}";
                                                    } catch (Exception e) {
                                                        e.printStackTrace();
                                                        Return = "{\"status\":false,\"code\":405,\"msg\":\"" + fileName + " Error\" }";
                                                    }

                                                    byte[] res = Return.getBytes();
                                                    finalResults = Arrays.toString(res);
                                                }
                                            } else {
                                                byte[] res = response.body().bytes();
                                                finalResults = Arrays.toString(res);
                                            }
                                        } catch (JSONException e) {
                                            e.printStackTrace();
                                        }
                                    } else {
                                        byte[] byteArrray = responseBody.bytes();
                                        finalResults = Arrays.toString(byteArrray);

                                    }
                                    Headers resHeaders = response.headers();
                                    StringBuilder HeadrsRes = new StringBuilder();
                                    Map<String, List<String>> map = resHeaders.toMultimap();
                                    for (Map.Entry<String, List<String>> entry : map.entrySet()) {
                                        HeadrsRes.append(entry.getKey()).append(":").append(entry.getValue().get(0)).append("%n");
                                    }
                                    byte[] headers_bytes = HeadrsRes.toString().getBytes();

                                    Custom_vars.put(OnSuccess, finalResults);
                                    finalResults = "Custom_vars";
                                    runJsCode(OnSuccess + "(`" + finalResults + "`, `" + OnSuccess + "`,`" + Arrays.toString(headers_bytes) + "`)");


                                }

                            } catch (Exception e) {
                                e.printStackTrace();
                            }


                        }
                    });
                } else {
                    if (data_json.has("OnFailer")) {
                        String Onfailer = null;
                        try {
                            Onfailer = data_json.getString("OnFailer");
                        } catch (JSONException e) {
                            e.printStackTrace();
                        }
                        runJsCode(Onfailer + "(409 , `" + Onfailer + "`,`Check Network Connection`)");
                    }
                }
            }
        }

        @JavascriptInterface
        public void exitApp() {
            finish();
        }

        @JavascriptInterface
        public boolean remove_file_from_dir(String dirName, String fileName) {
            File dir = new File(getFilesDir(), dirName);
            File file = new File(dir, fileName);
            if (file.exists()) {
                file.delete();
            }
            return true;
        }

        @JavascriptInterface
        public boolean change_home_page_status(boolean status) {
            return true;
        }

        @JavascriptInterface
        public void showToast(String toast) {
            MainActivity.this.runOnUiThread(new Runnable() {
                public void run() {
                    Toast.makeText(MainActivity.this, toast, Toast.LENGTH_SHORT).show();
                }
            });

        }

        @JavascriptInterface
        public String getUniqueDeviceID() {
            return MainActivity.this.DevId();
        }

        @JavascriptInterface
        public String getUserDeviceName() {
            return getDeviceName();
        }

        @JavascriptInterface
        public boolean validAauthToken(String Data) throws UnsupportedEncodingException {
            String app_token = getMouAauthToken();
            String mou_Aauth = get_MouPerfect(mou_Aauth0, false) + get_MouPerfect(mou_Aauth1, false) + get_MouPerfect(mou_Aauth2 + mou_Aauth3, false);
            mou_Aauth = mou_Aauth + get_MouPerfect("MpV3LWt3IFV3LVF=", false);
            return mou_Aauth.equals(app_token);
        }

        @JavascriptInterface
        public String MouPerfect(String text, boolean meth) throws UnsupportedEncodingException {
            return MainActivity.this.get_MouPerfect(text, meth);
        }

        @JavascriptInterface
        public boolean is_phone_notched() {
            return MainActivity.this.if_device_notched();
        }

        @JavascriptInterface
        public void play_vid(String url, String vid_name, String vid_user_agent, String string_CustomHeaders, String custom_ad_stats,
                             boolean continue_watch, String continue_watch_code, String Player_name,
                             Boolean isHlsChecked, String assets_js, String where_assets_js_file, String DrmScheme, String DrmLicenceUrl,
                             String whatWebPlayer
        ) {
            if (url != null && !url.equals("")) {
//                Intent Player_intent = new Intent(MainActivity.this, PlayerActivity.class);
                Intent Player_intent = new Intent(MainActivity.this, Player1Activity.class);
                Player_intent.setData(Uri.parse(url));
                Player_intent.putExtra("vid_url", url);
                Intent title = Player_intent.putExtra("title", vid_name);
                Player_intent.putExtra("isHlsChecked", isHlsChecked);


                if (!vid_user_agent.equals("")) {
                    Player_intent.putExtra("vid_user_agent", vid_user_agent);
                }

                Player_intent.putExtra("continue_watch", continue_watch);
                Player_intent.putExtra("Player_name", Player_name);
                Player_intent.putExtra("continue_watch_code", continue_watch_code);
                Player_intent.putExtra("assets_js", assets_js);
                Player_intent.putExtra("where_assets_js_file", where_assets_js_file);

                HashMap<String, String> CustomHeaders = new HashMap<>();
                try {
                    JSONObject vid_CustomHeaders_obj = new JSONObject(string_CustomHeaders);
                    Iterator<String> iter = vid_CustomHeaders_obj.keys();
                    while (iter.hasNext()) {
                        String key = iter.next();
                        try {
                            String value = vid_CustomHeaders_obj.get(key).toString();
                            CustomHeaders.put(key, value);
                        } catch (JSONException ignored) {
                        }
                    }
                } catch (Exception ignored) {
                }
                Player_intent.putExtra("CustomHeaders", CustomHeaders);

                Player_intent.putExtra("DrmScheme", DrmScheme);
                Player_intent.putExtra("DrmLicenceUrl", DrmLicenceUrl);
                Player_intent.putExtra("whatWebPlayer", whatWebPlayer);
                Player_intent.putExtra("custom_ad_stats", custom_ad_stats);
                Player_intent.putExtra("u_r_a", Custom_vars.get("u_r_a"));
                Player_intent.putExtra("e_m", Custom_vars.get("e_m"));
                Player_intent.putExtra("e_f_v_m", Custom_vars.get("e_f_v_m"));
                Player_intent.putExtra("e_v_m", Custom_vars.get("e_v_m"));
                Player_intent.putExtra("f_v_m", Custom_vars.get("f_v_m"));
                Player_intent.putExtra("AD_TAG_URI", Custom_vars.get("AD_TAG_URI"));

                startActivity(Player_intent);


            }


//            if (url != null && !url.equals("")) {
//
//                PackageManager pm = mContext.getPackageManager();
//                if (isPackageInstalled("com.mouscripts.elplayer", pm)) {
//                    Intent Player_intent = new Intent();
//                    Player_intent.setComponent(new ComponentName("com.mouscripts.elplayer", "com.mouscripts.elplayer.PlayerActivity"));
//                    Player_intent.setData(Uri.parse(url));
//                    Player_intent.putExtra("vid_url", url);
//                    Player_intent.putExtra("title", vid_name);
//                    if (!vid_user_agent.equals("")) {
//                        Player_intent.putExtra("vid_user_agent", vid_user_agent);
//                    }
//
//                    Player_intent.putExtra("continue_watch", continue_watch);
//
//                    HashMap<String, String> CustomHeaders = new HashMap<>();
//                    try {
//                        JSONObject vid_CustomHeaders_obj = new JSONObject(string_CustomHeaders);
//                        Iterator<String> iter = vid_CustomHeaders_obj.keys();
//                        while (iter.hasNext()) {
//                            String key = iter.next();
//                            try {
//                                String value = vid_CustomHeaders_obj.get(key).toString();
//                                CustomHeaders.put(key, value);
//                            } catch (JSONException ignored) {
//                            }
//                        }
//                    } catch (Exception ignored) {
//                    }
//                    Player_intent.putExtra("CustomHeaders", CustomHeaders);
//                    Player_intent.putExtra("custom_ad_stats", custom_ad_stats);
//                    startActivity(Player_intent);
//                } else {
//                    MainActivity.this.runOnUiThread(new Runnable() {
//                        public void run() {
//                            new AlertDialog.Builder(MainActivity.this)
//
//                                    .setMessage("To be able to watch, you must install the ELPlayer application on your device \n لتتمكن من المشاهدة، يجب عليك تثبيت تطبيق ELPlayer علي جهازك")
//                                    .setPositiveButton("تثبيت", (DialogInterface dialog, int which) -> {
//                                        startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse(elplayer_dl_link)));
//                                    })
//                                    .setNegativeButton("Cancel", (DialogInterface dialog, int which) -> {
//                                        dialog.dismiss();
//                                    })
//                                    .create()
//                                    .show();
//                        }
//                    });
//                }
//            }
        }

        @JavascriptInterface
        public String apk_version() {
            String version = null;
            try {
                PackageInfo pInfo = mContext.getPackageManager().getPackageInfo(mContext.getPackageName(), 0);
                version = pInfo.versionName;
            } catch (PackageManager.NameNotFoundException e) {
                e.printStackTrace();
            }
            return version;
        }

        @JavascriptInterface
        public String Write_file(String dirName, String fileName, String Text) {
            String Return = null;
            try {
                File dir = new File(getFilesDir(), dirName);
                dir.mkdirs();
                FileOutputStream fileOutputStream = new FileOutputStream(new File(dir, fileName));
                OutputStreamWriter outputWriter = new OutputStreamWriter(fileOutputStream);
                outputWriter.write(Text);
                outputWriter.close();

                Return = "File saved successfully!";
            } catch (Exception e) {
                e.printStackTrace();
                Return = "حدث خطأ اثناء الحفظ";

            }
            return Return;
        }

        @JavascriptInterface
        public String Read_file(String path) {
            final int READ_BLOCK_SIZE = 100;
            String Return = null;
            try {
                File file = new File(getApplicationContext().getFilesDir(), path);
                if (file.exists() && !file.isDirectory()) {
                    FileInputStream fileIn = openFileInput(path);
                    InputStreamReader InputRead = new InputStreamReader(fileIn);
                    char[] inputBuffer = new char[READ_BLOCK_SIZE];
                    String s = "";
                    int charRead;

                    while ((charRead = InputRead.read(inputBuffer)) > 0) {
                        // char to string conversion
                        String readstring = String.copyValueOf(inputBuffer, 0, charRead);
                        s += readstring;
                    }
                    InputRead.close();
                    Return = s;
                } else {
                    Return = "{false}";
                }

            } catch (Exception e) {
                e.printStackTrace();
                Return = "حدث خطأ اثناء القراءة";

            }
            return Return;
        }

        @JavascriptInterface
        public String Delete_file(String path) {
            String Return = null;

//            Return = String.valueOf(getFilesDir());
            File file = new File(getFilesDir(), path);
            if (file.exists()) {
                deleteFile(path);
            }
            return "File Deleted Succeffully";
        }

        @JavascriptInterface
        public boolean isFileExist(String dirName, String fileName) {
            File dir = new File(getFilesDir(), dirName);
            File file = new File(dir, fileName);
            return (file.exists() && !file.isDirectory());
        }

        @JavascriptInterface
        public int get_internal_file_size(String dirName, String fileName) {
            File dir = new File(getFilesDir(), dirName);
            File file = new File(dir, fileName);
            if (file.exists() && !file.isDirectory()) {
                return (int) file.length();
            } else {
                return 0;
            }
        }

        @JavascriptInterface
        public int get_file_size(String dirName, String fileName) {
            File file = new File(dirName, fileName);
            if (file.exists() && !file.isDirectory()) {
                return (int) file.length();
            } else {
                return 0;
            }
        }

        @JavascriptInterface
        public String get_files_path(String Where) {
            String returnn = "";
            if (Objects.equals(Where, "0")) {
                returnn = getFilesDir().getAbsolutePath();
            } else if (Objects.equals(Where, "1")) {
                returnn = new File(Environment.getExternalStorageDirectory() + File.separator).getAbsolutePath() + "/Elbatal";
            }
            Log.d(TAG, "get_files_path: " + returnn);
            return returnn + "/";
        }

        @JavascriptInterface
        public boolean is_network_available() {
            return isNetworkAvailable(mContext);
        }

        @JavascriptInterface
        public void login_with_google() {
            G_signIn();
        }

        @JavascriptInterface
        public void logout_with_google() {
            G_signOut();
        }

        @JavascriptInterface
        public void CanWebviewGoBack() {
            runOnUiThread(new Runnable() {
                @SuppressLint("JavascriptInterface")
                @Override
                public void run() {
                    runJsCode("UpdateCanWebviewGoBack(" + webView.canGoBack() + ")");
                }
            });
        }

        @JavascriptInterface
        public String get_index_link() {
            return getFilesDir().getPath() + "/project/index.html";
        }

        @JavascriptInterface
        public void save_file_to_dir(String Url, String dirName, String fileName, String OnSuccess, String OnProgress, String OnFailed) throws IOException {
            File dir = new File(getFilesDir(), dirName);
            dir.mkdirs();
            File file = new File(dir, fileName);
            OutputStream os = new FileOutputStream(file);
            OkHttpClient client = getUnsafeOkHttpClient();
            Request request = new Request.Builder().cacheControl(CacheControl.FORCE_NETWORK).url(Url).build();
            client.newCall(request).enqueue(new Callback() {
                public void onFailure(@NotNull Call call, @NotNull IOException e) {
                    runJsCode(OnFailed + "()");
                    e.printStackTrace();
                }

                @Override
                public void onResponse(@NotNull Call call, @NotNull Response response) throws IOException {
                    if (!response.isSuccessful()) {
                        // Handle error response
                        runJsCode(OnFailed + "()");
                        throw new IOException("Unexpected code " + response);
                    } else {
                        long target = response.body().contentLength();
                        int bytesRead = 0;
                        byte[] buffer = new byte[2048];
                        String latest_res;
                        try {
                            Integer All_Readed_Bytes = 0;
                            while ((bytesRead = response.body().byteStream().read(buffer)) != -1) {
                                All_Readed_Bytes += bytesRead;
                                os.write(buffer, 0, bytesRead);
                                long percent = (All_Readed_Bytes * 100L) / target;
                                latest_res = "{\"percent\":" + percent + '}';
                                runJsCode(OnProgress + "(`" + latest_res + "`)");
                            }
                        } finally {
                            latest_res = "{\"status\":true}";
                            runJsCode(OnSuccess + "(`" + latest_res + "`)");
                        }
                    }

                }
            });
        }

        @JavascriptInterface
        public String install_apk(String Path) {
//            BuildConfig.APPLICATION_ID +
            try {
                File file1 = new File(getFilesDir(), Path);
                Uri contentUri1 = FileProvider.getUriForFile(MainActivity.this,
                        "com.mouscripts.elbatal.provider", file1);
                Intent intent = new Intent(Intent.ACTION_VIEW, contentUri1);
                intent.setDataAndType(contentUri1, "application/vnd.android.package-archive");
                intent.putExtra(Intent.EXTRA_NOT_UNKNOWN_SOURCE, true);
                intent.setFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP | Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_GRANT_WRITE_URI_PERMISSION | Intent.FLAG_GRANT_READ_URI_PERMISSION);
                startActivity(intent);
            } catch (Exception e) {
                e.printStackTrace();
            }
            return "false";
        }

        @JavascriptInterface
        public boolean is_unknown_source_allowd() {
            boolean allow = false;
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                allow = MainActivity.this.getPackageManager().canRequestPackageInstalls();
            } else {
                try {
                    allow = Settings.Secure.getInt(getContentResolver(), Settings.Secure.INSTALL_NON_MARKET_APPS) == 1;
                } catch (Settings.SettingNotFoundException e) {
                    e.printStackTrace();
                }
            }
            return allow;
        }

        @JavascriptInterface
        public void request_unknown_source() {
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                Uri packageURI = Uri.parse("package:" + MainActivity.this.getPackageName());
                Intent intent = new Intent(Settings.ACTION_MANAGE_UNKNOWN_APP_SOURCES, packageURI);
                startActivity(intent);
            } else {
                startActivity(new Intent(Settings.ACTION_SECURITY_SETTINGS));
            }
        }

        @JavascriptInterface
        public void get_file_size_from_link(String Link, String Custom_headers, String func_name) throws JSONException {
            JSONObject json_custom_headers;
            Iterator<String> iter_headers = null;
            Map<String, String> headers = null;
            headers = new HashMap<String, String>();
            json_custom_headers = new JSONObject(Custom_headers);
            iter_headers = json_custom_headers.keys();
            while (iter_headers.hasNext()) {
                String key = iter_headers.next();
                try {
                    String value = json_custom_headers.get(key).toString();
                    headers.put(key, value);
                } catch (JSONException e) {
                }
            }
            Headers headerbuild = Headers.of(headers);

            OkHttpClient client = null;
            Request request = null;
            client = getUnsafeOkHttpClient();

            request = new Request.Builder().cacheControl(CacheControl.FORCE_NETWORK).url(Link).headers(headerbuild).build();
            client.newCall(request).enqueue(new Callback() {
                @Override
                public void onFailure(@NotNull Call call, @NotNull IOException e) {
                    e.printStackTrace();
                    runJsCode(func_name + "(`false`)");
                }

                @Override
                public void onResponse(@NotNull Call call, @NotNull Response response) throws IOException {
                    try {
                        String size = response.headers().get("Content-Length");
                        runJsCode(func_name + "(`" + size + "`)");
                    } catch (Exception e) {
                        e.printStackTrace();
                    }
                }
            });

        }

        @JavascriptInterface
        public void get_yacin_res(String Link, String func_name) throws JSONException {

            OkHttpClient client = null;
            Request request = null;
            client = getUnsafeOkHttpClient();

            request = new Request.Builder().cacheControl(CacheControl.FORCE_NETWORK).url(Link).build();
            client.newCall(request).enqueue(new Callback() {
                @Override
                public void onFailure(@NotNull Call call, @NotNull IOException e) {
                    e.printStackTrace();
                    runJsCode(func_name + "(`false`)");
                }

                @Override
                public void onResponse(@NotNull Call call, @NotNull Response response) throws IOException {
                    try {
                        String main_res = response.body().string();
                        Log.d(TAG, "onResponse: main_res => " + main_res);
                        String time = response.headers().get("t");
                        String yacin_key = "c!xZj+N9&G@Ev@vw" + time;
                        byte[] dec = null;
                        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                            dec = Base64.getDecoder().decode(main_res);
                        }
                        String decodedStr = new String(dec, StandardCharsets.US_ASCII);
                        StringBuilder result = new StringBuilder();
                        for (int i = 0; i < decodedStr.length(); i++) {
                            char append_text = (char) ((int) (decodedStr.charAt(i)) ^ (int) (yacin_key.charAt(i % yacin_key.length())));
                            result.append(append_text);
                        }
                        String resultt = String.valueOf(result);
                        byte[] res = resultt.getBytes();
                        String finalResults = Arrays.toString(res);
                        runJsCode(func_name + "(`" + finalResults + "`)");
                    } catch (Exception e) {
                        e.printStackTrace();
                    }
                }
            });


        }

        @JavascriptInterface
        public String decrypt_yacine(String main_res, String yacin_key) throws JSONException {
            byte[] dec = null;
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                dec = Base64.getDecoder().decode(main_res);
            }
            String decodedStr = new String(dec, StandardCharsets.US_ASCII);
            StringBuilder result = new StringBuilder();
            for (int i = 0; i < decodedStr.length(); i++) {
                char append_text = (char) ((int) (decodedStr.charAt(i)) ^ (int) (yacin_key.charAt(i % yacin_key.length())));
                result.append(append_text);
            }
            byte[] res = String.valueOf(result).getBytes();
            return Arrays.toString(res);
        }

        @JavascriptInterface
        public int get_downloading_file_size(String dirName, String fileName) {
            File dir = new File(getExternalFilesDir(""), dirName);
            File file = new File(dir, fileName);
            if (file.exists() && !file.isDirectory()) {
                return (int) file.length();
            } else {
                return 0;
            }
        }

        @JavascriptInterface
        public void subscribeNotificationsTopic(String Tobic) {
            runOnUiThread(new Runnable() {
                @Override
                public void run() {
                    // [START subscribe_topics]
                    FirebaseMessaging.getInstance().subscribeToTopic(Tobic)
                            .addOnCompleteListener(new OnCompleteListener<Void>() {
                                @Override
                                public void onComplete(@NonNull Task<Void> task) {
                                    String msg = "true";
                                    if (!task.isSuccessful()) {
                                        msg = "false";
                                    }
                                    runJsCode("subscriped_to_notification_topic(" + msg + ",\"" + Tobic + "\")");

//                        Toast.makeText(MainActivity.this, Tobic, Toast.LENGTH_SHORT).show();
                                }
                            });
                    askNotificationPermission();
                }
            });
            // [END subscribe_topics]
        }

        @JavascriptInterface
        public void UnsubscribeNotificationsTopic(String Tobic) {
            // [START subscribe_topics]
            FirebaseMessaging.getInstance().unsubscribeFromTopic(Tobic)
                    .addOnCompleteListener(new OnCompleteListener<Void>() {
                        @Override
                        public void onComplete(@NonNull Task<Void> task) {
                            String msg = "true";
                            if (!task.isSuccessful()) {
                                msg = "false";
                            }
                            runJsCode("unsubscriped_to_notification_topic(" + msg + ",\"" + Tobic + "\")");


//                        Toast.makeText(MainActivity.this, msg, Toast.LENGTH_SHORT).show();
                        }
                    });
            askNotificationPermission();
            // [END subscribe_topics]
        }

        @JavascriptInterface
        public boolean check_storage_Permission() {
            return checkStoragePermission();
        }

        @JavascriptInterface
        public void request_storage_permissoin(String call_back_function) {
            requestPermission();
        }

        public Bundle bundle_downloading = new Bundle();
        Map<String, Call> map_caller = new HashMap<String, Call>();
        public Call call = null;

        @JavascriptInterface
        public void download_file_now(String Url, String dirName, String fileName, String dl_id, boolean is_resume, String Custom_headers) throws Exception {

            JSONObject json_custom_headers;
            Iterator<String> iter_headers = null;
            Map<String, String> headers = null;
            headers = new HashMap<String, String>();
            json_custom_headers = new JSONObject(Custom_headers);
            iter_headers = json_custom_headers.keys();
            while (iter_headers.hasNext()) {
                String key = iter_headers.next();
                try {
                    String value = json_custom_headers.get(key).toString();
                    headers.put(key, value);
                } catch (JSONException e) {
                }
            }
            Headers headerbuild = Headers.of(headers);

            if (!isExternalStorageAvailable() || isExternalStorageReadOnly()) {
                Log.e(TAG, "download_file: ERROR PREMISION");
            } else {
                boolean is_exist = false;
                int downloaded_size = 0;
                File dir = new File(Environment.getExternalStorageDirectory(), "/Elbatal/" + dirName);
                File file = new File(dir, fileName);
                if (is_resume) {
                    is_exist = (file.exists() && !file.isDirectory());
                }
                if (is_exist) {
                    if (file.exists() && !file.isDirectory()) {
                        downloaded_size = (int) file.length();
                    }
                } else {
                    dir.mkdirs();
                }
                OutputStream os = null;
                OkHttpClient client = getUnsafeOkHttpClient();
                Request.Builder requestBuilder = new Request.Builder().cacheControl(CacheControl.FORCE_NETWORK);
                if (is_resume) {
                    requestBuilder.addHeader("Range", "bytes=" + downloaded_size + "-");
                    os = new FileOutputStream(file, true);
                } else {
                    os = new FileOutputStream(file, false);
                }
                Request request = null;
                request = requestBuilder.url(Url).headers(headerbuild).build();
                call = client.newCall(request);
                map_caller.put(dl_id, call);
                int finalDownloaded_size = downloaded_size;

                OutputStream finalOs = os;
                call.enqueue(new Callback() {
                    @Override
                    public void onFailure(@NotNull Call call, @NotNull IOException e) {
                        runJsCode("on_download_error(`" + dl_id + "`,`" + e.getMessage() + "`)");
                    }

                    @Override
                    public void onResponse(@NotNull Call call, @NotNull Response response) throws IOException {
                        long target = response.body().contentLength();
                        int bytesRead = 0;
                        int buffer_len = 1024 * 4;
                        byte[] buffer = new byte[buffer_len];
                        String latest_res;
                        try {
                            Integer All_Readed_Bytes = finalDownloaded_size;
                            if (is_resume) {
                                runJsCode("file_resuming(`" + dl_id + "`)");
                            }
                            while ((bytesRead = response.body().byteStream().read(buffer, 0, buffer_len)) != -1) {
                                All_Readed_Bytes = All_Readed_Bytes + bytesRead;
                                finalOs.write(buffer, 0, bytesRead);
                                bundle_downloading.putString(dl_id, String.valueOf(All_Readed_Bytes));
                            }
                        } catch (Exception e) {
                            runJsCode("on_download_error(`" + dl_id + "`,`" + e.getMessage() + "`)");
                            e.printStackTrace();
                        }
                    }
                });

            }
        }

        @JavascriptInterface
        public void pause_downloading(String dl_id) {
            try {
                Call this_call = map_caller.get(dl_id);
                this_call.cancel();
            } catch (Exception e) {
                runJsCode("on_download_error(`" + dl_id + "`,`1004`)");
            }
        }

        @JavascriptInterface
        public String get_downloading_len(String dl_id) {
            return bundle_downloading.getString(dl_id, "0");
        }


        @JavascriptInterface
        public void delete_dir(String destDir) throws IOException {
            File destdir = new File(getFilesDir(), destDir);
            if (destdir.isDirectory()) {
                FileUtils.deleteDirectory(destdir);
            }
        }

        @JavascriptInterface
        public void unzip(String zipFilePath, String destDir, String callback_func_name) throws IOException {
            File destdir = new File(getFilesDir(), destDir);
            File zip_file = new File(getFilesDir(), zipFilePath);
            String FolderName = destdir.getAbsolutePath();
            if (destdir.isDirectory()) {
                FileUtils.deleteDirectory(destdir);
            }
            final int BUFFER_SIZE = 4096;
            BufferedOutputStream bufferedOutputStream = null;
            FileInputStream fileInputStream;
            try {
                fileInputStream = new FileInputStream(zip_file.getAbsolutePath());
                ZipInputStream zipInputStream = new ZipInputStream(new BufferedInputStream(fileInputStream));
                ZipEntry zipEntry;

                while ((zipEntry = zipInputStream.getNextEntry()) != null) {

                    String zipEntryName = zipEntry.getName();

                    String name = destdir.getAbsolutePath().substring(destdir.getAbsolutePath().lastIndexOf("/") - 1);
                    File FileName = new File(FolderName);
                    if (!FileName.isDirectory()) {
                        try {
                            if (FileName.mkdir()) {
                            } else {
                            }
                        } catch (Exception e) {
                            e.printStackTrace();
                        }
                    }

                    File file = new File(FolderName + "/" + zipEntryName);

                    if (file.exists()) {

                    } else {
                        if (zipEntry.isDirectory()) {
                            file.mkdirs();
                        } else {
                            byte[] buffer = new byte[BUFFER_SIZE];
                            FileOutputStream fileOutputStream = new FileOutputStream(file);
                            bufferedOutputStream = new BufferedOutputStream(fileOutputStream, BUFFER_SIZE);
                            int count;

                            while ((count = zipInputStream.read(buffer, 0, BUFFER_SIZE)) != -1) {
                                bufferedOutputStream.write(buffer, 0, count);
                            }

                            bufferedOutputStream.flush();
                            bufferedOutputStream.close();
                        }
                    }
                }
                zipInputStream.close();
                runJsCode(callback_func_name + "(true)");
            } catch (FileNotFoundException e) {
                // TODO Auto-generated catch block
                e.printStackTrace();
            } catch (IOException e) {
                // TODO Auto-generated catch block
                e.printStackTrace();
            }
        }

        @JavascriptInterface
        public void exit_app() {
            MainActivity.this.finish();
            System.exit(0);
        }

        @JavascriptInterface
        public void focus_audio() {
            releaseAudioFocusForMyApp(mContext);
            boolean gotFocus = requestAudioFocusForMyApp(mContext);
            if (gotFocus) {
                //play audio.
            }
        }

        @JavascriptInterface
        public void Show_Unity_Interstitial() {
            UnityAds.show(MainActivity.this, Custom_vars.get("u_i_a"), new UnityAdsShowOptions(), showListener);
        }

        @JavascriptInterface
        public void initialize_Unity_Ads() {
            UnityAds.initialize(getApplicationContext(), unityGameID, testMode, MainActivity.this);
        }

        @JavascriptInterface
        public void load_Unity_Ads() {
            UnityAds.load(Custom_vars.get("u_i_a"), loadListener);
        }

        @JavascriptInterface
        public void change_custom_var(String key, String val) {
            Custom_vars.put(key, val);
        }

        @JavascriptInterface
        public String Get_From_Custom_vars(String key) {
            return Custom_vars.get(key);
        }

        @JavascriptInterface
        public void Remove_Custom_var(String key) {
            Custom_vars.remove(key);
        }

        @JavascriptInterface
        public String get_dest_folder() {
            String dir = getFilesDir().getAbsolutePath();
            return dir;
        }

        @JavascriptInterface
        public boolean is_device_rooted() {
            if (getPackageManager().hasSystemFeature(PackageManager.FEATURE_TELEVISION)
                    || getPackageManager().hasSystemFeature(PackageManager.FEATURE_LEANBACK)) {
                return false;
            } else {
                return isDeviceRooted();
            }
        }

        @JavascriptInterface
        public boolean is_package_installed(String package_name) {
            PackageManager pm = mContext.getPackageManager();
            return isPackageInstalled(package_name, pm);
        }

        @JavascriptInterface
        public String get_webview_data() {
            try {
                PackageInfo pi = WebViewCompat.getCurrentWebViewPackage(getApplicationContext());
                String version_name = pi.versionName;
                String packageName = pi.packageName;
                String version_code = String.valueOf(pi.versionCode);
                JSONObject json = new JSONObject();
                json.put("packageName", JSONObject.wrap(packageName));
                json.put("version_name", JSONObject.wrap(version_name));
                json.put("version_code", JSONObject.wrap(version_code));
                return String.valueOf(json);
            } catch (JSONException e) {
                throw new RuntimeException(e);
            }
        }

        @JavascriptInterface
        public boolean is_run_from_tv() {
            return (getPackageManager().hasSystemFeature(PackageManager.FEATURE_TELEVISION)
                    || getPackageManager().hasSystemFeature(PackageManager.FEATURE_LEANBACK));
        }

        @JavascriptInterface
        public void share_text_to_apps(String Title, String text) {
            Intent intent = new Intent(android.content.Intent.ACTION_SEND);
            intent.setType("text/plain");
            intent.putExtra(android.content.Intent.EXTRA_TEXT, text);
            intent.putExtra(android.content.Intent.EXTRA_SUBJECT, Title);
            startActivity(Intent.createChooser(intent, text));
        }


        @JavascriptInterface
        public String get_IntentExtra_string() {
            return IntentExtra_string;
        }

        @JavascriptInterface
        public String get_Share_data() {
            return Share_data;
        }

        @JavascriptInterface
        public String get_Share_or_notify() {
            return Share_or_notify;
        }

        @JavascriptInterface
        public void open_external_link(String link) {
            startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse(link)));
        }

        @JavascriptInterface
        public String getFcmToken() {
            return mContext.getSharedPreferences("FcmToken", MODE_PRIVATE).getString("FcmToken", "empty");
        }

        @JavascriptInterface
        public String get_Signature() throws PackageManager.NameNotFoundException {
            Signature[] sigs = mContext.getPackageManager().getPackageInfo(mContext.getPackageName(), PackageManager.GET_SIGNATURES).signatures;
            String Sig_hash = "false";
            for (Signature sig : sigs) {
                Sig_hash = String.valueOf(sig.hashCode());
            }
            return Sig_hash;
//            Signature releaseSig = mContext.getPackageManager().getPackageArchiveInfo("/mnt/sdcard/myReleaseApk.apk", PackageManager.GET_SIGNATURES).signatures[0];

//            try {
//                // get the signature form the package manager
//                PackageInfo packageInfo = mContext.getPackageManager().getPackageInfo(mContext.getPackageName(), PackageManager.GET_SIGNATURES);
//                Signature[] appSignatures = packageInfo.signatures;
//                // this sample only checks the first certificate
//                for (Signature signature : appSignatures) {
//                    byte[] signatureBytes = signature.toByteArray();
//                    // calc sha1 in hex
//                    String currentSignature = calcSHA1(signatureBytes);
//                    // compare signatures
//                    Log.d(TAG, "get_Signature: " + currentSignature);
//
//                    return currentSignature;
//
//                }
//            } catch (Exception e) { // if error assume failed to validate
//                return "false";
//            }
//
//            return "false";
//

        }

        @JavascriptInterface
        public boolean isproinstalled() {
            // the packagename of the 'key' app
            String proPackage = "com.mouscripts.elbatal";

            final PackageManager pm = mContext.getPackageManager();
            // get a list of installed packages
            List<PackageInfo> list =
                    pm.getInstalledPackages(PackageManager.GET_DISABLED_COMPONENTS);

            Iterator<PackageInfo> i = list.iterator();
            while (i.hasNext()) {
                PackageInfo p = i.next();

                if ((p.packageName.equals(proPackage)) &&
                        (pm.checkSignatures(mContext.getPackageName(), p.packageName) == PackageManager.SIGNATURE_MATCH))
                    return true;
            }
            return false;
        }

        @JavascriptInterface
        public void open_adm() {
            PackageManager pm = mContext.getPackageManager();
            if (isPackageInstalled("com.dv.adm", pm)) {
                Intent launchIntent = getPackageManager().getLaunchIntentForPackage("com.dv.adm");
                startActivity(launchIntent);
            } else {
                Intent intent = new Intent(Intent.ACTION_VIEW);
                String uriString = "market://details?id=com.dv.adm";
                intent.setData(Uri.parse(uriString));
                startActivity(intent);
            }
        }

        @JavascriptInterface
        public void open_1dm() {
            PackageManager pm = mContext.getPackageManager();
            if (isPackageInstalled("idm.internet.download.manager", pm)) {
                Intent launchIntent = getPackageManager().getLaunchIntentForPackage("idm.internet.download.manager");
                startActivity(launchIntent);
            } else {
                Intent intent = new Intent(Intent.ACTION_VIEW);
                String uriString = "market://details?id=idm.internet.download.manager";
                intent.setData(Uri.parse(uriString));
                startActivity(intent);
            }
        }

        @JavascriptInterface
        public void open_1dm_pluse() {
            PackageManager pm = mContext.getPackageManager();
            if (isPackageInstalled("idm.internet.download.manager.plus", pm)) {
                Intent launchIntent = getPackageManager().getLaunchIntentForPackage("idm.internet.download.manager.plus");
                startActivity(launchIntent);
            } else {
                Intent intent = new Intent(Intent.ACTION_VIEW);
                String uriString = "market://details?id=idm.internet.download.manager.plus";
                intent.setData(Uri.parse(uriString));
                startActivity(intent);
            }
        }

        @SuppressLint("SetJavaScriptEnabled")
        @JavascriptInterface
        public void load_url_in_helper_webview(String Url, String js_link_will_load, String where_file, String Useragent, String Referrer) {
            runOnUiThread(new Runnable() {
                @SuppressLint("JavascriptInterface")
                @Override
                public void run() {

                    if (helper_dialog != null) {
                        if (HelperWebView_loded) {
                            HelperWebView.loadUrl("about:blank");
                        }
                        helper_dialog.dismiss();
                    }
                    helper_dialog = new Dialog(MainActivity.this, R.style.DialogStyle);

                    helper_dialog.setContentView(R.layout.layout_custom_dialog);


//                    helper_dialog.getWindow().setBackgroundDrawableResource(R.drawable.bg_window);

//                    helper_dialog.findViewById(R.id.root_vg).setVisibility(View.GONE);


                    ImageView btnClose = helper_dialog.findViewById(R.id.btn_close);
                    btnClose.setOnClickListener(new View.OnClickListener() {
                        @Override
                        public void onClick(View view) {
                            Log.d(TAG, "onClick: Close Helper");
                            if (HelperWebView_loded) {
                                HelperWebView.loadUrl("about:blank");
                            }
                            helper_dialog.dismiss();
                        }
                    });

                    //        helper web view
//                    if(HelperWebView_loded == false) {
                    HelperWebView = helper_dialog.findViewById(R.id.HelperWebView);
                    WebSettings HelperWebViewSettings = HelperWebView.getSettings();
                    HelperWebView.setHapticFeedbackEnabled(false);
                    HelperWebView.setHorizontalScrollBarEnabled(false);
                    HelperWebView.setVerticalScrollBarEnabled(false);
                    HelperWebView.getSettings().setTextZoom(100);
                    HelperWebView.getSettings().setUserAgentString(Useragent);


                    Map<String, String> extraHeaders = new HashMap<String, String>();
                    extraHeaders.put("Referer", Referrer);
                    extraHeaders.put("User-Agent", Useragent);

                    HelperWebView.getSettings().setRenderPriority(WebSettings.RenderPriority.HIGH);
                    CookieManager cookieManager = CookieManager.getInstance();
                    cookieManager.setAcceptThirdPartyCookies(HelperWebView, true);
                    cookieManager.setAcceptCookie(true);

                    if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                        HelperWebView.setRendererPriorityPolicy(RENDERER_PRIORITY_BOUND, true);
                    }
                    final String databasePath = MainActivity.this.getApplicationContext().getDir("database", Context.MODE_PRIVATE).getPath();
                    HelperWebViewSettings.setDatabasePath(databasePath);
                    HelperWebViewSettings.setAllowFileAccessFromFileURLs(true);
                    HelperWebViewSettings.setSavePassword(false);
                    HelperWebViewSettings.setSaveFormData(false);
                    HelperWebViewSettings.setAllowUniversalAccessFromFileURLs(true);
                    HelperWebViewSettings.setAllowFileAccess(true);
                    HelperWebViewSettings.setJavaScriptEnabled(true);
                    HelperWebView.addJavascriptInterface(new MainActivity.WebAppInterface(MainActivity.this), "mouscripts");


                    HelperWebViewSettings.setMediaPlaybackRequiresUserGesture(false);
                    HelperWebViewSettings.setJavaScriptCanOpenWindowsAutomatically(true);
                    HelperWebViewSettings.setDomStorageEnabled(true);
                    HelperWebViewSettings.setDatabaseEnabled(true);
                    HelperWebViewSettings.setLoadsImagesAutomatically(true);
                    HelperWebViewSettings.setUseWideViewPort(true);
                    HelperWebViewSettings.setCacheMode(WebSettings.LOAD_NO_CACHE);
                    HelperWebViewSettings.setLoadWithOverviewMode(true);
                    HelperWebViewSettings.setSupportZoom(false);
                    HelperWebViewSettings.setSupportMultipleWindows(true);
                    HelperWebViewSettings.setSaveFormData(true);
                    HelperWebView.setOverScrollMode(View.OVER_SCROLL_NEVER);
                    HelperWebView.requestFocus();
                    HelperWebView_loded = true;
//                    }
                    HelperWebView.loadUrl(Url, extraHeaders);

                    HelperWebView.setWebViewClient(new WebViewClient() {
                        @Override
                        public boolean shouldOverrideUrlLoading(WebView view, String url) {
                            final Uri uri = Uri.parse(url);

//                            Log.d(TAG, "shouldOverrideUrlLoading: uri => " + uri);

                            show_loader_HelperWebView();
                            return false;
                        }

                        @Override
                        public WebResourceResponse shouldInterceptRequest(WebView view, String url) {

                            if (url.equals(Url) ||
                                    url.contains("recaptcha") ||
                                    url.contains("jquery") ||
                                    url.contains("verify")
                            ) {


                            } else {
//                                return new WebResourceResponse("text/javascript", "UTF-8", null);
                            }

//                            if (
//                                    url.contains("pagead") &&
//                                    url.contains("gtag") &&
//                                    url.contains("doubleclick") &&
//                                    url.contains("sodar2") &&
//                                    url.contains("ointmentapathetic.com")
//                            ) {
//                                return new WebResourceResponse("text/javascript", "UTF-8", null);
//                            }else {
//                                Log.d(TAG, "shouldInterceptRequest: url===> " +url );
//                            }
                            return null;
                        }

                        @Override
                        public void onPageFinished(WebView view, String url) {
                            if (!url.equals("about:blank")) {
                                HelperWebView.loadUrl("javascript:window.mouscripts.showHTML" +
                                        "('<html>'+document.getElementsByTagName('html')[0].innerHTML+'</html>');");
                                try {
                                    byte[] buffer = null;
                                    String encoded_js = "";
                                    if (Objects.equals(where_file, "1")) {
                                        URL jsUrl = new URL("http://192.168.1.16:8080/newbatal/" + js_link_will_load);
                                        HttpURLConnection conn = (HttpURLConnection) jsUrl.openConnection();
                                        conn.setConnectTimeout(5000);
                                        conn.setReadTimeout(5000);
                                        InputStream inputStream = conn.getInputStream();
                                        ByteArrayOutputStream baos = new ByteArrayOutputStream();
                                        byte[] chunk = new byte[4096];
                                        int bytesRead;
                                        while ((bytesRead = inputStream.read(chunk)) != -1) {
                                            baos.write(chunk, 0, bytesRead);
                                        }
                                        inputStream.close();
                                        buffer = baos.toByteArray();

                                        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                                            encoded_js = Base64.getEncoder().encodeToString(buffer);
                                        } else {
                                            encoded_js = android.util.Base64.encodeToString(buffer, android.util.Base64.DEFAULT);
                                        }
                                    } else if (Objects.equals(where_file, "2")) {
                                        File file = new File(getFilesDir(), "project/" + js_link_will_load);
                                        FileInputStream fIn = new FileInputStream(file);
                                        InputStreamReader isr = new InputStreamReader(fIn);
                                        BufferedReader buffreader = new BufferedReader(isr);
                                        String readString = buffreader.readLine();
                                        StringBuffer datax = new StringBuffer();
                                        while (readString != null) {
                                            datax.append(readString);
                                            readString = buffreader.readLine();
                                        }
                                        isr.close();

                                        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                                            encoded_js = Base64.getEncoder().encodeToString(datax.toString().getBytes());
                                        } else {
                                            encoded_js = android.util.Base64.encodeToString(datax.toString().getBytes(), android.util.Base64.DEFAULT);
                                        }
                                    }


                                    HelperWebView.loadUrl("javascript:(function() {" +
                                            "var parent = document.getElementsByTagName('head').item(0);" +
                                            "var script = document.createElement('script');" +
                                            "script.type = 'text/javascript';" +
                                            "script.innerHTML = window.atob('" + encoded_js + "');" +
                                            "parent.appendChild(script)" +
                                            "})()");
                                    Log.d(TAG, "onPageFinished: injected Successfully");


                                } catch (Exception e) {
                                    e.printStackTrace();
                                }
                            }

                        }

                        @SuppressLint("WebViewClientOnReceivedSslError")
                        @Override
                        public void onReceivedSslError(WebView view, SslErrorHandler handler, SslError error) {
                            handler.proceed();
                        }
                    });

//                    helper_dialog.show();

//       END helper web view


                }
            });
        }

        @JavascriptInterface
        public void download_with_adm(String url, String filename,String string_CustomHeaders) {

            PackageManager pm = mContext.getPackageManager();
            if (isPackageInstalled("com.dv.adm", pm)) {
                try {
                    Intent launchIntent = getPackageManager().getLaunchIntentForPackage("com.dv.adm");
                    launchIntent.setComponent(new ComponentName("com.dv.adm", "com.dv.adm.AEditor"));
                    launchIntent.putExtra("com.android.extra.filename", filename);
                    launchIntent.putExtra("android.intent.extra.TEXT", url);
                    try {
                        JSONObject vid_CustomHeaders_obj = new JSONObject(string_CustomHeaders);
                        if (vid_CustomHeaders_obj.length() > 0) {
                            Bundle extra = new Bundle();
                            Iterator<String> iter = vid_CustomHeaders_obj.keys();
                            while (iter.hasNext()) {
                                String key = iter.next();
                                extra.putString(key, vid_CustomHeaders_obj.get(key).toString());
                            }
                            launchIntent.putExtra("extra_headers", extra);
                        }
                    } catch (Exception ignored) {
                    }
                    startActivity(launchIntent);
                } catch (ActivityNotFoundException e) {
                    e.printStackTrace();
                }
            } else {
                Intent intent = new Intent(Intent.ACTION_VIEW);
                String uriString = "market://details?id=com.dv.adm";
                intent.setData(Uri.parse(uriString));
                startActivity(intent);
            }
        }

        @JavascriptInterface
        public void download_with_1dm(String url, String filename,String string_CustomHeaders) {
            PackageManager pm = mContext.getPackageManager();
            if (isPackageInstalled("idm.internet.download.manager", pm)) {
                Intent launchIntent = getPackageManager().getLaunchIntentForPackage("idm.internet.download.manager");
                launchIntent.setComponent(new ComponentName("idm.internet.download.manager", "idm.internet.download.manager.Downloader"));
                launchIntent.setData(Uri.parse(url));
                launchIntent.putExtra("com.android.extra.filename", filename);
                try {
                    JSONObject vid_CustomHeaders_obj = new JSONObject(string_CustomHeaders);
                    if (vid_CustomHeaders_obj.length() > 0) {
                        Bundle extra = new Bundle();
                        Iterator<String> iter = vid_CustomHeaders_obj.keys();
                        while (iter.hasNext()) {
                            String key = iter.next();
                            extra.putString(key, vid_CustomHeaders_obj.get(key).toString());
                        }
                        launchIntent.putExtra("extra_headers", extra);
                    }
                } catch (Exception ignored) {
                }


//                launchIntent.putExtra("android.intent.extra.TEXT", url);
                startActivity(launchIntent);
            } else {
                Intent intent = new Intent(Intent.ACTION_VIEW);
                String uriString = "market://details?id=idm.internet.download.manager";
                intent.setData(Uri.parse(uriString));
                startActivity(intent);
            }
        }

        @JavascriptInterface
        public void Youtube_Extractor(String YTLink) {
            Log.d(TAG, "Youtube_Extractor: " + YTLink);

        }

        @JavascriptInterface
        public void show_Loader_HelperWebView() {
            show_loader_HelperWebView();
        }

        @JavascriptInterface
        public void hide_Loader_HelperWebView() {
            MainActivity.this.runOnUiThread(new Runnable() {
                public void run() {
                    LinearLayout Loader_HelperWebView = helper_dialog.findViewById(R.id.Loader_HelperWebView);
                    Loader_HelperWebView.setVisibility(View.INVISIBLE);
                }
            });

        }

        @JavascriptInterface
        public void dismiss_HelperWebView() {
            if (helper_dialog != null) {
                helper_dialog.dismiss();
            }
        }

        @JavascriptInterface
        public void unload_helper_webview() {
            runOnUiThread(new Runnable() {
                @Override
                public void run() {
                    if (helper_dialog != null) {
                        if (HelperWebView_loded) {
                            HelperWebView.loadUrl("about:blank");
                            Log.d(TAG, "run: unloaded Done");
                        }
                    }
                }
            });
        }

        @JavascriptInterface
        public void Show_helper_dialog() {
            MainActivity.this.runOnUiThread(new Runnable() {
                public void run() {
                    if (helper_dialog != null) {
                        helper_dialog.show();
                    }
                }
            });
        }

        @JavascriptInterface
        public String get_app_version(String package_name) {
            PackageManager pm = getPackageManager();
            try {
                PackageInfo pi = pm.getPackageInfo(package_name, 0);
                String version_name = pi.versionName;
                String version_code = String.valueOf(pi.versionCode);
                JSONObject json = new JSONObject();
                json.put("version_name", JSONObject.wrap(version_name));
                json.put("version_code", JSONObject.wrap(version_code));
                return String.valueOf(json);
            } catch (PackageManager.NameNotFoundException e) {
                Log.e(TAG, "Android System WebView is not found");
            } catch (JSONException e) {
                throw new RuntimeException(e);
            }
            return "";
        }

        @JavascriptInterface
        public void getOstora(String Link) throws IOException {


            URL url = new URL(Link);
            Date date = new Date();
            long timestamp = date.getTime();
            String data = "date=2024-06-05";  // Data to post
            byte[] postData = data.getBytes(StandardCharsets.UTF_8);
            int postDataLength = postData.length;
            HttpURLConnection urlConnection = (HttpURLConnection) url.openConnection();
            urlConnection.setRequestMethod("POST");
            // Set custom headers
            urlConnection.setRequestProperty("Time", String.valueOf(timestamp));
            urlConnection.setRequestProperty("User-Agent", "Mozilla/5.0 (Android 14; Mobile; rv:124.0) Gecko/124.0 Firefox/124.0");
            urlConnection.setRequestProperty("Content-Type", "application/x-www-form-urlencoded");
            urlConnection.setRequestProperty("Content-Encoding", "gzip");
            urlConnection.setRequestProperty("Accept-Encoding", "gzip");
            urlConnection.setUseCaches(false);
            urlConnection.setInstanceFollowRedirects(true);
            urlConnection.setDoOutput(true);

            OutputStream os = urlConnection.getOutputStream();
            try (OutputStream outputStream = urlConnection.getOutputStream()) {
                outputStream.write(postData);
            }

            InputStream inputStream = urlConnection.getInputStream();

            GZIPOutputStream gZIPOutputStream = new GZIPOutputStream(os);
//
//            InputStream newinputStream = "gzip".equals(urlConnection.getHeaderField("Content-Encoding")) ? new GZIPInputStream(inputStream) : inputStream;


            int responseCode = urlConnection.getResponseCode();

            // Read the response
//            BufferedReader in = new BufferedReader(new InputStreamReader(newinputStream));
//            String inputLine;
//            StringBuilder response = new StringBuilder();
//
//            while ((inputLine = in.readLine()) != null) {
//                response.append(inputLine);
//            }
//            in.close();

            // Return the response
            Log.d(TAG, "getOstora: " + responseCode);


//            OkHttpClient client = null;
//            Request request = null;
//            client = getUnsafeOkHttpClient();
//
//            Date date = new Date();
//            long timestamp = date.getTime();
//
//            RequestBody formBody = new FormBody.Builder()
//                    .add("id", "711d7784f977da5a")
//                    .add("main", "1")
//                    .build();
//            request = new Request.Builder()
//                    .addHeader("time", String.valueOf(timestamp))
//                    .addHeader("User-Agent",
//                            "Mozilla/5.0 (Android 14; Mobile; rv:124.0) Gecko/124.0 Firefox/124.0")
//                    .addHeader("Content-Type",
//                            "application/json")
//                    .addHeader("Content-Encoding",
//                            "gzip")
//                    .addHeader("Accept-Encoding",
//                            "gzip")
//                    .url(Link)
//                    .post(formBody)
//                    .build();
//            client.newCall(request).enqueue(new Callback() {
//                @Override
//                public void onFailure(@NotNull Call call, @NotNull IOException e) {
//                    e.printStackTrace();
////                    runJsCode(func_name + "(`false`)");
//                }
//
//                @Override
//                public void onResponse(@NotNull Call call, @NotNull Response response) throws IOException {
//                    try {
//                        String main_res_bytes = response.body().string();
//                        Log.d(TAG, "onResponse: main_res => " +main_res);
//                        String Encoding = response.headers().get("Content-Encoding");
//
//
//
//                    } catch (Exception e) {
//                        e.printStackTrace();
//                    }
//                }
//            });

        }

        @JavascriptInterface
        public void change_elplayer_dl_link(String new_url) {
            elplayer_dl_link = new_url;
        }

        @JavascriptInterface
        public void download_with_1dm_pluse(String url, String filename,String string_CustomHeaders) {
            PackageManager pm = mContext.getPackageManager();
            if (isPackageInstalled("idm.internet.download.manager.plus", pm)) {
                Intent launchIntent = getPackageManager().getLaunchIntentForPackage("idm.internet.download.manager.plus");
                launchIntent.setComponent(new ComponentName("idm.internet.download.manager.plus", "idm.internet.download.manager.Downloader"));
                launchIntent.setData(Uri.parse(url));
                try {
                    JSONObject vid_CustomHeaders_obj = new JSONObject(string_CustomHeaders);
                    if (vid_CustomHeaders_obj.length() > 0) {
                        Bundle extra = new Bundle();
                        Iterator<String> iter = vid_CustomHeaders_obj.keys();
                        while (iter.hasNext()) {
                            String key = iter.next();
                            extra.putString(key, vid_CustomHeaders_obj.get(key).toString());
                        }
                        launchIntent.putExtra("extra_headers", extra);
                    }
                } catch (Exception ignored) {
                }
                launchIntent.putExtra("com.android.extra.filename", filename);
//                launchIntent.putExtra("android.intent.extra.TEXT", url);
                startActivity(launchIntent);
            } else {
                Intent intent = new Intent(Intent.ACTION_VIEW);
                String uriString = "market://details?id=idm.internet.download.manager.plus";
                intent.setData(Uri.parse(uriString));
                startActivity(intent);
            }
        }

        @JavascriptInterface
        public String get_data_from_clipboard() {
            ClipboardManager clipboard = (ClipboardManager) getSystemService(CLIPBOARD_SERVICE);
            return clipboard.getText().toString();
        }

        @JavascriptInterface
        public String mou_decrypt(String encoded) {
            return MouDecrypt(encoded);
        }

        @JavascriptInterface
        public String mou_encrypt(String txt) {
            return MouEncrypt(txt);
        }

        @JavascriptInterface
        public void active_app(String user_id) {

            Intent intent = new Intent();
            intent.setComponent(new ComponentName("com.mouscripts.bplayer", "com.mouscripts.bplayer.Player1Activity"));
// Optional: Add extras
            intent.putExtra("action", "Active_APP");
            intent.putExtra("u_id", user_id);

// Launch the activity
            activityResultLauncher.launch(intent);


        }

        private boolean isExternalStorageReadOnly() {
            String extStorageState = Environment.getExternalStorageState();
            return Environment.MEDIA_MOUNTED_READ_ONLY.equals(extStorageState);
        }

        private boolean isExternalStorageAvailable() {
            String extStorageState = Environment.getExternalStorageState();
            return Environment.MEDIA_MOUNTED.equals(extStorageState);
        }

        private boolean checkStoragePermission() {
            if (Build.VERSION.SDK_INT >= 23) {
                int result = ContextCompat.checkSelfPermission(MainActivity.this, android.Manifest.permission.WRITE_EXTERNAL_STORAGE);
                return result == PackageManager.PERMISSION_GRANTED;
            } else {
                return true;
            }
        }

        private void requestPermission() {
            ActivityCompat.requestPermissions(MainActivity.this, new String[]{android.Manifest.permission.WRITE_EXTERNAL_STORAGE}, 100);
//            if (ActivityCompat.shouldShowRequestPermissionRationale(MainActivity.this, android.Manifest.permission.WRITE_EXTERNAL_STORAGE)) {
//                Toast.makeText(MainActivity.this, "Write External Storage permission allows us to save files. Please allow this permission in App Settings.", Toast.LENGTH_LONG).show();
//            } else {
//                ActivityCompat.requestPermissions(MainActivity.this, new String[]{android.Manifest.permission.WRITE_EXTERNAL_STORAGE}, 100);
//            }
        }


    }


    @Override
    public void onRequestPermissionsResult(int requestCode, String[] permissions, int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        if (requestCode == 100) {
            if (grantResults.length > 0 && grantResults[0] == PackageManager.PERMISSION_GRANTED) {
                Log.e("value", "Permission Granted, Now you can use local drive .");
                runJsCode("return_request_storage_permissoin(true)");
            } else {
                Log.e("value", "Permission Denied, You cannot use local drive .");
                runJsCode("return_request_storage_permissoin(false)");
            }
        }


    }

    public static boolean isDeviceRooted() {
        return checkRootMethod1() || checkRootMethod2() || checkRootMethod3();
    }

    public static boolean checkRootMethod1() {
        String buildTags = android.os.Build.TAGS;
        return buildTags != null && buildTags.contains("test-keys");
    }

    public static boolean checkRootMethod2() {
        String[] paths = {"/system/app/Superuser.apk", "/sbin/su", "/system/bin/su", "/system/xbin/su", "/data/local/xbin/su", "/data/local/bin/su", "/system/sd/xbin/su",
                "/system/bin/failsafe/su", "/data/local/su", "/su/bin/su"};
        for (String path : paths) {
            if (new File(path).exists()) return true;
        }
        return false;
    }

    public static boolean checkRootMethod3() {
        Process process = null;
        try {
            process = Runtime.getRuntime().exec(new String[]{"/system/xbin/which", "su"});
            BufferedReader in = new BufferedReader(new InputStreamReader(process.getInputStream()));
            return in.readLine() != null;
        } catch (Throwable t) {
            return false;
        } finally {
            if (process != null) process.destroy();
        }
    }

    public boolean isPackageInstalled(String packageName, PackageManager packageManager) {
        try {
            packageManager.getPackageInfo(packageName, 0);
            return true;
        } catch (PackageManager.NameNotFoundException e) {
            return false;
        }
    }

    private OkHttpClient getUnsafeOkHttpClient() {
        try {
            final TrustManager[] trustAllCerts = new TrustManager[]{
                    new X509TrustManager() {

                        @Override
                        public void checkClientTrusted(java.security.cert.X509Certificate[] chain,
                                                       String authType) throws
                                CertificateException {
                        }

                        @Override
                        public void checkServerTrusted(java.security.cert.X509Certificate[] chain,
                                                       String authType) throws
                                CertificateException {
                        }

                        @Override
                        public java.security.cert.X509Certificate[] getAcceptedIssuers() {
                            return new java.security.cert.X509Certificate[]{};
                        }
                    }
            };

            final SSLContext sslContext = SSLContext.getInstance(SSL);
            sslContext.init(null, trustAllCerts, new java.security.SecureRandom());

            final SSLSocketFactory sslSocketFactory = sslContext.getSocketFactory();

            OkHttpClient.Builder builder = new OkHttpClient.Builder().
                    connectTimeout(30, TimeUnit.SECONDS)
                    .writeTimeout(30, TimeUnit.SECONDS)
                    .readTimeout(30, TimeUnit.SECONDS);
            builder.sslSocketFactory(sslSocketFactory, (X509TrustManager) trustAllCerts[0]);
            builder.hostnameVerifier(new HostnameVerifier() {
                @Override
                public boolean verify(String hostname, SSLSession session) {
                    return true;
                }
            });

            return builder.build();
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }
    private void askNotificationPermission() {
        // This is only necessary for API level >= 33 (TIRAMISU)
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            if (ContextCompat.checkSelfPermission(this, android.Manifest.permission.POST_NOTIFICATIONS) ==
                    PackageManager.PERMISSION_GRANTED) {
                // FCM SDK (and your app) can post notifications.
            } else if (shouldShowRequestPermissionRationale(android.Manifest.permission.POST_NOTIFICATIONS)) {
                // TODO: display an educational UI explaining to the user the features that will be enabled
                //       by them granting the POST_NOTIFICATION permission. This UI should provide the user
                //       "OK" and "No thanks" buttons. If the user selects "OK," directly request the permission.
                //       If the user selects "No thanks," allow the user to continue without notifications.
            } else {
                // Directly ask for the permission
                requestPermissionLauncher.launch(Manifest.permission.POST_NOTIFICATIONS);
            }
        }
    }

    private void logRegToken() {
        FirebaseMessaging.getInstance().getToken()
                .addOnCompleteListener(new OnCompleteListener<String>() {
                    @Override
                    public void onComplete(@NonNull Task<String> task) {
                        if (!task.isSuccessful()) {
                            Log.w(TAG, "Fetching FCM registration token failed", task.getException());
                            return;
                        }
                        String token = task.getResult();
                        String msg = "FCM Registration token: " + token;
                        Log.d(TAG, msg);
                    }
                });
    }

    public void setFullscreen(boolean enabled) {
        if (enabled) {
            getWindow().setFlags(WindowManager.LayoutParams.FLAG_FULLSCREEN,
                    WindowManager.LayoutParams.FLAG_FULLSCREEN);

            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.P) {
                getWindow().setFlags(WindowManager.LayoutParams.FLAG_LAYOUT_NO_LIMITS, WindowManager.LayoutParams.FLAG_LAYOUT_NO_LIMITS);
                getWindow().getAttributes().layoutInDisplayCutoutMode = WindowManager.LayoutParams.LAYOUT_IN_DISPLAY_CUTOUT_MODE_SHORT_EDGES;
            }
            getWindow().getDecorView().setSystemUiVisibility(View.SYSTEM_UI_FLAG_LOW_PROFILE
                    | View.SYSTEM_UI_FLAG_FULLSCREEN | View.SYSTEM_UI_FLAG_LAYOUT_STABLE
                    | View.SYSTEM_UI_FLAG_IMMERSIVE_STICKY | View.SYSTEM_UI_FLAG_LAYOUT_HIDE_NAVIGATION
                    | View.SYSTEM_UI_FLAG_HIDE_NAVIGATION);
        }
    }

    private final IUnityAdsLoadListener loadListener = new IUnityAdsLoadListener() {
        @Override
        public void onUnityAdsAdLoaded(String placementId) {
            runJsCode("unity_ads_loaded();");
        }

        @Override
        public void onUnityAdsFailedToLoad(String placementId, UnityAds.UnityAdsLoadError error, String message) {
            Log.e("UnityAdsExample", "Unity Ads failed to load ad for " + placementId + " with error: [" + error + "] " + message);
            runJsCode("unity_Interstitial_end(false,'" + error + "')");
        }
    };

    @Override
    public void onInitializationComplete() {
        UnityAds.load(Custom_vars.get("u_i_a"), loadListener);
    }

    @Override
    public void onInitializationFailed(UnityAds.UnityAdsInitializationError error, String message) {
        Log.e("UnityAdsExample", "Unity Ads initialization failed with error: [" + error + "] " + message);
    }

    private final IUnityAdsShowListener showListener = new IUnityAdsShowListener() {
        @Override
        public void onUnityAdsShowFailure(String placementId, UnityAds.UnityAdsShowError error, String message) {
            Log.e("UnityAdsExample", "Unity Ads failed to show ad for " + placementId + " with error: [" + error + "] " + message);
            runJsCode("unity_Interstitial_end(false,'" + error + "')");
//            runJsCode("unity_Interstitial_end(false,'"+ error +"')");

        }

        @Override
        public void onUnityAdsShowStart(String placementId) {
            Log.v("UnityAdsExample", "onUnityAdsShowStart: " + placementId);
        }

        @Override
        public void onUnityAdsShowClick(String placementId) {
            Log.v("UnityAdsExample", "onUnityAdsShowClick: " + placementId);
        }

        @Override
        public void onUnityAdsShowComplete(String placementId, UnityAds.UnityAdsShowCompletionState state) {
            Log.v("UnityAdsExample", "onUnityAdsShowComplete: " + placementId);
            if ("COMPLETED".compareTo(String.valueOf(state)) == 0) {
                runJsCode("unity_Interstitial_end(true,'Completed')");
            } else {
                runJsCode("unity_Interstitial_end(true,'Skiped')");
            }
            UnityAds.load(Custom_vars.get("u_i_a"), loadListener);
        }
    };

    @Override
    protected void onNewIntent(Intent intent) {
        super.onNewIntent(intent);
        setIntent(intent);
        if (intent.hasExtra("open_link")) {
            String open_link = intent.getStringExtra("open_link");
        }

        if (getIntent().getExtras() != null) {
            JSONObject json = new JSONObject();

            for (String key : getIntent().getExtras().keySet()) {
                Object value = getIntent().getExtras().get(key);
                try {
                    json.put(key, JSONObject.wrap(value));
                } catch (JSONException e) {
                    throw new RuntimeException(e);
                }
            }
            byte[] res = String.valueOf(json).getBytes();
            IntentExtra_string = Arrays.toString(res);
            Share_or_notify = "2";
//            Log.d(TAG, "onNewIntent: json => " + IntentExtra_string);


        }


        Uri uri = getIntent().getData();
        if (uri != null) {
            String args = uri.getQuery();
            if (args != null) {
                Log.d(TAG, "onNewIntent: args => " + args);
                Share_data = args;
                Share_or_notify = "1";
                webView.loadUrl("http://192.168.1.16:8080/newbatal/index.html");
            }
//            else {
//                webView.loadUrl("http://192.168.1.16:8080/newbatal/index.html?opend_link=" + uri);
//            }
        } else {
            webView.loadUrl("http://192.168.1.16:8080/newbatal/index.html");
        }
    }

    private final ActivityResultLauncher<String> requestPermissionLauncher =
            registerForActivityResult(new ActivityResultContracts.RequestPermission(), isGranted -> {
                if (isGranted) {
                    Toast.makeText(this, "Notifications permission granted", Toast.LENGTH_SHORT)
                            .show();
                } else {
                    Toast.makeText(this, "can't post notifications without POST_NOTIFICATIONS permission",
                            Toast.LENGTH_LONG).show();
                }
            });

    public boolean requestAudioFocusForMyApp(final Context context) {
        AudioManager am = (AudioManager) context.getSystemService(Context.AUDIO_SERVICE);

        // Request audio focus for playback
        int result = am.requestAudioFocus(null,
                // Use the music stream.
                AudioManager.STREAM_MUSIC,
                // Request permanent focus.
                AudioManager.AUDIOFOCUS_GAIN);

        if (result == AudioManager.AUDIOFOCUS_REQUEST_GRANTED) {
            Log.d("AudioFocus", "Audio focus received");
            return true;
        } else {
            Log.d("AudioFocus", "Audio focus NOT received");
            return false;
        }
    }

    public String getDeviceName() {
        String manufacturer = Build.MANUFACTURER;
        String model = Build.MODEL;
        if (model.startsWith(manufacturer)) {
            return capitalize(model);
        } else {
            return capitalize(manufacturer) + " " + model;
        }
    }

    private String capitalize(String s) {
        if (s == null || s.length() == 0) {
            return "";
        }
        char first = s.charAt(0);
        if (Character.isUpperCase(first)) {
            return s;
        } else {
            return Character.toUpperCase(first) + s.substring(1);
        }
    }

    public void releaseAudioFocusForMyApp(final Context context) {
        AudioManager am = (AudioManager) context.getSystemService(Context.AUDIO_SERVICE);
        am.abandonAudioFocus(null);
    }

    public void ShowMessage(String msg) {
        Toast.makeText(this, msg, Toast.LENGTH_SHORT).show();
    }


    @SuppressLint("HardwareIds")
    public String DevId() {

        String dev_id = Settings.Secure.getString(MainActivity.this.getContentResolver(),
                Settings.Secure.ANDROID_ID);

        int interval = 4;
        char separator = '-';
        StringBuilder sb = new StringBuilder(dev_id);

        for (int i = 0; i < dev_id.length() / interval; i++) {
            sb.insert(((i + 1) * interval) + i, separator);
        }
        String dev_id_withDashes = sb.toString();

        if (String.valueOf(dev_id_withDashes.charAt(dev_id_withDashes.length() - 1)).equals("-")) {
            dev_id_withDashes = dev_id_withDashes.substring(0, dev_id_withDashes.length() - 1);
        }


        return dev_id_withDashes;
//        String uniquePseudoID = "35" +
//                Build.BOARD.length() % 10 +
//                Build.BRAND.length() % 10 +
//                Build.DEVICE.length() % 10 +
//                Build.DISPLAY.length() % 10 +
//                Build.HOST.length() % 10 +
//                Build.ID.length() % 10 +
//                Build.MANUFACTURER.length() % 10 +
//                Build.MODEL.length() % 10 +
//                Build.PRODUCT.length() % 10 +
//                Build.TAGS.length() % 10 +
//                Build.TYPE.length() % 10 +
//                Build.USER.length() % 10;
//        String serial = Build.getRadioVersion();
//
//            return new UUID(uniquePseudoID.hashCode(), serial.hashCode()).toString();

    }

    public static String strtr(String str, String from, String to) {
        char[] out = null;
        for (int i = 0, len = str.length(); i < len; i++) {
            char c = str.charAt(i);
            int p = from.indexOf(c);
            if (p >= 0) {
                if (out == null) out = str.toCharArray();
                out[i] = to.charAt(p);
            }
        }
        return out != null ? new String(out) : str;
    }

    public String get_MouPerfect(String text, boolean meth) throws UnsupportedEncodingException {
        String defaultt = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
        String custom = "ZYXWVUTSRQPONMLKJIHGFEDCBAzyxwvutsrqponmlkjihgfedcba9876543210+/";
        if (meth) {
//            String encoded = URLEncoder.encode(text, "UTF-8");
            String encoded = text;
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                encoded = Base64.getEncoder().encodeToString(encoded.getBytes());
            } else {
                encoded = android.util.Base64.encodeToString(encoded.getBytes(), android.util.Base64.DEFAULT);
            }
            encoded = strtr(encoded, defaultt, custom);
            return encoded;
        } else {
            String decoded = strtr(text, defaultt, custom);
            byte[] decodedBytes = new byte[0];
            if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.O) {
                decodedBytes = Base64.getDecoder().decode(decoded);
            } else {
                decodedBytes = android.util.Base64.decode(decoded, android.util.Base64.DEFAULT);
            }
            decoded = new String(decodedBytes);
//            Log.d(TAG, "get_MouPerfect: decoded => " + decoded);
            decoded = URLDecoder.decode(decoded, "UTF-8");
            return decoded;
        }
    }

    public boolean if_device_notched() {
        int statusBarHeight = 0;
        @SuppressLint("InternalInsetResource") int resourceId = getResources().getIdentifier("status_bar_height", "dimen", "android");
        if (resourceId > 0) {
            statusBarHeight = getResources().getDimensionPixelSize(resourceId);
        }
        return statusBarHeight > convertDpToPixel(24);
    }

    public int convertDpToPixel(float dp) {
        DisplayMetrics metrics = Resources.getSystem().getDisplayMetrics();
        float px = dp * (metrics.densityDpi / 160f);
        return Math.round(px);
    }

    public void runJsCode(String Js_code) {
        webView.post(() -> webView.loadUrl("javascript:" + Js_code));
    }

    // Define the callback interface
    interface JsCallback {
        WebResourceResponse onComplete();
    }

    // Example implementation of runJsCode
    private void runJsCodeWithCallback(String jsCode, JsCallback callback) {
        // Assuming you have a WebView instance named webView
        MainActivity.this.runOnUiThread(new Runnable() {
            public void run() {
                webView.evaluateJavascript(jsCode, new ValueCallback<String>() {
                    @Override
                    public void onReceiveValue(String value) {
                        // JS execution completed
                        if (callback != null) {
                            callback.onComplete();
                        }
                    }
                });
            }
        });
    }

    public static boolean isNetworkAvailable(final Context context) {
        final ConnectivityManager cm = (ConnectivityManager)
                context.getSystemService(Context.CONNECTIVITY_SERVICE);
        if (cm == null) return false;
        final NetworkInfo networkInfo = cm.getActiveNetworkInfo();
        return (networkInfo != null && networkInfo.isConnected());
    }

    private static void disableSSLCertificateChecking() {
        TrustManager[] trustAllCerts = new TrustManager[]{new X509TrustManager() {
            public X509Certificate[] getAcceptedIssuers() {
                return null;
            }

            @Override
            public void checkClientTrusted(X509Certificate[] arg0, String arg1) throws CertificateException {
                // Not implemented
            }

            @Override
            public void checkServerTrusted(X509Certificate[] arg0, String arg1) throws CertificateException {
                // Not implemented
            }
        }};

        try {
            SSLContext sc = SSLContext.getInstance("TLS");

            sc.init(null, trustAllCerts, new java.security.SecureRandom());

            HttpsURLConnection.setDefaultSSLSocketFactory(sc.getSocketFactory());
        } catch (KeyManagementException e) {
            e.printStackTrace();
        } catch (NoSuchAlgorithmException e) {
            e.printStackTrace();
        }
    }

    private void show_loader_HelperWebView() {
        LinearLayout Loader_HelperWebView = helper_dialog.findViewById(R.id.Loader_HelperWebView);
        Loader_HelperWebView.setVisibility(View.VISIBLE);
    }

    private void hash() {
        PackageInfo info;
        try {
            info = getPackageManager().getPackageInfo(
                    this.getPackageName(), PackageManager.GET_SIGNATURES);

            for (android.content.pm.Signature signature : info.signatures) {
                MessageDigest md;
                md = MessageDigest.getInstance("SHA");
                md.update(signature.toByteArray());
                Log.e("sagar sha key", md.toString());
                String something = new String(android.util.Base64.encode(md.digest(), 0));
                Log.e("sagar Hash key", something);
                System.out.println("Hash key" + something);
            }

        } catch (PackageManager.NameNotFoundException e1) {
            Log.e("name not found", e1.toString());
        } catch (NoSuchAlgorithmException e) {
            Log.e("no such an algorithm", e.toString());
        } catch (Exception e) {
            Log.e("exception", e.toString());
        }
    }

    private String getMouAauthToken() {
        PackageManager pm = this.getPackageManager();
        String packageName = this.getPackageName();
        int flags = PackageManager.GET_SIGNATURES;
        PackageInfo packageInfo = null;
        try {
            packageInfo = pm.getPackageInfo(packageName, flags);
        } catch (PackageManager.NameNotFoundException e) {
            e.printStackTrace();
        }
        Signature[] signatures = packageInfo.signatures;
        byte[] cert = signatures[0].toByteArray();
        InputStream input = new ByteArrayInputStream(cert);
        CertificateFactory cf = null;
        try {
            cf = CertificateFactory.getInstance("X509");
        } catch (CertificateException e) {
            e.printStackTrace();
        }
        X509Certificate c = null;
        try {
            c = (X509Certificate) cf.generateCertificate(input);
        } catch (CertificateException e) {
            e.printStackTrace();
        }
        String hexString = "";
        try {
            MessageDigest md = MessageDigest.getInstance("SHA1");
            byte[] publicKey = md.digest(c.getEncoded());
            hexString = byte2HexFormatted(publicKey);
        } catch (NoSuchAlgorithmException e1) {
            e1.printStackTrace();
        } catch (CertificateEncodingException e) {
            e.printStackTrace();
        }
        return hexString;
    }

    public static String byte2HexFormatted(byte[] arr) {
        StringBuilder str = new StringBuilder(arr.length * 2);
        for (int i = 0; i < arr.length; i++) {
            String h = Integer.toHexString(arr[i]);
            int l = h.length();
            if (l == 1) h = "0" + h;
            if (l > 2) h = h.substring(l - 2, l);
            str.append(h.toUpperCase());
            if (i < (arr.length - 1)) str.append(':');
        }
        return str.toString();
    }

    public static String MouDecrypt(String enc) {
        int numberLength = 10;
        int originalStringLength = enc.length() - numberLength;
        int middleIndex = originalStringLength / 2;
        String extractedNumber = enc.substring(middleIndex, middleIndex + numberLength);
        String key = Mou_Key + extractedNumber;
        String firstHalf = enc.substring(0, middleIndex);
        String secondHalf = enc.substring(middleIndex + numberLength);
        enc = firstHalf + secondHalf;
        byte[] base64DecodedBytes = android.util.Base64.decode(enc, android.util.Base64.DEFAULT);
        byte[] resultBytes = new byte[base64DecodedBytes.length];
        for (int i = 0; i < base64DecodedBytes.length; i++) {
            resultBytes[i] = (byte) (base64DecodedBytes[i] ^ key.charAt(i % key.length()));
        }
        return new String(resultBytes, StandardCharsets.UTF_8);
    }

    public static String MouEncrypt(String plain) {
        String nowUtcString = "5658574935";
        if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.O) {
            LocalDateTime nowUtc = LocalDateTime.now(ZoneOffset.UTC);
            nowUtcString = String.valueOf(nowUtc.toEpochSecond(ZoneOffset.UTC));
        }
        String key = Mou_Key + nowUtcString;
        StringBuilder result = new StringBuilder();
        int i = 0;
        for (char letter : plain.toCharArray()) {
            result.append((char) (letter ^ key.charAt(i % key.length())));
            i++;
        }
        String encodedResult = android.util.Base64.encodeToString(result.toString().getBytes(), android.util.Base64.DEFAULT);
        int middleIndex = encodedResult.length() / 2;
        String firstHalf = encodedResult.substring(0, middleIndex);
        String secondHalf = encodedResult.substring(middleIndex);
        String finalResult = firstHalf + nowUtcString + secondHalf;
        return finalResult;
    }

    public static Map<String, String> getQueryParams(String url) {
        Map<String, String> params = new HashMap<>();

        try {
            URI uri = new URI(url);
            String query = uri.getQuery();

            if (query != null) {
                String[] pairs = query.split("&");

                for (String pair : pairs) {
                    String[] keyValue = pair.split("=");
                    String key = URIdecode(keyValue[0]);
                    String value = keyValue.length > 1 ? URIdecode(keyValue[1]) : "";
                    params.put(key, value);
                }
            }
        } catch (Exception e) {
            e.printStackTrace();
        }

        return params;
    }

    public static String URIdecode(String input) {
        StringBuilder decoded = new StringBuilder();
        try {
            for (int i = 0; i < input.length(); i++) {
                char ch = input.charAt(i);
                if (ch == '%') {
                    // Handle percent-encoded characters
                    if (i + 2 < input.length()) {
                        String hex = input.substring(i + 1, i + 3);
                        decoded.append((char) Integer.parseInt(hex, 16));
                        i += 2; // Move past the two hex characters
                    }
                } else if (ch == '+') {
                    // Convert '+' to space
                    decoded.append(' ');
                } else {
                    // Append regular characters
                    decoded.append(ch);
                }
            }
        } catch (Exception e) {
            e.printStackTrace();
            // In case of an error, return the input as is
            return input;
        }

        return decoded.toString();
    }
    // Prevent activity recreation on orientation change


    private class MyWebChromeClient extends WebChromeClient {
        @Override
        public boolean onJsAlert(WebView view, String url, String message, JsResult result) {
            new AlertDialog.Builder(view.getContext())
                    .setTitle("")
                    .setMessage(message)
                    .setPositiveButton("OK", (DialogInterface dialog, int which) -> result.confirm())
                    .setOnDismissListener((DialogInterface dialog) -> result.confirm())
                    .create()
                    .show();
            return true;
        }

        @Override
        public boolean onJsConfirm(WebView view, String url, String message, JsResult result) {
            new AlertDialog.Builder(view.getContext())
                    .setTitle("")
                    .setMessage(message)
                    .setPositiveButton("OK", (DialogInterface dialog, int which) -> result.confirm())
                    .setNegativeButton("Cancel", (DialogInterface dialog, int which) -> result.cancel())
                    .setOnDismissListener((DialogInterface dialog) -> result.cancel())
                    .create()
                    .show();
            return true;
        }

        @Override
        public boolean onJsPrompt(WebView view, String url, String message, String defaultValue, JsPromptResult result) {
            final EditText input = new EditText(view.getContext());
            input.setInputType(InputType.TYPE_CLASS_TEXT);
            input.setText(defaultValue);
            new AlertDialog.Builder(view.getContext())
                    .setTitle("")
                    .setMessage(message)
                    .setView(input)
                    .setPositiveButton("OK", (DialogInterface dialog, int which) -> result.confirm(input.getText().toString()))
                    .setNegativeButton("Cancel", (DialogInterface dialog, int which) -> result.cancel())
                    .setOnDismissListener((DialogInterface dialog) -> result.cancel())
                    .create()
                    .show();
            return true;
        }

        @Override
        public void onProgressChanged(WebView view, int newProgress) {
            super.onProgressChanged(view, newProgress);
        }

        @Override
        public void onExceededDatabaseQuota(String url,
                                            String databaseIdentifier, long currentQuota,
                                            long estimatedSize, long totalUsedQuota,
                                            WebStorage.QuotaUpdater quotaUpdater) {
            quotaUpdater.updateQuota(5 * 1024 * 1024);
        }

        @Override
        public void onShowCustomView(View view, CustomViewCallback callback) {
            if (customView != null) {
                callback.onCustomViewHidden();
                return;
            }

            customView = view;
            fullScreenContainer.setVisibility(View.VISIBLE);
            fullScreenContainer.addView(view);
            customViewCallback = callback;

            // Hide system UI
            getWindow().getDecorView().setSystemUiVisibility(
                    View.SYSTEM_UI_FLAG_FULLSCREEN |
                            View.SYSTEM_UI_FLAG_HIDE_NAVIGATION |
                            View.SYSTEM_UI_FLAG_IMMERSIVE_STICKY);
            setRequestedOrientation(ActivityInfo.SCREEN_ORIENTATION_LANDSCAPE);
        }

        @Override
        public void onHideCustomView() {
            if (customView == null) return;

            fullScreenContainer.setVisibility(View.GONE);
            fullScreenContainer.removeView(customView);
            customView = null;
            customViewCallback.onCustomViewHidden();

            // Restore UI
            getWindow().getDecorView().setSystemUiVisibility(View.SYSTEM_UI_FLAG_VISIBLE);
            setRequestedOrientation(ActivityInfo.SCREEN_ORIENTATION_UNSPECIFIED);
        }
    }
}