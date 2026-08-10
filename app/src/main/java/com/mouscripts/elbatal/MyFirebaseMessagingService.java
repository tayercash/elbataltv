package com.mouscripts.elbatal;

import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.net.Uri;
import android.os.Build;
import android.util.Log;

import androidx.annotation.NonNull;
import androidx.core.app.NotificationCompat;

import com.google.firebase.messaging.FirebaseMessagingService;
import com.google.firebase.messaging.RemoteMessage;

import java.util.Map;
import java.util.Objects;

public class MyFirebaseMessagingService extends FirebaseMessagingService {

    private static final String TAG = "FCMService";
    private static final String CHANNEL_ID = "FCM_CHANNEL";

    @Override
    public void onNewToken(@NonNull String token) {
        Log.d(TAG, "Refreshed token: " + token);
        getSharedPreferences("FcmToken", MODE_PRIVATE).edit().putString("FcmToken", token).apply();
    }

    @Override
    public void onMessageReceived(@NonNull RemoteMessage remoteMessage) {
        Log.d(TAG, "Message received: " + remoteMessage);

        if (!remoteMessage.getData().isEmpty()) {
            // Extract custom data
            String title = remoteMessage.getData().get("title");
            String body = remoteMessage.getData().get("body");
//            String url = remoteMessage.getData().get("url");
//            String openUrlWhere = remoteMessage.getData().get("open_url_where");

            // Show notification
            showNotification(title, body, remoteMessage.getData());
        }
    }

    private void showNotification(String title, String body,  Map<String, String> data) {
        String url = data.get("url");
        String openUrlWhere = data.get("open_url_where");

        Intent intent;
        if ("browser".equals(openUrlWhere) && url != null && !url.isEmpty()) {
            intent = new Intent(Intent.ACTION_VIEW, Uri.parse(url));
        } else {
            intent = new Intent(this, MainActivity.class);

            for (String key : data.keySet()) {
                String value = data.get(key);
                if(Objects.equals(key, "url")){
                    intent.setData(Uri.parse(value));
                }
                Log.d(TAG, "sendNotification: data = " + key + " = " + value);
                intent.putExtra(key,value);
            }
        }

        PendingIntent pendingIntent = PendingIntent.getActivity(
                this,
                0,
                intent,
                PendingIntent.FLAG_UPDATE_CURRENT | PendingIntent.FLAG_IMMUTABLE);

        NotificationManager notificationManager =
                (NotificationManager) getSystemService(Context.NOTIFICATION_SERVICE);

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            NotificationChannel channel = new NotificationChannel(
                    CHANNEL_ID,
                    "FCM Notifications",
                    NotificationManager.IMPORTANCE_HIGH);
            if (notificationManager != null) {
                notificationManager.createNotificationChannel(channel);
            }
        }

        NotificationCompat.Builder builder = new NotificationCompat.Builder(this, CHANNEL_ID)
                .setSmallIcon(R.drawable.notification)
                .setContentTitle(title)
                .setContentText(body)
                .setPriority(NotificationCompat.PRIORITY_HIGH)
                .setAutoCancel(true)
                .setContentIntent(pendingIntent);

        if (notificationManager != null) {
            notificationManager.notify((int) System.currentTimeMillis(), builder.build());
        }
    }
}