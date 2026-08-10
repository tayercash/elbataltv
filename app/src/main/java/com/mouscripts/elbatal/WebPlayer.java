package com.mouscripts.elbatal;

import static android.webkit.WebView.RENDERER_PRIORITY_BOUND;

import android.annotation.SuppressLint;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.pm.ActivityInfo;
import android.content.pm.PackageInfo;
import android.graphics.Bitmap;
import android.net.Uri;
import android.net.http.SslError;
import android.os.Build;
import android.os.Bundle;
import android.text.InputType;
import android.util.Log;
import android.view.View;
import android.view.Window;
import android.view.WindowManager;
import android.webkit.CookieManager;
import android.webkit.JavascriptInterface;
import android.webkit.JsPromptResult;
import android.webkit.JsResult;
import android.webkit.SslErrorHandler;
import android.webkit.WebChromeClient;
import android.webkit.WebResourceRequest;
import android.webkit.WebResourceResponse;
import android.webkit.WebSettings;
import android.webkit.WebStorage;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.EditText;

import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.ByteArrayInputStream;
import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.security.KeyManagementException;
import java.security.NoSuchAlgorithmException;
import java.security.cert.CertificateException;
import java.security.cert.X509Certificate;
import java.util.Base64;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map;
import java.util.Objects;

import javax.net.ssl.HttpsURLConnection;
import javax.net.ssl.SSLContext;
import javax.net.ssl.TrustManager;
import javax.net.ssl.X509TrustManager;

public class WebPlayer extends AppCompatActivity {
    private final String TAG = "WebPlayer";
    private WebView webView;
    private String vid_user_agent = null;
    private String assets_js = null;
    private String where_assets_js_file = null;
    StringBuilder blocklist;
    String loddnormallist= "0"; //if you want to use a filterlist without "::::" at the beginning. please change to 1

    private HashMap<String, String> vid_CustomHeaders;
    @SuppressLint({"SetJavaScriptEnabled", "AddJavascriptInterface"})
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        supportRequestWindowFeature(Window.FEATURE_NO_TITLE); //will hide the title
        setFullscreen(true);
        getWindow().setFlags(
                WindowManager.LayoutParams.FLAG_HARDWARE_ACCELERATED,
                WindowManager.LayoutParams.FLAG_HARDWARE_ACCELERATED);
        setContentView(R.layout.web_player);

        disableSSLCertificateChecking();

        WebPlayer.this.setRequestedOrientation(ActivityInfo.SCREEN_ORIENTATION_LANDSCAPE);

        Intent intent = getIntent();
        vid_user_agent = intent.getStringExtra("vid_user_agent");
        assets_js = intent.getStringExtra("assets_js");
        where_assets_js_file = intent.getStringExtra("where_assets_js_file");

//        load_block_list();

        vid_CustomHeaders = (HashMap<String, String>) intent.getSerializableExtra("CustomHeaders");


        final String databasePath = WebPlayer.this.getApplicationContext().getDir("database", Context.MODE_PRIVATE).getPath();
        webView = findViewById(R.id.WebView);
        WebSettings webSettings = webView.getSettings();
        webSettings.setJavaScriptEnabled(true);
        webView.setHapticFeedbackEnabled(false);
        webView.setHorizontalScrollBarEnabled(false);
        webView.setVerticalScrollBarEnabled(false);
        webView.getSettings().setTextZoom(100);
        webView.getSettings().setRenderPriority(WebSettings.RenderPriority.HIGH);
        CookieManager.getInstance().setAcceptThirdPartyCookies(webView, true);
        webView.getSettings().setUserAgentString(vid_user_agent);

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            webView.setRendererPriorityPolicy(RENDERER_PRIORITY_BOUND, true);
        }
        webSettings.setDatabasePath(databasePath);
        webSettings.setAllowFileAccessFromFileURLs(true);
        webSettings.setSavePassword(false);
        webSettings.setSaveFormData(false);
        webSettings.setAllowUniversalAccessFromFileURLs(true);
        webSettings.setAllowFileAccess(true);
        webView.addJavascriptInterface(new WebAppInterface(this), "elplayer");
        webSettings.setJavaScriptEnabled(true);
        webSettings.setMediaPlaybackRequiresUserGesture(false);
        webSettings.setJavaScriptCanOpenWindowsAutomatically(false);
        webSettings.setDomStorageEnabled(true);
        webSettings.setDatabaseEnabled(true);
        webSettings.setLoadsImagesAutomatically(true);
        webSettings.setUseWideViewPort(false);
        webSettings.setCacheMode(WebSettings.LOAD_NO_CACHE);
        webSettings.setSupportZoom(true);
        webSettings.setSupportMultipleWindows(false);
        webSettings.setSaveFormData(true);

        webView.setLayerType(View.LAYER_TYPE_HARDWARE, null);
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

        // [END handle_data_extras]
        if (savedInstanceState == null) {
//            Uri uri = getIntent().getData();
            Uri uri = Uri.parse(getIntent().getStringExtra("vid_url"));
            Map<String, String> extraHeaders = new HashMap<String, String>();


            try {
                JSONObject vid_CustomHeaders_obj = new JSONObject(vid_CustomHeaders);
                Iterator<String> iter = vid_CustomHeaders_obj.keys();
                while (iter.hasNext()) {
                    String key = iter.next();
                    try {
                        String value = vid_CustomHeaders_obj.get(key).toString();
                        extraHeaders.put(key, value);
                    } catch (JSONException ignored) {
                    }
                }
            } catch (Exception ignored){
            }
            if (intent.hasExtra("vid_user_agent")) {
                extraHeaders.put("User-Agent", intent.getStringExtra("vid_user_agent"));
            }
            Log.d(TAG, "onCreate: extraHeaders => " + extraHeaders);

            if (uri != null) {
                webView.loadUrl(String.valueOf(uri),extraHeaders);
            }
        }

        webView.setWebViewClient(new WebViewClient() {
            @Override
            public WebResourceResponse shouldInterceptRequest(WebView view, WebResourceRequest request) {
//                ByteArrayInputStream EMPTY3 = new ByteArrayInputStream("".getBytes());
//                String kk53 = String.valueOf(blocklist);//Load blocklist
//                if (kk53.contains(":::::" + request.getUrl().getHost())) {// If blocklist equals url = Block
//                    return new WebResourceResponse("text/plain", "utf-8", EMPTY3);//Block
//                }
                return super.shouldInterceptRequest(view, request);
            }
            @Override
            public void onPageStarted(WebView view, String url, Bitmap favicon) {
            }

            @Override
            public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest request) {
                return true;
            }


            @Override
            public void onReceivedError(WebView view, int errorCode, String description, String failingUrl) {
                if(errorCode == -1){
                    Log.d(TAG, "onReceivedError: Oh no! " + errorCode);
                    Log.d(TAG, "failingUrl => " + failingUrl);
                    webView.loadUrl("http://192.168.1.16:8080/newbatal/404.html");

                }
            }


            @Override
            public void onPageFinished(WebView view, String url) {
                super.onPageFinished(view, url);

                if (!url.equals("about:blank")) {
                    try {
                        byte[] buffer = null;
                        String encoded_js = "";
                        if (Objects.equals(where_assets_js_file, "1")) {
                            URL jsUrl = new URL("http://192.168.1.16:8080/newbatal/" + assets_js);
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
                        } else if (Objects.equals(where_assets_js_file, "2")) {
                            File file = new File(getFilesDir(), "project/" + assets_js);
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


                        webView.loadUrl("javascript:(function() {" +
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


//                if (url.equals("http://192.168.1.16:8080/newbatal/index.html")) {
//
//                }

            }

            @SuppressLint("WebViewClientOnReceivedSslError")
            @Override
            public void onReceivedSslError(WebView view, SslErrorHandler handler, SslError error) {
                Log.d("ssl_error", error.toString());
                handler.proceed();
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
    private static void disableSSLCertificateChecking() {
        TrustManager[] trustAllCerts = new TrustManager[] { new X509TrustManager() {
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
//    private void load_block_list(){//Blocklist loading
//        String strLine2="";
//        blocklist = new StringBuilder();
//
//        InputStream fis2 = this.getResources().openRawResource(R.raw.adblockserverlist);//Storage location
//        BufferedReader br2 = new BufferedReader(new InputStreamReader(fis2));
//        if(fis2 != null) {
//            try {
//                while ((strLine2 = br2.readLine()) != null) {
//                    if(loddnormallist.equals("0")){
//                        blocklist.append(strLine2);//if ":::::" exists in blocklist | Line for Line
//                        blocklist.append("\n");
//                    }
//                    if(loddnormallist.equals("1")){
//                        blocklist.append(":::::"+strLine2);//if ":::::" not exists in blocklist | Line for Line
//                        blocklist.append("\n");
//                    }
//
//                }
//            } catch (IOException e) {
//                e.printStackTrace();
//            }
//        }
//    }
    public static class WebAppInterface {
        Context mContext;

        WebAppInterface(Context c) {
            mContext = c;
        }

        @JavascriptInterface
        public void showHTML(String html) {


        }
    }
}
