package com.mouscripts.elbatal;

import static android.webkit.WebView.RENDERER_PRIORITY_BOUND;

import static org.apache.http.conn.ssl.SSLSocketFactory.SSL;

import android.annotation.SuppressLint;
import android.content.ComponentName;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.graphics.Bitmap;
import android.net.Uri;
import android.net.http.SslError;
import android.os.Build;
import android.os.Bundle;

import android.text.InputType;
import android.util.Log;
import android.view.View;
import android.view.WindowManager;
import android.webkit.CookieManager;
import android.webkit.JavascriptInterface;
import android.webkit.JsPromptResult;
import android.webkit.JsResult;
import android.webkit.SslErrorHandler;
import android.webkit.WebChromeClient;
import android.webkit.WebResourceError;
import android.webkit.WebResourceRequest;
import android.webkit.WebResourceResponse;
import android.webkit.WebSettings;
import android.webkit.WebStorage;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.Button;
import android.widget.EditText;
import android.widget.RelativeLayout;
import android.widget.TextView;
import android.widget.Toast;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.content.FileProvider;


import org.json.JSONException;
import org.json.JSONObject;

import java.io.InputStream;
import java.security.cert.CertificateException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.Map;
import java.util.Objects;
import java.util.concurrent.TimeUnit;

import javax.net.ssl.HostnameVerifier;
import javax.net.ssl.SSLContext;
import javax.net.ssl.SSLSession;
import javax.net.ssl.SSLSocketFactory;
import javax.net.ssl.TrustManager;
import javax.net.ssl.X509TrustManager;

import okhttp3.OkHttpClient;
import okhttp3.Request;
import okhttp3.Response;


public class Player1Activity extends AppCompatActivity {
    String TAG = "Player1Activity";
    //    private GestureDetector _tapDetector;
    private String AD_TAG_URI = ""; // Replace with your ad URL

    private TextView loading_ad_text;
    private RelativeLayout alertDialog;
    private Button Back_button;
    private Button Retry_button;
    private String vid_title;
    private Uri vid_url;
    private String custom_ad_stats;
    private String u_r_a;
    private String e_m;
    private String e_f_v_m;
    private String e_v_m;
    private String f_v_m;
    private String vid_user_agent;
    private HashMap<String, String> vid_CustomHeaders;
    private boolean continue_watch = false;
    private String continue_watch_code = "";
    private String Player_name = "Elbatal";
    private Integer player_num = 1;
    private String assets_js = "";
    private String where_assets_js_file = "";
    private boolean isHlsChecked = false;
    private String DrmLicenceUrl = "";
    private String DrmScheme = "";
    private String whatWebPlayer = "";
    private WebView webView;
    private ActivityResultLauncher<Intent> activityResultLauncher;
    private Intent pendingPlayerIntent;

    //    private String DrmClearKeyJson = "";
    @SuppressLint({"MissingInflatedId", "SetJavaScriptEnabled"})
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        hideSystemUI();
        setContentView(R.layout.activity1_player);

        // ✅ Register once here
        activityResultLauncher = registerForActivityResult(
                new ActivityResultContracts.StartActivityForResult(),
                result -> {
                    if (result.getResultCode() == RESULT_OK) {
                        Intent data = result.getData();
                        if (data != null && data.hasExtra("RunLocalWebPlayer")) {
                            // Modify and launch WebPlayer
                            pendingPlayerIntent.setPackage("com.mouscripts.elbatal");
                            pendingPlayerIntent.setClassName("com.mouscripts.elbatal", "com.mouscripts.elbatal.WebPlayer");
                            startActivity(pendingPlayerIntent);
                            finish();
                        }
                    }
                }
        );

        Intent nowintent = getIntent();
        vid_title = nowintent.getStringExtra("title");
//        vid_url = nowintent.getData();
        vid_url = Uri.parse(nowintent.getStringExtra("vid_url"));
        custom_ad_stats = nowintent.getStringExtra("custom_ad_stats");
        DrmLicenceUrl = nowintent.hasExtra("DrmLicenceUrl") ? nowintent.getStringExtra("DrmLicenceUrl") : "";
        DrmScheme = nowintent.hasExtra("DrmScheme") ? nowintent.getStringExtra("DrmScheme") : "clearkey";
//        DrmClearKeyJson = nowintent.hasExtra("DrmClearKeyJson") ? nowintent.getStringExtra("DrmClearKeyJson") : "";
        whatWebPlayer = nowintent.getStringExtra("whatWebPlayer");
        isHlsChecked = nowintent.getBooleanExtra("isHlsChecked",false);
        u_r_a = nowintent.getStringExtra("u_r_a");
        e_m = nowintent.getStringExtra("e_m");
        e_v_m = nowintent.getStringExtra("e_v_m");
        e_f_v_m = nowintent.getStringExtra("e_f_v_m");
        f_v_m = nowintent.getStringExtra("f_v_m");
        vid_user_agent = nowintent.hasExtra("vid_user_agent") ? nowintent.getStringExtra("vid_user_agent") : "";
        vid_CustomHeaders = (HashMap<String, String>) nowintent.getSerializableExtra("CustomHeaders");
        continue_watch = nowintent.getBooleanExtra("continue_watch", false);
        continue_watch_code = nowintent.getStringExtra("continue_watch_code");
        Player_name = nowintent.getStringExtra("Player_name");
        player_num = nowintent.getIntExtra("player_num",1);

        assets_js = nowintent.getStringExtra("assets_js");
        where_assets_js_file = nowintent.getStringExtra("where_assets_js_file");
        AD_TAG_URI = nowintent.getStringExtra("AD_TAG_URI");

        loading_ad_text = findViewById(R.id.loading_ad_text);
        alertDialog = findViewById(R.id.alertDialog);
        Back_button = (Button) findViewById(R.id.Back_button);
        Retry_button = (Button) findViewById(R.id.Retry_button);

        Back_button.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                if (f_v_m.compareTo("false") == 0) {
                    continueToVid();
                }
                finish();
            }
        });
        Retry_button.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                loading_ad_text.setVisibility(View.VISIBLE);
                alertDialog.setVisibility(View.GONE);
                webView.loadUrl(AD_TAG_URI);

            }
        });

        boolean show_ads = false;
        if (!Objects.equals(custom_ad_stats, "false")) {
            if (e_m.compareTo("true") == 0) {
                if (e_f_v_m.compareTo("true") == 0) {
                    show_ads = true;
                }
            }
        }

        if (show_ads) {
            webView = findViewById(R.id.webview);
            WebSettings webSettings = webView.getSettings();
            webSettings.setJavaScriptEnabled(true);
            webView.setHapticFeedbackEnabled(false);
            webView.setHorizontalScrollBarEnabled(false);
            webView.setVerticalScrollBarEnabled(false);
            webView.getSettings().setTextZoom(100);
            webView.getSettings().setRenderPriority(WebSettings.RenderPriority.HIGH);
            CookieManager cookieManager = CookieManager.getInstance();
            cookieManager.setAcceptCookie(true);
            cookieManager.setAcceptThirdPartyCookies(webView, true);
//            CookieManager.getInstance().setAcceptThirdPartyCookies(webView, true);
            webView.getSettings().setUserAgentString("Mozilla/5.0 (Linux; Android 10; Mobile) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.74 Mobile Safari/537.36");
            webView.setBackgroundColor(0); // Set background color to transparent

            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                webView.setRendererPriorityPolicy(RENDERER_PRIORITY_BOUND, true);
            }
            webSettings.setAllowFileAccessFromFileURLs(true);
            webSettings.setSavePassword(false);
            webSettings.setSaveFormData(false);
            webSettings.setAllowUniversalAccessFromFileURLs(true);
            webSettings.setAllowFileAccess(true);
            webView.addJavascriptInterface(new Player1Activity.WebAppInterface(this), "mouscripts");
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
//            webView.setLayerType(View.LAYER_TYPE_SOFTWARE, null); // Optional, for older devices

            webView.setOverScrollMode(View.OVER_SCROLL_NEVER);
            webView.requestFocus();
            webView.clearCache(true);
            webView.setWebChromeClient(new WebChromeClient() {
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


            });

            webView.setWebViewClient(new WebViewClient() {
                @SuppressLint("WebViewClientOnReceivedSslError")
                @Override
                public void onReceivedSslError(WebView view, SslErrorHandler handler, SslError error) {
                    handler.proceed();
                }
                @Override
                public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest request) {
//                    view.loadUrl(request.getUrl().toString());
                    return false;
                }
                @Override
                public void onPageStarted(WebView view, String url, Bitmap favicon) {
                    webView.setVisibility(View.GONE);
                }
                @Override
                public void onPageFinished(WebView view, String url) {
                    if(!Objects.equals(url, "about:blank")){
                        webView.setVisibility(View.VISIBLE);
                    }
                }
                @Override
                public void onReceivedError(WebView view, WebResourceRequest request, WebResourceError error) {
                    CookieManager.getInstance().removeAllCookies(null);
                    webView.clearCache(true);
                    webView.reload();
                }

                @Override
                public WebResourceResponse shouldInterceptRequest(WebView view, WebResourceRequest request) {
                    OkHttpClient okHttpClient = getUnsafeOkHttpClient();
                    try {
                        String url = request.getUrl().toString();
                        if(url.equals(AD_TAG_URI)) {

                            // Create an OkHttp Request
                            Request okHttpRequest = new Request.Builder()
                                    .url(url)
                                    .build();

                            // Execute the request
                            Response okHttpResponse = okHttpClient.newCall(okHttpRequest).execute();

                            // Get status code
                            int statusCode = okHttpResponse.code();
                            Log.d("WebView", "getUrl: " + request.getUrl().toString());
                            Log.d("WebView", "Status Code: " + statusCode);

                            // Get MIME type and response data
                            String mimeType = okHttpResponse.header("Content-Type", "text/html");
                            InputStream inputStream = okHttpResponse.body().byteStream();

                            Map<String, String> singleValueHeaders = new HashMap<>();
                            for (Map.Entry<String, List<String>> entry : okHttpResponse.headers().toMultimap().entrySet()) {
                                singleValueHeaders.put(entry.getKey(), String.join(", ", entry.getValue()));
                            }
                            // Return response to WebView
                            return new WebResourceResponse(
                                    mimeType,
                                    "UTF-8",
                                    statusCode,
                                    okHttpResponse.message(),
                                    singleValueHeaders,
                                    inputStream
                            );
                        }
                        else {
                            return null;  // Fallback to normal behavior
                        }

                    } catch (Exception e) {
                        Log.e("WebView", "Error: " + e.getMessage());
                        return null;  // Fallback to normal behavior
                    }
                }


            });


            webView.loadUrl(AD_TAG_URI);
        } else {
            continueToVid();
        }

    }
    public void continueToVid() {
        if (Objects.equals(Player_name, "Elbatal") || Objects.equals(Player_name, "WebPlayer")) {
            Intent Player_intent = null;
//            if(Objects.equals(Player_name, "WebPlayer")){
//                Player_intent = new Intent(Player1Activity.this, WebPlayer.class);

//                Player_intent = new Intent(Intent.ACTION_VIEW);
//
//                Player_intent.setPackage("com.mouscripts.bplayer");
//                Player_intent.setClassName("com.mouscripts.bplayer", "com.mouscripts.bplayer.Player1Activity");

//            } else if(Objects.equals(Player_name, "Elbatal")){
//                Player_intent.setComponent(new ComponentName("com.mouscripts.bplayer", "com.mouscripts.bplayer.Player1Activity"));
//                Player_intent = new Intent(Intent.ACTION_VIEW);
//
//                Player_intent.setPackage("com.mouscripts.bplayer");
//                Player_intent.setClassName("com.mouscripts.bplayer", "com.mouscripts.bplayer.Player1Activity");

//                Intent currentIntent = getIntent();
//                copyIntentExtras(currentIntent, Player_intent);
//            }

            Player_intent = new Intent(Intent.ACTION_VIEW);

            Player_intent.setPackage("com.mouscripts.bplayer");
            Player_intent.setClassName("com.mouscripts.bplayer", "com.mouscripts.bplayer.Player1Activity");

            Integer player_num = 1;
            if(Objects.equals(Player_name, "WebPlayer")){
                player_num = 2;
            }

            Player_intent.putExtra("player_num", player_num);

//            assert Player_intent != null;
            if (!"file".equalsIgnoreCase(vid_url.getScheme())) {
                Player_intent.setData(vid_url);
            }
            Player_intent.putExtra("vid_url", vid_url.toString());
            Player_intent.putExtra("title", vid_title);
            Player_intent.putExtra("isHlsChecked", isHlsChecked);
            if (!vid_user_agent.equals("")) {
                Player_intent.putExtra("vid_user_agent", vid_user_agent);
            }
            Player_intent.putExtra("continue_watch", continue_watch);
            Player_intent.putExtra("continue_watch_code", continue_watch_code);
            Player_intent.putExtra("CustomHeaders", vid_CustomHeaders);

            Player_intent.putExtra("DrmLicenceUrl", DrmLicenceUrl);
            Player_intent.putExtra("DrmScheme", DrmScheme);

            Player_intent.putExtra("whatWebPlayer", whatWebPlayer);

            Player_intent.putExtra("custom_ad_stats", custom_ad_stats);
            Player_intent.putExtra("u_r_a", u_r_a);
            Player_intent.putExtra("e_m", e_m);
            Player_intent.putExtra("e_v_m", e_v_m);
            Player_intent.putExtra("assets_js", assets_js);
            Player_intent.putExtra("where_assets_js_file", where_assets_js_file);

            if(Objects.equals(whatWebPlayer, "localWebPlayer")){
                pendingPlayerIntent = Player_intent;
                activityResultLauncher.launch(Player_intent);
            } else {
                startActivity(Player_intent);
                finish();
            }

        } else if(Objects.equals(Player_name, "MX_Player") || Objects.equals(Player_name, "MX_Player_Pro")) {
//            Intent Player_intent = new Intent(Intent.ACTION_VIEW);
//            Player_intent.setData(vid_url);
//            Player_intent.putExtra("title", vid_title);
//            Player_intent.setPackage( "com.mxtech.videoplayer.ad" );
//            startActivity(Player_intent);

            List<String> tempList = new ArrayList<>();
            Bundle headers = new Bundle();
            try {
                JSONObject vid_CustomHeaders_obj = new JSONObject(vid_CustomHeaders);
                Iterator<String> iter = vid_CustomHeaders_obj.keys();
                while (iter.hasNext()) {
                    String key = iter.next();
                    try {
                        String value = vid_CustomHeaders_obj.get(key).toString();
                        headers.putString(key, value);

                        tempList.add(key);
                        tempList.add(value);
                    } catch (JSONException ignored) {
                    }
                }
            } catch (Exception ignored){
            }
            if (!vid_user_agent.equals("")) {
                headers.putString("User-Agent", vid_user_agent);
                tempList.add("User-Agent");
                tempList.add(vid_user_agent);
            }
// must have size
            String[] info = new String[tempList.size()];
            for (int i = 0; i < tempList.size(); i++) {
                info[i] = tempList.get(i);
            }

            Intent intent = new Intent(Intent.ACTION_VIEW);
            intent.setData(vid_url);
            intent.putExtra("title", vid_title);
            if(!continue_watch) {
                intent.putExtra("position", 0L);
            }
            if(Objects.equals(Player_name, "MX_Player")) {
                intent.setPackage("com.mxtech.videoplayer.ad");
                intent.setClassName("com.mxtech.videoplayer.ad", "com.mxtech.videoplayer.ad.ActivityScreen");
//                intent.setPackage("com.mxtech.videoplayer.ad"); //
            } else if(Objects.equals(Player_name, "MX_Player_Pro")){
                intent.setPackage("com.mxtech.videoplayer.pro");
                intent.setClassName("com.mxtech.videoplayer.pro", "com.mxtech.videoplayer.pro.ActivityScreen");
//                intent.setPackage("com.mxtech.videoplayer.pro");
            }
            intent.putExtra("headers", info);
            startActivity(intent);
            finish();


        }
    }


    private void hideSystemUI() {

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

    private void showSystemUI() {
        int uiOptions = View.SYSTEM_UI_FLAG_LAYOUT_FULLSCREEN
                | View.SYSTEM_UI_FLAG_LAYOUT_STABLE
                | View.SYSTEM_UI_FLAG_LAYOUT_HIDE_NAVIGATION;
    }

    @Override
    protected void onResume() {
        hideSystemUI();
        super.onResume();
    }

    public class WebAppInterface {
        Context mContext;
        WebAppInterface(Context c) {
            mContext = c;
        }
        @JavascriptInterface
        public void mou_continue() {
            continueToVid();
        }
        @JavascriptInterface
        public void open_external_link(String link) {
            startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse(link)));
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

    // Method to copy extras from one intent to another
    private void copyIntentExtras(Intent sourceIntent, Intent targetIntent) {
        Bundle extras = sourceIntent.getExtras(); // Get extras from the source intent
        if (extras != null) {
            for (String key : extras.keySet()) {
                Object value = extras.get(key);
                Log.d(TAG, "copyIntentExtras: key => " + key);
                Log.d(TAG, "copyIntentExtras: value => " + value);
                if (value != null) {
                    // Add the value to the target intent, maintaining the original data type
                    if (value instanceof String) {
                        targetIntent.putExtra(key, (String) value);
                    } else if (value instanceof Integer) {
                        targetIntent.putExtra(key, (Integer) value);
                    } else if (value instanceof Boolean) {
                        targetIntent.putExtra(key, (Boolean) value);
                    } else if (value instanceof Float) {
                        targetIntent.putExtra(key, (Float) value);
                    } else if (value instanceof Long) {
                        targetIntent.putExtra(key, (Long) value);
                    } else if (value instanceof Double) {
                        targetIntent.putExtra(key, (Double) value);
                    } else if (value instanceof Bundle) {
                        targetIntent.putExtra(key, (Bundle) value);
                    }
                    // Add more types as needed
                }
            }
        }

        // Copy data URI
        Uri dataUri = sourceIntent.getData();
        if (dataUri != null) {
            targetIntent.setData(dataUri);
        }
    }

}

