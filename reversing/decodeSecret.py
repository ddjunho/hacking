import base64

def decode_secret(encoded_hex):
    # 16진수를 다시 문자열로 변환
    reversed_str = bytes.fromhex(encoded_hex).decode()
    # 문자열을 뒤집기
    reversed_str = reversed_str[::-1]
    # Base64 디코딩
    decoded = base64.b64decode(reversed_str)
    return decoded

# 주어진 16진수 문자열
encoded_hex = '3d3d516343746d4d6d6c315669563362'

# 원본 문자열 복원
original_secret = decode_secret(encoded_hex)
print(original_secret)
