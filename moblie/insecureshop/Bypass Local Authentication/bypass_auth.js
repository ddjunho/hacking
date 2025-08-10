Java.perform(function () {
    var Util = Java.use("com.insecureshop.util.Util");

    Util.verifyUserNamePassword.implementation = function (username, password) {
        console.log("[+] Intercepted verifyUserNamePassword");
        console.log("    username: " + username);
        console.log("    password: " + password);
        return true;  // 인증 우회
    };
});