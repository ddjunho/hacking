Java.perform(function () {
    var FileInputStream = Java.use("java.io.FileInputStream");
    var InputStreamReader = Java.use("java.io.InputStreamReader");
    var BufferedReader = Java.use("java.io.BufferedReader");
    var File = Java.use("java.io.File");

    var filePath = "/data/data/com.insecureshop/shared_prefs/Prefs.xml";
    var file = File.$new(filePath);

    if (!file.exists()) {
        console.log("File does not exist: " + filePath);
        return;
    }

    var inputStream = FileInputStream.$new(file);
    var reader = BufferedReader.$new(InputStreamReader.$new(inputStream));
    var line;

    var content = "";
    while ((line = reader.readLine()) != null) {
        content += line + "\n";
    }

    reader.close();
    inputStream.close();

    // 결과를 PC에서 출력할 수 있도록 전달
    send(content);
});
