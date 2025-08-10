Java.perform(function () {
    var currentApplication = Java.use("android.app.ActivityThread").currentApplication();
    var context = currentApplication.getApplicationContext();

    var Intent = Java.use("android.content.Intent");
    var ProductListActivity = Java.use("com.insecureshop.ProductListActivity");

    var intent = Intent.$new(context, ProductListActivity.class);
    intent.setFlags(0x10000000);  // FLAG_ACTIVITY_NEW_TASK

    context.startActivity(intent);

    console.log("[+] ProductListActivity launched directly.");
});
