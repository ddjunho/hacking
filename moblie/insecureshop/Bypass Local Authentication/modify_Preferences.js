Java.perform(function () {
    var currentActivity = Java.use("android.app.ActivityThread").currentActivity();
    var Intent = Java.use("android.content.Intent");
    var ProductListActivity = Java.use("com.insecureshop.ProductListActivity");

    var intent = Intent.$new(currentActivity, ProductListActivity.class);
    currentActivity.startActivity(intent);

    console.log("[+] Launched ProductListActivity directly.");
    var Prefs = Java.use("com.insecureshop.util.Prefs");
    var context = Java.use("android.app.ActivityThread").currentApplication().getApplicationContext();
    var prefsInstance = Prefs.getInstance(context);

    prefsInstance.setUsername("11");
    prefsInstance.setPassword("11");

    console.log("[+] Preferences modified. You can now navigate manually to ProductListActivity.");
});
